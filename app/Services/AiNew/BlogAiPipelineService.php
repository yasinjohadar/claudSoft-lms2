<?php

namespace App\Services\AiNew;

use App\Ai\Agents\BlogOutlineAgent;
use App\Ai\Agents\BlogSectionPlainAgent;
use App\Exceptions\Ai\AiProviderException;
use App\Exceptions\Ai\ResumableIncompleteException;
use App\Models\AIModel;
use App\Models\BlogAiGeneration;
use App\Models\BlogAiSection;
use App\Models\BlogCategory;
use App\Models\LaravelAiModel;
use App\Models\User;
use App\Services\Ai\AIBlogPostService;
use App\Services\Ai\AIModelService;
use App\Services\Ai\AIProviderService;
use App\Services\Ai\BlogAiResultNormalizer;
use App\Services\Ai\BlogHtmlStyleGuide;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Throwable;

/**
 * Forked from DocumentationAiPipelineService, trimmed to only the "generate"
 * operation — blog has no "improve existing post" / "structure raw content"
 * wizard today, so the chunk-based runTransform()/runStructure() paths are not
 * needed here.
 */
class BlogAiPipelineService
{
    private const SECTION_TIMEOUT = 240;

    private const OUTLINE_TIMEOUT = 120;

    public function __construct(
        private LaravelAiProviderManager $providerManager,
        private LaravelAiPromptRunner $promptRunner,
        private LaravelAiRequestLogger $logger,
        private LaravelAiBlogService $laravelAiBlogService,
        private AIBlogPostService $legacyBlog,
        private AIModelService $legacyModelService,
        private BlogAiResultNormalizer $resultNormalizer,
        private BlogStagedGenerator $stagedGenerator,
    ) {}

    public function run(BlogAiGeneration $generation): void
    {
        set_time_limit(0);

        $generation->refresh();
        if (in_array($generation->status, [
            BlogAiGeneration::STATUS_COMPLETED,
            BlogAiGeneration::STATUS_CANCELLED,
        ], true)) {
            return;
        }

        $generation->markRunning('starting', 'بدء المعالجة…', 2);

        try {
            $result = match ($generation->operation) {
                BlogAiGeneration::OPERATION_GENERATE => $this->runGenerate($generation),
                default => throw new \InvalidArgumentException('عملية غير معروفة: '.$generation->operation),
            };

            $generation->markCompleted($result);
        } catch (ResumableIncompleteException $e) {
            // Finished sections are already persisted; stop instead of throwing the work away.
            Log::warning('Blog AI paused with resumable progress', [
                'uuid' => $generation->uuid,
                'done' => $e->done,
                'planned' => $e->planned,
                'failed_headings' => $e->failedHeadings,
            ]);
            $generation->markPaused($e->getMessage());
        } catch (Throwable $e) {
            Log::error('Blog AI pipeline failed', [
                'uuid' => $generation->uuid,
                'operation' => $generation->operation,
                'message' => $e->getMessage(),
            ]);

            // Keep partial work reachable when the run had already produced sections.
            if ($generation->sections()->where('status', BlogAiSection::STATUS_DONE)->exists()) {
                $generation->markPaused($this->friendlyError($e).' — الأقسام المكتملة محفوظة، اضغط «متابعة التوليد».');

                return;
            }

            $generation->markFailed($this->friendlyError($e));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function runGenerate(BlogAiGeneration $generation): array
    {
        $payload = $generation->payload;
        $topic = trim((string) ($payload['topic'] ?? ''));
        if ($topic === '') {
            throw new \InvalidArgumentException('الموضوع مطلوب.');
        }

        $engine = (string) ($payload['blog_engine'] ?? 'legacy');
        $contentLength = (string) ($payload['content_length'] ?? 'medium');
        $options = $this->wizardOptionsFromPayload($payload);
        $useStaged = in_array($contentLength, ['medium', 'long'], true);

        Log::info('Blog AI generate path', [
            'uuid' => $generation->uuid,
            'engine' => $engine,
            'content_length' => $contentLength,
            'staged' => $useStaged || $engine === 'laravel_ai',
        ]);

        // Legacy short: one-shot (reuses the existing single-call generator,
        // which already returns the fully expanded SEO/OG/Twitter/Schema shape).
        // Legacy medium/long, and laravel_ai at any length: staged outline+sections.
        if ($engine !== 'laravel_ai') {
            if (! $useStaged) {
                $generation->markProgress('legacy_generate', 'توليد المقال (محرك قديم)…', 20);
                $model = $this->resolveLegacyModel($payload);
                $result = $this->legacyBlog->generateBlogPost($topic, $model, $options);
                $generation->markProgress('legacy_generate', 'اكتمل التوليد', 95, ['title' => $result['title'] ?? null]);

                return $result;
            }

            return $this->runStagedGenerate($generation, $topic, $options, $contentLength, useLaravelAi: false);
        }

        return $this->runStagedGenerate($generation, $topic, $options, $contentLength, useLaravelAi: true);
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function runStagedGenerate(
        BlogAiGeneration $generation,
        string $topic,
        array $options,
        string $contentLength,
        bool $useLaravelAi,
    ): array {
        $payload = $generation->payload;
        $user = User::query()->find($generation->user_id);
        $sectionTarget = $this->sectionCountForLength($contentLength);

        $laraModel = null;
        $legacyModel = null;
        if ($useLaravelAi) {
            $laraModel = $this->resolveLaravelModel($payload);
            $outlineTokens = (int) config('ai.blog.outline_max_tokens', 2048);
            // Same length-aware ceiling the legacy engine uses, so a long article
            // gets room to be comprehensive instead of being truncated into stubs.
            $sectionTokens = self::sectionTokenCeiling($contentLength);
            if ((int) ($laraModel->max_tokens ?? 0) > 0) {
                $outlineTokens = min($outlineTokens, (int) $laraModel->max_tokens);
                $sectionTokens = min($sectionTokens, (int) $laraModel->max_tokens);
            }
        } else {
            $legacyModel = $this->resolveLegacyModel($payload);
            $outlineTokens = $this->legacyBlog->tokensForStage($legacyModel, 'outline');
            $sectionTokens = $this->legacyBlog->tokensForStage($legacyModel, 'section', false, $contentLength);
        }

        $outlineWriter = function (int $target, int $maxTokens) use (
            $useLaravelAi, $topic, $options, $user, $laraModel, $legacyModel
        ): array {
            if ($useLaravelAi) {
                return $this->generateOutline($topic, $options, $target, $user, $laraModel, $maxTokens);
            }

            return $this->legacyBlog->generateBlogOutline($topic, $legacyModel, $options, $target, $maxTokens);
        };

        $sectionWriter = function (BlogSectionAttempt $attempt) use (
            $useLaravelAi, $topic, $generation, $options, $user, $laraModel, $legacyModel, $contentLength
        ): string {
            $outline = $generation->partial_result['outline'] ?? [];

            if ($useLaravelAi) {
                return $this->requestSectionHtml(
                    $topic,
                    is_array($outline) ? $outline : [],
                    $attempt->heading,
                    $attempt->brief,
                    $attempt->priorHeadings,
                    $options,
                    $user,
                    $laraModel,
                    $contentLength,
                    $attempt->compact,
                    $attempt->maxTokens,
                    $attempt->laterHeadings,
                );
            }

            return $this->legacyBlog->generateBlogSectionHtml(
                $topic,
                is_array($outline) ? $outline : [],
                $attempt->heading,
                $attempt->brief,
                $attempt->priorHeadings,
                $legacyModel,
                $options,
                $attempt->compact,
                $attempt->maxTokens,
                $attempt->laterHeadings,
            );
        };

        $staged = $this->stagedGenerator->generate(
            $generation,
            $topic,
            $options,
            $sectionTarget,
            $outlineTokens,
            $sectionTokens,
            $outlineWriter,
            $sectionWriter,
        );

        $generation->markProgress('expand', 'استكمال حقول SEO…', 96);

        return $this->expandStagedResult($staged, $topic, $payload, $options, $useLaravelAi);
    }

    /**
     * Append SEO/OG/Twitter/Schema/synonyms to the plain {title, slug, excerpt,
     * content} the staged generator assembled — the same tail every one-shot
     * path already applies, so both delivery modes end up with an identical
     * final shape.
     *
     * @param  array<string, mixed>  $staged
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $options
     */
    private function expandStagedResult(array $staged, string $topic, array $payload, array $options, bool $useLaravelAi): array
    {
        if ($useLaravelAi) {
            return $this->laravelAiBlogService->expandDraftToWizardPayload([
                'title' => $staged['title'] ?? '',
                'content' => $staged['content'] ?? '',
                'excerpt' => $staged['excerpt'] ?? '',
            ], $options);
        }

        $legacyModel = $this->resolveLegacyModel($payload);

        return $this->legacyBlog->expandGeneratedPost([
            'title' => $staged['title'] ?? '',
            'slug' => $staged['slug'] ?? '',
            'excerpt' => $staged['excerpt'] ?? '',
            'content' => $staged['content'] ?? '',
        ], $topic, $legacyModel, $options);
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function generateOutline(
        string $topic,
        array $options,
        int $sectionTarget,
        ?User $user,
        LaravelAiModel $model,
        ?int $maxTokens = null,
    ): array {
        $language = $options['language'] ?? 'ar';
        $tone = $options['tone'] ?? 'professional';
        $length = $options['content_length'] ?? 'medium';
        /** @var BlogCategory|null $category */
        $category = $options['category'] ?? null;

        $langLine = $language === 'en'
            ? 'Plan the article in English.'
            : 'خطط المقال بالعربية.';
        $categoryLine = $category ? "التصنيف: {$category->name}. " : '';
        $pageBudget = BlogHtmlStyleGuide::pageBudget((string) $length);
        $topicForPrompt = Str::limit(trim($topic), 1500);

        $prompt = <<<PROMPT
خطط مقال مدونة شاملاً ثم قسّمه إلى أقسام.

الموضوع: {$topicForPrompt}
{$categoryLine}
عدد الأقسام المستهدف تقريباً: {$sectionTarget}
طول المحتوى الإجمالي المستهدف: {$pageBudget}
الأسلوب: {$tone}
{$langLine}

اجعل الأقسام تقرأ كعناوين فرعية طبيعية للمقال (وليست فصولاً موسوعية)، ولا تكرر أقساماً بنفس المعنى.

أعد: title, slug, excerpt, sections[{heading, brief}].
كل brief جملة قصيرة توضّح ما سيُغطى في القسم فقط.
PROMPT;

        $started = hrtime(true);

        /** @var StructuredAgentResponse $response */
        $response = $this->providerManager->runWithModel($model, function () use ($model, $prompt, $maxTokens) {
            return $this->promptRunner->runStructured(
                $model,
                new BlogOutlineAgent,
                $prompt,
                self::OUTLINE_TIMEOUT,
                null,
                $maxTokens,
            );
        });

        $structured = $response->toArray();
        $this->logger->logSuccess($model, $user, 'blog.outline', ['topic' => $topic], $structured, (int) ((hrtime(true) - $started) / 1_000_000));

        return $structured;
    }

    /**
     * @param  array<string, mixed>  $outline
     * @param  list<string>  $priorHeadings
     * @param  array<string, mixed>  $options
     * @param  list<string>  $laterHeadings
     */
    private function requestSectionHtml(
        string $topic,
        array $outline,
        string $heading,
        string $brief,
        array $priorHeadings,
        array $options,
        ?User $user,
        LaravelAiModel $model,
        string $contentLength,
        bool $compact,
        ?int $maxTokens = null,
        array $laterHeadings = [],
    ): string {
        $language = $options['language'] ?? 'ar';
        $tone = $options['tone'] ?? 'professional';
        $articleTitle = trim((string) ($outline['title'] ?? '')) ?: $topic;
        $langLine = $language === 'en' ? 'Write this section in English.' : 'اكتب هذا القسم بالعربية.';
        $prior = $priorHeadings === [] ? '(لا يوجد بعد)' : implode(' | ', $priorHeadings);
        $laterLine = $laterHeadings === []
            ? ''
            : 'أقسام لاحقة (لا تتناولها هنا): '.implode(' | ', $laterHeadings);
        $styleGuide = $this->styleGuide();
        $budgetLine = BlogHtmlStyleGuide::sectionBudget($contentLength, $compact);

        $prompt = <<<PROMPT
اكتب قسماً واحداً فقط من مقال مدونة.

موضوع المقال: {$topic}
عنوان المقال: {$articleTitle}
القسم الحالي: {$heading}
ملخص القسم: {$brief}
أقسام سابقة (للتماسك، لا تكررها): {$prior}
{$laterLine}
الأسلوب: {$tone}
{$langLine}
{$budgetLine}

{$styleGuide}

ابدأ بـ <h2>{$heading}</h2> ولا تُرجع أقساماً أخرى.
أعد HTML فقط: ابدأ مباشرة بـ <h2 وانتهِ بآخر وسم، بدون JSON وبدون markdown وبدون أي شرح.
PROMPT;

        $started = hrtime(true);

        // Plain text, not structured output: making the model escape HTML inside a
        // JSON string is what collapsed code samples onto a single line.
        $response = $this->providerManager->runWithModel($model, function () use ($model, $prompt, $maxTokens) {
            return $this->promptRunner->runPlain(
                $model,
                new BlogSectionPlainAgent,
                $prompt,
                self::SECTION_TIMEOUT,
                null,
                $maxTokens,
            );
        });

        $raw = (string) $response->text;
        $this->logger->logSuccess(
            $model,
            $user,
            'blog.section'.($compact ? '.retry' : ''),
            ['heading' => $heading, 'compact' => $compact],
            ['html_len' => mb_strlen($raw)],
            (int) ((hrtime(true) - $started) / 1_000_000)
        );

        $html = $this->resultNormalizer->extractSectionHtml($raw);
        if ($html === '') {
            $html = $this->resultNormalizer->extractSectionHtml($this->normalizeHtml($raw));
        }

        return $html;
    }

    /**
     * Upper bound on completion tokens for one section.
     *
     * Mirrors AIBlogPostService::tokensForStage() so both engines give a long
     * article the same room.
     */
    public static function sectionTokenCeiling(string $contentLength): int
    {
        $cap = (int) config('ai.blog.section_max_tokens', 4096);

        return match ($contentLength) {
            'short' => min($cap, 2048),
            'long' => $cap,
            default => min($cap, 3072),
        };
    }

    private function sectionCountForLength(string $length): int
    {
        return match ($length) {
            'short' => 3,
            'long' => 8,
            default => 5,
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function wizardOptionsFromPayload(array $payload): array
    {
        $category = ! empty($payload['category_id'])
            ? BlogCategory::query()->find((int) $payload['category_id'])
            : null;

        return [
            'content_length' => $payload['content_length'] ?? 'medium',
            'tone' => $payload['tone'] ?? 'professional',
            'language' => $payload['language'] ?? 'ar',
            'category' => $category,
            'generate_seo' => $payload['generate_seo'] ?? true,
            'generate_og' => $payload['generate_og'] ?? true,
            'generate_twitter' => $payload['generate_twitter'] ?? true,
            'generate_schema' => $payload['generate_schema'] ?? true,
            'generate_keyword_synonyms' => $payload['generate_keyword_synonyms'] ?? true,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveLaravelModel(array $payload): LaravelAiModel
    {
        if (! empty($payload['laravel_ai_model_id'])) {
            $model = LaravelAiModel::query()
                ->where('id', (int) $payload['laravel_ai_model_id'])
                ->where('is_active', true)
                ->first();
            if ($model) {
                return $model;
            }
        }

        $model = LaravelAiModel::query()->activeOrdered()->forCapability('blog.generate')->first()
            ?? LaravelAiModel::query()->activeOrdered()->first();

        if (! $model) {
            throw new \RuntimeException('لا يوجد موديل Laravel AI نشط.');
        }

        return $model;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveLegacyModel(array $payload): AIModel
    {
        if (! empty($payload['ai_model_id'])) {
            $model = AIModel::query()->find((int) $payload['ai_model_id']);
            if ($model) {
                return $model;
            }
        }

        $model = $this->legacyModelService->getDefaultModel();
        if (! $model) {
            throw new \RuntimeException('لا يوجد موديل AI متاح.');
        }

        return $model;
    }

    private function normalizeHtml(string $content): string
    {
        return $this->resultNormalizer->normalizeHtmlString($content);
    }

    private function styleGuide(): string
    {
        return BlogHtmlStyleGuide::block();
    }

    private function friendlyError(Throwable $e): string
    {
        // Providers now tag their errors with [HTTP nnn] so the classifier can read
        // past the Arabic wording; the admin does not need to see the marker.
        $msg = AIProviderService::stripDiagnostics($e->getMessage());
        $lower = strtolower($msg);

        if ($e instanceof AiProviderException) {
            return match ($e->kind) {
                AiProviderException::KIND_AUTH => 'المزود رفض الطلب (مفتاح API أو الرصيد). راجع إعدادات الموديل ثم تابع التوليد — لن تُفقد الأقسام المكتملة. التفاصيل: '.$msg,
                AiProviderException::KIND_RATE_LIMIT => 'المزود يطبّق حد معدل على الطلبات. جرّب لاحقاً أو خفّض AI_BLOG_SECTION_MAX_TOKENS وارفع AI_BLOG_SECTION_DELAY_MS. التفاصيل: '.$msg,
                AiProviderException::KIND_TOO_LARGE => 'الطلب تجاوز حد الحجم عند المزود رغم تقليل max_tokens تلقائياً. خفّض AI_BLOG_SECTION_MAX_TOKENS ثم تابع التوليد. التفاصيل: '.$msg,
                default => 'فشل الاتصال بالمزود: '.$msg,
            };
        }

        if (str_contains($lower, 'timeout')) {
            return 'انتهت مهلة أحد مراحل التوليد. أعد المحاولة — المحتوى الطويل يُقسَّم إلى أقسام ويُعاد تلقائياً عند الحاجة.';
        }
        if ($this->promptRunner->isRetryableTokenOrSizeError($e)) {
            return 'تعذّر إكمال الطلب ضمن حد الرموز المقبول عند المزود رغم استخدام max_tokens من الموديل وإعادة المحاولة تلقائياً. '
                .'راجع قيمة max_tokens في لوحة الموديل (حد الإكمال وليس نافذة السياق) ثم أعد التوليد.';
        }
        if (str_contains($lower, 'api key')) {
            return 'مشكلة في API Key. تحقق من إعدادات الموديل.';
        }

        return 'فشل التوليد: '.$msg;
    }
}
