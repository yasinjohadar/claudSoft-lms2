<?php

namespace App\Services\AiNew;

use App\Ai\Agents\DocumentationOutlineAgent;
use App\Ai\Agents\DocumentationRefineContentAgent;
use App\Ai\Agents\DocumentationSectionAgent;
use App\Models\AIModel;
use App\Models\DocumentationAiGeneration;
use App\Models\DocumentationCategory;
use App\Models\DocumentationPage;
use App\Models\LaravelAiModel;
use App\Models\User;
use App\Services\Ai\AIDocumentationPageService;
use App\Services\Ai\AIModelService;
use App\Services\Ai\DocumentationAiResultNormalizer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class DocumentationAiPipelineService
{
    private const SECTION_TIMEOUT = 180;

    private const OUTLINE_TIMEOUT = 120;

    private const CHUNK_TIMEOUT = 180;

    public function __construct(
        private LaravelAiProviderManager $providerManager,
        private LaravelAiPromptRunner $promptRunner,
        private LaravelAiRequestLogger $logger,
        private LaravelAiDocumentationService $laravelDocs,
        private AIDocumentationPageService $legacyDocs,
        private AIModelService $legacyModelService,
        private DocumentationAiResultNormalizer $resultNormalizer,
    ) {}

    public function run(DocumentationAiGeneration $generation): void
    {
        set_time_limit(0);

        $generation->refresh();
        if (in_array($generation->status, [
            DocumentationAiGeneration::STATUS_COMPLETED,
            DocumentationAiGeneration::STATUS_CANCELLED,
        ], true)) {
            return;
        }

        $generation->markRunning('starting', 'بدء المعالجة…', 2);

        try {
            $result = match ($generation->operation) {
                DocumentationAiGeneration::OPERATION_GENERATE => $this->runGenerate($generation),
                DocumentationAiGeneration::OPERATION_REFINE => $this->runTransform($generation, 'refine'),
                DocumentationAiGeneration::OPERATION_ENHANCE => $this->runTransform($generation, 'enhance'),
                default => throw new \InvalidArgumentException('عملية غير معروفة: '.$generation->operation),
            };

            $generation->markCompleted($result);
        } catch (Throwable $e) {
            Log::error('Documentation AI pipeline failed', [
                'uuid' => $generation->uuid,
                'operation' => $generation->operation,
                'message' => $e->getMessage(),
            ]);
            $generation->markFailed($this->friendlyError($e));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function runGenerate(DocumentationAiGeneration $generation): array
    {
        $payload = $generation->payload;
        $topic = trim((string) ($payload['topic'] ?? ''));
        if ($topic === '') {
            throw new \InvalidArgumentException('الموضوع مطلوب.');
        }

        $engine = (string) ($payload['docs_engine'] ?? 'legacy');
        $contentLength = (string) ($payload['content_length'] ?? 'medium');
        $options = $this->wizardOptionsFromPayload($payload);
        $useStaged = in_array($contentLength, ['medium', 'long'], true);

        Log::info('Documentation AI generate path', [
            'uuid' => $generation->uuid,
            'engine' => $engine,
            'content_length' => $contentLength,
            'staged' => $useStaged || $engine === 'laravel_ai',
        ]);

        // Legacy short: one-shot. Medium/long: staged outline+sections on legacy models.
        if ($engine !== 'laravel_ai') {
            if (! $useStaged) {
                $generation->markProgress('legacy_generate', 'توليد الصفحة (محرك قديم)…', 20);
                $model = $this->resolveLegacyModel($payload);
                $result = $this->legacyDocs->generateDocumentationPage($topic, $model, $options);
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
        DocumentationAiGeneration $generation,
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
        } else {
            $legacyModel = $this->resolveLegacyModel($payload);
        }

        $generation->markProgress('outline', 'بناء مخطط الأقسام…', 8);
        $outline = $useLaravelAi
            ? $this->generateOutline($topic, $options, $sectionTarget, $user, $laraModel)
            : $this->legacyDocs->generateDocumentationOutline($topic, $legacyModel, $options, $sectionTarget);

        $sections = $outline['sections'] ?? [];
        if (! is_array($sections) || count($sections) < 2) {
            throw new \RuntimeException('فشل بناء مخطط الأقسام. حاول مجدداً.');
        }

        $sections = array_values(array_slice($sections, 0, $sectionTarget + 2));
        $total = count($sections);
        $htmlParts = [];
        $priorHeadings = [];

        $generation->markProgress('outline', 'تم المخطط — بدء كتابة الأقسام', 15, [
            'title' => $outline['title'] ?? null,
            'sections_planned' => $total,
        ]);

        foreach ($sections as $index => $section) {
            $heading = trim((string) ($section['heading'] ?? ''));
            $brief = trim((string) ($section['brief'] ?? ''));
            if ($heading === '') {
                continue;
            }

            $pct = 15 + (int) floor((($index + 1) / max(1, $total)) * 70);
            $generation->markProgress(
                'section_'.($index + 1),
                'كتابة القسم '.($index + 1).' من '.$total.'…',
                $pct,
                [
                    'title' => $outline['title'] ?? null,
                    'sections_done' => $index,
                    'sections_planned' => $total,
                    'current_heading' => $heading,
                ]
            );

            if ($useLaravelAi) {
                $html = $this->generateSectionHtml(
                    $topic,
                    $outline,
                    $heading,
                    $brief,
                    $priorHeadings,
                    $options,
                    $user,
                    $laraModel,
                    $contentLength,
                );
            } else {
                $html = $this->generateLegacySectionHtml(
                    $topic,
                    $outline,
                    $heading,
                    $brief,
                    $priorHeadings,
                    $options,
                    $legacyModel,
                );
            }

            $htmlParts[] = $html;
            $priorHeadings[] = $heading;
        }

        $generation->markProgress('assemble', 'دمج الأقسام…', 90);

        $content = $this->normalizeHtml(implode("\n", array_filter($htmlParts)));
        if ($content === '') {
            throw new \RuntimeException('لم يُنتج محتوى HTML صالحاً بعد دمج الأقسام.');
        }

        $outlineTitle = trim((string) ($outline['title'] ?? ''));
        if ($outlineTitle === '' || $this->resultNormalizer->looksLikeInstructionPrompt($outlineTitle, $topic)) {
            throw new \RuntimeException('عنوان الصفحة من المخطط غير صالح. أعد التوليد.');
        }

        $excerpt = trim((string) ($outline['excerpt'] ?? ''));
        if ($excerpt === '' || $this->resultNormalizer->looksLikeJsonBlob($excerpt)) {
            $excerpt = $this->resultNormalizer->excerptFromHtml($content);
        }

        $shaped = $this->resultNormalizer->assertWizardShape([
            'title' => $outlineTitle,
            'slug' => $outline['slug'] ?? null,
            'excerpt' => $excerpt,
            'content' => $content,
        ], $topic);

        $result = [
            'title' => $shaped['title'],
            'slug' => $this->normalizeSlug(
                $shaped['slug'] ?? (isset($outline['slug']) ? trim((string) $outline['slug']) : null),
                $shaped['title']
            ),
            'excerpt' => $shaped['excerpt'],
            'content' => $shaped['content'],
        ];

        if ($payload['generate_meta'] ?? true) {
            $result['meta_title'] = $shaped['meta_title'];
            $result['meta_description'] = $shaped['meta_description'];
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $outline
     * @param  list<string>  $priorHeadings
     * @param  array<string, mixed>  $options
     */
    private function generateLegacySectionHtml(
        string $topic,
        array $outline,
        string $heading,
        string $brief,
        array $priorHeadings,
        array $options,
        AIModel $model,
    ): string {
        $html = $this->legacyDocs->generateDocumentationSectionHtml(
            $topic,
            $outline,
            $heading,
            $brief,
            $priorHeadings,
            $model,
            $options,
            compact: false,
        );

        if ($html === '') {
            Log::warning('Documentation AI legacy section empty — retrying compact prompt', [
                'heading' => $heading,
            ]);
            $html = $this->legacyDocs->generateDocumentationSectionHtml(
                $topic,
                $outline,
                $heading,
                $brief,
                $priorHeadings,
                $model,
                $options,
                compact: true,
            );
        }

        if ($html === '') {
            throw new \RuntimeException('فشل توليد قسم: '.$heading.' — أعد المحاولة أو راجع max_tokens للموديل.');
        }

        if (! str_contains($html, 'content-section')) {
            $html = '<section class="content-section"><h2 class="section-title">'.e($heading).'</h2>'.$html.'</section>';
        }

        return $html;
    }

    /**
     * @return array<string, mixed>
     */
    private function runTransform(DocumentationAiGeneration $generation, string $mode): array
    {
        $payload = $generation->payload;
        $sourceHtml = (string) ($payload['source_html'] ?? '');
        $len = mb_strlen($sourceHtml);
        if ($len < 1) {
            throw new \InvalidArgumentException('المحتوى المصدر مطلوب.');
        }
        if ($len > AIDocumentationPageService::MAX_REFINE_SOURCE_CHARS) {
            throw new \InvalidArgumentException(
                'المحتوى أطول من الحد المسموح ('.number_format(AIDocumentationPageService::MAX_REFINE_SOURCE_CHARS).' حرف).'
            );
        }

        $options = [
            'user_notes' => $payload['user_notes'] ?? null,
            'tone' => $payload['tone'] ?? 'professional',
            'language' => $payload['language'] ?? 'ar',
            'update_excerpt' => (bool) ($payload['update_excerpt'] ?? false),
        ];

        if ($mode === 'enhance') {
            $notes = trim((string) ($options['user_notes'] ?? ''));
            if ($notes === '' || mb_strlen($notes) < 10) {
                throw new \InvalidArgumentException('صف الأفكار أو الإضافات المطلوبة (10 أحرف على الأقل).');
            }
        }

        $engine = (string) ($payload['docs_engine'] ?? 'laravel_ai');
        $chunks = $this->splitHtmlChunks($sourceHtml);

        // Short content: single pass (still async)
        if (count($chunks) <= 1 || mb_strlen($sourceHtml) < 3500) {
            $generation->markProgress($mode, $mode === 'enhance' ? 'تطبيق الأفكار…' : 'تحسين المحتوى…', 25);
            if ($engine === 'laravel_ai') {
                $laraModel = $this->resolveLaravelModel($payload);
                $user = User::query()->find($generation->user_id);
                $data = $mode === 'enhance'
                    ? $this->laravelDocs->enhanceForLegacy($sourceHtml, $options, $user, $laraModel)
                    : $this->laravelDocs->refineForLegacy($sourceHtml, $options, $user, $laraModel);
            } else {
                $model = $this->resolveLegacyModel($payload);
                $data = $mode === 'enhance'
                    ? $this->legacyDocs->enhanceDocumentationContent($sourceHtml, $model, $options)
                    : $this->legacyDocs->refineDocumentationContent($sourceHtml, $model, $options);
                if ($mode === 'enhance' && ! isset($data['stats'])) {
                    $data['stats'] = AIDocumentationPageService::computeEnhanceStats($sourceHtml, $data['content']);
                }
            }
            $generation->markProgress($mode, 'اكتمل', 95);

            return $data;
        }

        if ($engine !== 'laravel_ai') {
            // Legacy: one shot inside job (avoids browser timeout)
            $generation->markProgress('legacy_'.$mode, 'معالجة المحتوى الطويل…', 30);
            $model = $this->resolveLegacyModel($payload);
            $data = $mode === 'enhance'
                ? $this->legacyDocs->enhanceDocumentationContent($sourceHtml, $model, $options)
                : $this->legacyDocs->refineDocumentationContent($sourceHtml, $model, $options);
            if ($mode === 'enhance' && ! isset($data['stats'])) {
                $data['stats'] = AIDocumentationPageService::computeEnhanceStats($sourceHtml, $data['content']);
            }

            return $data;
        }

        $laraModel = $this->resolveLaravelModel($payload);
        $user = User::query()->find($generation->user_id);
        $total = count($chunks);
        $out = [];

        foreach ($chunks as $index => $chunk) {
            $pct = 10 + (int) floor((($index + 1) / max(1, $total)) * 80);
            $generation->markProgress(
                'chunk_'.($index + 1),
                'معالجة الجزء '.($index + 1).' من '.$total.'…',
                $pct,
                ['chunks_done' => $index, 'chunks_total' => $total]
            );

            $structured = $this->transformChunk($chunk, $options, $mode, $user, $laraModel);
            $html = $this->resultNormalizer->extractSectionHtml((string) ($structured['content'] ?? ''));
            if ($html === '') {
                $html = $this->normalizeHtml((string) ($structured['content'] ?? ''));
            }
            if ($html !== '' && $this->resultNormalizer->isPlausibleHtml($html)) {
                $out[] = $html;
            } else {
                $out[] = $chunk; // keep original on empty/invalid
            }
        }

        $generation->markProgress('assemble', 'دمج الأجزاء…', 92);
        $content = $this->normalizeHtml(implode("\n", $out));
        if ($content === '' || ! $this->resultNormalizer->isPlausibleHtml($content)) {
            throw new \RuntimeException('فشل دمج المحتوى بعد التقسيم.');
        }

        $result = ['content' => $content];
        if ($options['update_excerpt'] ?? false) {
            $result['excerpt'] = Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($content)) ?: ''), 200);
        }
        if ($mode === 'enhance') {
            $result['stats'] = AIDocumentationPageService::computeEnhanceStats($sourceHtml, $content);
        }

        return $result;
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
    ): array {
        $language = $options['language'] ?? 'ar';
        $tone = $options['tone'] ?? 'professional';
        $length = $options['content_length'] ?? 'medium';
        /** @var DocumentationCategory|null $category */
        $category = $options['category'] ?? null;
        /** @var DocumentationPage|null $parent */
        $parent = $options['parent'] ?? null;

        $langLine = $language === 'en'
            ? 'Plan the page in English.'
            : 'خطط الصفحة بالعربية.';

        $categoryLine = $category ? "القسم: {$category->name}. " : '';
        $parentLine = $parent ? "فرع من: «{$parent->title}». " : '';

        $topicForPrompt = Str::limit(trim($topic), 1500);
        $buildPrompt = function (int $target) use ($topicForPrompt, $categoryLine, $parentLine, $length, $tone, $langLine): string {
            return <<<PROMPT
خطط صفحة توثيق شاملة ثم قسّمها إلى أقسام.

الموضوع: {$topicForPrompt}
{$categoryLine}{$parentLine}
عدد الأقسام المستهدف تقريباً: {$target}
طول المحتوى الإجمالي المستهدف: {$length}
الأسلوب: {$tone}
{$langLine}

أعد: title, slug, excerpt, sections[{heading, brief}].
كل brief جملة قصيرة توضّح ما سيُغطى في القسم فقط.
PROMPT;
        };

        $started = hrtime(true);
        $prompt = $buildPrompt($sectionTarget);

        try {
            /** @var \Laravel\Ai\Responses\StructuredAgentResponse $response */
            $response = $this->providerManager->runWithModel($model, function () use ($model, $prompt) {
                return $this->promptRunner->runStructured($model, new DocumentationOutlineAgent, $prompt, self::OUTLINE_TIMEOUT);
            });
        } catch (Throwable $e) {
            if (! $this->promptRunner->isRetryableTokenOrSizeError($e)) {
                throw $e;
            }

            // Keep generating: retry outline with a tighter prompt while still covering the topic.
            $retryTarget = max(4, (int) floor($sectionTarget * 0.75));
            Log::warning('Documentation AI outline hit provider size/token limit — retrying', [
                'section_target' => $sectionTarget,
                'retry_target' => $retryTarget,
                'model_id' => $model->id,
                'model_max_tokens' => $model->max_tokens,
                'error' => $e->getMessage(),
            ]);
            $prompt = $buildPrompt($retryTarget);
            $response = $this->providerManager->runWithModel($model, function () use ($model, $prompt) {
                return $this->promptRunner->runStructured($model, new DocumentationOutlineAgent, $prompt, self::OUTLINE_TIMEOUT);
            });
        }

        $structured = $response->toArray();
        $this->logger->logSuccess($model, $user, 'docs.outline', ['topic' => $topic], $structured, (int) ((hrtime(true) - $started) / 1_000_000));

        return $structured;
    }

    /**
     * @param  array<string, mixed>  $outline
     * @param  list<string>  $priorHeadings
     * @param  array<string, mixed>  $options
     */
    private function generateSectionHtml(
        string $topic,
        array $outline,
        string $heading,
        string $brief,
        array $priorHeadings,
        array $options,
        ?User $user,
        LaravelAiModel $model,
        string $contentLength = 'medium',
    ): string {
        $html = '';
        $sizeError = false;

        try {
            $html = $this->requestSectionHtml(
                $topic,
                $outline,
                $heading,
                $brief,
                $priorHeadings,
                $options,
                $user,
                $model,
                $contentLength,
                compact: false,
            );
        } catch (Throwable $e) {
            if (! $this->promptRunner->isRetryableTokenOrSizeError($e)) {
                throw $e;
            }
            $sizeError = true;
            Log::warning('Documentation AI section hit provider size/token limit — retrying compact', [
                'heading' => $heading,
                'content_length' => $contentLength,
                'error' => $e->getMessage(),
            ]);
        }

        if ($html === '' || $sizeError) {
            if (! $sizeError && $html === '') {
                Log::warning('Documentation AI section empty — retrying compact prompt', [
                    'heading' => $heading,
                    'content_length' => $contentLength,
                ]);
            }
            $html = $this->requestSectionHtml(
                $topic,
                $outline,
                $heading,
                $brief,
                $priorHeadings,
                $options,
                $user,
                $model,
                $contentLength,
                compact: true,
            );
        }

        if ($html === '') {
            throw new \RuntimeException('فشل توليد قسم: '.$heading.' — أعد المحاولة أو راجع max_tokens للموديل.');
        }

        if (! str_contains($html, 'content-section')) {
            $html = '<section class="content-section"><h2 class="section-title">'.e($heading).'</h2>'.$html.'</section>';
        }

        return $html;
    }

    /**
     * @param  array<string, mixed>  $outline
     * @param  list<string>  $priorHeadings
     * @param  array<string, mixed>  $options
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
    ): string {
        $language = $options['language'] ?? 'ar';
        $tone = $options['tone'] ?? 'professional';
        $pageTitle = trim((string) ($outline['title'] ?? '')) ?: $topic;
        $langLine = $language === 'en' ? 'Write this section in English.' : 'اكتب هذا القسم بالعربية.';
        $prior = $priorHeadings === [] ? '(لا يوجد بعد)' : implode(' | ', $priorHeadings);
        $styleGuide = $this->styleGuide();
        $compactLine = $compact
            ? "مهم: اجعل القسم أقصر. مثال كود واحد فقط، بدون تكرار، ركّز على الفكرة الأساسية."
            : '';

        $prompt = <<<PROMPT
اكتب قسماً واحداً فقط من صفحة توثيق.

موضوع الصفحة: {$topic}
عنوان الصفحة: {$pageTitle}
القسم الحالي: {$heading}
ملخص القسم: {$brief}
أقسام سابقة (للتماسك، لا تكررها): {$prior}
الأسلوب: {$tone}
{$langLine}
{$compactLine}

{$styleGuide}

لفّ القسم بـ <section class="content-section"> وابدأ بـ <h2 class="section-title">{$heading}</h2>
لا تُرجع أقساماً أخرى. أعد الحقل html فقط.
PROMPT;

        // Prefer the model's configured max_tokens; only suggest a floor when DB limit is unset.
        $preferMinTokens = in_array($contentLength, ['medium', 'long'], true) ? 8192 : null;
        if ($compact) {
            $preferMinTokens = null;
        }
        $started = hrtime(true);

        /** @var \Laravel\Ai\Responses\StructuredAgentResponse $response */
        $response = $this->providerManager->runWithModel($model, function () use ($model, $prompt, $preferMinTokens) {
            return $this->promptRunner->runStructured(
                $model,
                new DocumentationSectionAgent,
                $prompt,
                self::SECTION_TIMEOUT,
                $preferMinTokens,
            );
        });

        $structured = $response->toArray();
        $this->logger->logSuccess(
            $model,
            $user,
            'docs.section'.($compact ? '.retry' : ''),
            ['heading' => $heading, 'compact' => $compact],
            ['html_len' => mb_strlen((string) ($structured['html'] ?? ''))],
            (int) ((hrtime(true) - $started) / 1_000_000)
        );

        $html = $this->resultNormalizer->extractSectionHtml((string) ($structured['html'] ?? ''));
        if ($html === '') {
            $html = $this->resultNormalizer->extractSectionHtml(
                $this->normalizeHtml((string) ($structured['html'] ?? ''))
            );
        }

        return $html;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function transformChunk(
        string $chunkHtml,
        array $options,
        string $mode,
        ?User $user,
        LaravelAiModel $model,
    ): array {
        $tone = $options['tone'] ?? 'professional';
        $language = $options['language'] ?? 'ar';
        $notes = trim((string) ($options['user_notes'] ?? ''));
        $langLine = $language === 'en' ? 'Keep English.' : 'أبقِ العربية.';
        $modeLine = $mode === 'enhance'
            ? "طبّق هذه الأفكار على الجزء فقط:\n{$notes}"
            : ($notes !== '' ? "ملاحظات التحرير:\n{$notes}" : 'حسّن الوضوح والصياغة دون تغيير المعنى.');

        $prompt = <<<PROMPT
حرّر جزء HTML من صفحة توثيق (جزء واحد فقط).

{$modeLine}
الأسلوب: {$tone}
{$langLine}
حافظ على نفس بنية HTML ودليل الأنماط إن وُجدت (content-section, section-title, …).

<<<CHUNK>>>
{$chunkHtml}
<<<END_CHUNK>>>

أعد content (HTML للجزء فقط) و excerpt=null.
PROMPT;

        $started = hrtime(true);

        /** @var \Laravel\Ai\Responses\StructuredAgentResponse $response */
        $response = $this->providerManager->runWithModel($model, function () use ($model, $prompt) {
            return $this->promptRunner->runStructured($model, new DocumentationRefineContentAgent, $prompt, self::CHUNK_TIMEOUT);
        });

        $structured = $response->toArray();
        $this->logger->logSuccess($model, $user, 'docs.chunk_'.$mode, ['chunk_len' => mb_strlen($chunkHtml)], ['ok' => true], (int) ((hrtime(true) - $started) / 1_000_000));

        return $structured;
    }

    /**
     * @return list<string>
     */
    private function splitHtmlChunks(string $html): array
    {
        $html = trim($html);
        if ($html === '') {
            return [];
        }

        if (preg_match_all('/<section\b[^>]*class="[^"]*content-section[^"]*"[^>]*>.*?<\/section>/is', $html, $m) && count($m[0]) >= 2) {
            return array_values($m[0]);
        }

        if (preg_match_all('/(<h2\b[^>]*>.*?<\/h2>.*?)(?=<h2\b|$)/is', $html, $m) && count($m[1]) >= 2) {
            return array_values(array_map('trim', $m[1]));
        }

        $max = 4500;
        if (mb_strlen($html) <= $max) {
            return [$html];
        }

        $chunks = [];
        $remaining = $html;
        while (mb_strlen($remaining) > $max) {
            $slice = mb_substr($remaining, 0, $max);
            $break = mb_strrpos($slice, '</p>');
            if ($break === false) {
                $break = mb_strrpos($slice, '>');
            }
            $at = $break !== false ? $break + 1 : $max;
            $chunks[] = trim(mb_substr($remaining, 0, $at));
            $remaining = trim(mb_substr($remaining, $at));
        }
        if ($remaining !== '') {
            $chunks[] = $remaining;
        }

        return $chunks;
    }

    private function sectionCountForLength(string $length): int
    {
        return match ($length) {
            'short' => 4,
            'long' => 12,
            default => 7,
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function wizardOptionsFromPayload(array $payload): array
    {
        $category = ! empty($payload['documentation_category_id'])
            ? DocumentationCategory::query()->find((int) $payload['documentation_category_id'])
            : null;
        $parent = ! empty($payload['parent_id'])
            ? DocumentationPage::query()->find((int) $payload['parent_id'])
            : null;

        return [
            'content_length' => $payload['content_length'] ?? 'medium',
            'tone' => $payload['tone'] ?? 'professional',
            'language' => $payload['language'] ?? 'ar',
            'category' => $category,
            'parent' => $parent,
            'generate_meta' => $payload['generate_meta'] ?? true,
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

        $model = LaravelAiModel::query()->activeOrdered()->forCapability('docs.refine')->first()
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

    private function normalizeSlug(?string $slug, string $title): string
    {
        if ($slug !== null && trim($slug) !== '') {
            $s = preg_replace('/\s+/', '-', trim($slug));
            $s = preg_replace('/[^\p{Arabic}a-zA-Z0-9-]/u', '', (string) $s);
            $s = preg_replace('/-+/', '-', (string) $s);
            $s = trim((string) $s, '-');
            if ($s !== '') {
                return $s;
            }
        }

        $s = Str::slug($title, '-', 'ar');

        return $s !== '' ? $s : 'doc-'.time();
    }

    private function styleGuide(): string
    {
        return <<<'GUIDE'
استخدم HTML فقط:
- <section class="content-section">...</section>
- <h2 class="section-title">...</h2> و <h3 class="subsection-title"> عند الحاجة
- <div class="text-block">...</div>
- تنبيهات: <div class="info-box info|warning|success|error"><div class="info-box-title">...</div><p>...</p></div>
- جداول: <table class="styled-table">...
- أكواد: <pre><code class="language-php">...</code></pre>
- قوائم: <ul class="styled-list"><li>...</li></ul>
GUIDE;
    }

    private function friendlyError(Throwable $e): string
    {
        $msg = $e->getMessage();
        $lower = strtolower($msg);
        if (str_contains($lower, 'timeout')) {
            return 'انتهت مهلة أحد مراحل التوليد. أعد المحاولة — المحتوى الطويل يُقسَّم إلى أقسام ويُعاد تلقائياً عند الحاجة.';
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
