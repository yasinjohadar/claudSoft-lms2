<?php

namespace App\Services\AiNew;

use App\Ai\Agents\DocumentationDraftAgent;
use App\Ai\Agents\DocumentationEnhanceContentAgent;
use App\Ai\Agents\DocumentationPageWizardAgent;
use App\Ai\Agents\DocumentationRefineContentAgent;
use App\Models\DocumentationCategory;
use App\Models\DocumentationPage;
use App\Models\LaravelAiModel;
use App\Services\Ai\AIDocumentationPageService;
use App\Services\Ai\DocumentationAiResultNormalizer;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Throwable;

class LaravelAiDocumentationService
{
    public function __construct(
        private LaravelAiProviderManager $providerManager,
        private LaravelAiRequestLogger $logger,
        private LaravelAiPromptRunner $promptRunner,
        private DocumentationAiResultNormalizer $resultNormalizer,
    ) {}

    /**
     * @return array{summary: string, sections: array<int, array{heading: string, body_html: string}>}
     */
    public function generateStructuredPage(
        string $brief,
        ?Authenticatable $user = null,
        ?LaravelAiModel $model = null,
        int $timeout = 120,
    ): array {
        $model ??= LaravelAiModel::query()->activeOrdered()->forCapability('docs.refine')->first()
            ?? LaravelAiModel::query()->activeOrdered()->first();

        if (! $model) {
            throw new \RuntimeException('No active Laravel AI model is configured.');
        }

        $prompt = "Produce documentation from this brief:\n\n{$brief}";
        $started = hrtime(true);
        $operation = 'docs.refine';

        try {
            /** @var StructuredAgentResponse $response */
            $response = $this->providerManager->runWithModel($model, function () use ($model, $prompt, $timeout) {
                $agent = new DocumentationDraftAgent;

                return $this->promptRunner->runStructured($model, $agent, $prompt, $timeout);
            });

            $latency = (int) ((hrtime(true) - $started) / 1_000_000);
            $structured = $response->toArray();

            $this->logger->logSuccess(
                $model,
                $user,
                $operation,
                ['brief' => $brief],
                $structured,
                $latency,
            );

            return $structured;
        } catch (Throwable $e) {
            $latency = (int) ((hrtime(true) - $started) / 1_000_000);
            Log::error('LaravelAiDocumentationService failed', ['exception' => $e->getMessage()]);

            $this->logger->logFailure(
                $model,
                $user,
                $operation,
                ['brief' => $brief],
                $e->getMessage(),
                $latency,
            );

            if (str_contains(strtolower($e->getMessage()), 'too large')) {
                throw new \RuntimeException(
                    'طلب التوليد تجاوز حد الحجم أو الرموز عند المزود. جرّب «طول محتوى» أقصر أو موديلاً آخر.',
                    0,
                    $e
                );
            }

            throw $e;
        }
    }

    /**
     * Same shape as AIDocumentationPageService::generateDocumentationPage for the admin wizard JSON.
     *
     * @param  array{
     *     content_length?: string,
     *     tone?: string,
     *     language?: string,
     *     category?: DocumentationCategory|null,
     *     parent?: DocumentationPage|null,
     *     generate_meta?: bool
     * }  $options
     * @return array<string, mixed>
     */
    public function generateForLegacyWizard(
        string $topic,
        array $options,
        ?Authenticatable $user = null,
        ?LaravelAiModel $explicitModel = null,
        int $timeout = 300,
    ): array {
        set_time_limit(500);

        $topic = Str::limit(trim($topic), 4000);
        $prompt = $this->buildWizardPrompt($topic, $options);
        $structured = $this->executeWizardDraft($prompt, $user, $explicitModel, $timeout, [
            'topic' => $topic,
            'wizard' => true,
        ]);

        return $this->expandWizardPayload($structured, $topic, $options);
    }

    /**
     * Same contract as AIDocumentationPageService::refineDocumentationContent (admin refine JSON).
     *
     * @param  array{
     *     user_notes?: string|null,
     *     tone?: string,
     *     language?: string,
     *     update_excerpt?: bool
     * }  $options
     * @return array{content: string, excerpt?: string}
     */
    public function refineForLegacy(
        string $rawHtml,
        array $options,
        ?Authenticatable $user = null,
        ?LaravelAiModel $explicitModel = null,
        int $timeout = 300,
    ): array {
        $len = mb_strlen($rawHtml);
        if ($len > AIDocumentationPageService::MAX_REFINE_SOURCE_CHARS) {
            throw new \InvalidArgumentException(
                'المحتوى أطول من الحد المسموح ('.number_format(AIDocumentationPageService::MAX_REFINE_SOURCE_CHARS).' حرف، طولك الحالي: '.number_format($len).'). قلّص النص أو قسّمه إلى أجزاء.'
            );
        }

        set_time_limit(500);

        $prompt = $this->buildRefinePrompt($rawHtml, $options);
        $structured = $this->executeRefineDraft($prompt, $user, $explicitModel, $timeout, [
            'refine' => true,
            'source_chars' => $len,
        ]);

        return $this->expandRefinePayload($structured, $options);
    }

    /**
     * Same contract as AIDocumentationPageService::enhanceDocumentationContent (admin enhance JSON).
     *
     * @param  array{
     *     user_notes?: string|null,
     *     tone?: string,
     *     language?: string,
     *     update_excerpt?: bool
     * }  $options
     * @return array{content: string, excerpt?: string, stats: array<string, int>}
     */
    public function enhanceForLegacy(
        string $rawHtml,
        array $options,
        ?Authenticatable $user = null,
        ?LaravelAiModel $explicitModel = null,
        int $timeout = 300,
    ): array {
        $len = mb_strlen($rawHtml);
        if ($len > AIDocumentationPageService::MAX_REFINE_SOURCE_CHARS) {
            throw new \InvalidArgumentException(
                'المحتوى أطول من الحد المسموح ('.number_format(AIDocumentationPageService::MAX_REFINE_SOURCE_CHARS).' حرف، طولك الحالي: '.number_format($len).'). قلّص النص أو قسّمه إلى أجزاء.'
            );
        }

        $userNotes = trim((string) ($options['user_notes'] ?? ''));
        if ($userNotes === '' || mb_strlen($userNotes) < 10) {
            throw new \InvalidArgumentException('صف الأفكار أو الإضافات المطلوبة (10 أحرف على الأقل).');
        }

        set_time_limit(500);

        $prompt = $this->buildEnhancePrompt($rawHtml, $options);
        $structured = $this->executeRefineDraft($prompt, $user, $explicitModel, $timeout, [
            'enhance' => true,
            'source_chars' => $len,
        ], 'enhance');

        $result = $this->expandRefinePayload($structured, $options);
        $result['stats'] = AIDocumentationPageService::computeEnhanceStats($rawHtml, $result['content']);

        return $result;
    }

    /**
     * @param  array{
     *     user_notes?: string|null,
     *     tone?: string,
     *     language?: string,
     *     update_excerpt?: bool
     * }  $options
     */
    private function buildEnhancePrompt(string $rawHtml, array $options): string
    {
        $tone = $options['tone'] ?? 'professional';
        $language = $options['language'] ?? 'ar';
        $userNotes = trim((string) ($options['user_notes'] ?? ''));
        $updateExcerpt = (bool) ($options['update_excerpt'] ?? false);

        $toneMap = [
            'professional' => 'احترافي وواضح',
            'friendly' => 'ودود ومبسّط',
            'technical' => 'تقني ودقيق',
            'casual' => 'عادي',
            'formal' => 'رسمي',
        ];
        $toneLabel = $toneMap[$tone] ?? $toneMap['professional'];

        $langLine = $language === 'en'
            ? 'Output documentation body in clear English where appropriate; keep code and identifiers as needed.'
            : 'المخرجات بالعربية الفصحى الواضحة حيث يناسب؛ احتفظ بالرموز البرمجية كما هي.';

        $styleGuide = $this->documentationStyleGuideBlock();

        $excerptInstruction = $updateExcerpt
            ? 'أعد في الحقل المنظم excerpt نصاً عادياً قصيراً (جملة أو اثنتان) يلخص الصفحة بدون HTML.'
            : 'اجعل excerpt قيمة null (لا تقترح مقتطفاً).';

        return <<<PROMPT
أنت محرر توثيق تقني خبير في وضع «إضافة أفكار». لديك HTML لصفحة توثيق موجودة.

قواعد صارمة — الأولوية للحفاظ على المحتوى القديم:
1. احتفظ بكل الأقسام والفقرات والجداول وأكواد SOURCE_HTML كما هي (نفس المعنى والترتيب) ما لم يطلب المحرر صراحةً حذف شيء.
2. لا تعِد هيكلة الصفحة بالكامل ولا تختصر المحتوى الموجود.
3. أضف فقط ما طلبه المحرر في «تعليمات الإضافة» أدناه — بأسلوب احترافي متسق مع بقية الصفحة.
4. ضع الإضافات في المواضع المنطقية (بعد مقدمة، قبل خاتمة، أو في قسم جديد `<section class="content-section">` في النهاية إن لم يُحدد موضع).
5. طبّق تنسيق HTML حسب الدليل أدناه للمحتوى الجديد.
6. الأسلوب: {$toneLabel}
7. {$langLine}
8. {$excerptInstruction}

تعليمات الإضافة من المحرر (نفّذها بدقة):
{$userNotes}

دليل التنسيق (HTML فقط داخل content):
{$styleGuide}

المحتوى الأصلي (HTML) — يجب الحفاظ عليه مع الإضافات:
<<<SOURCE_HTML>>>
{$rawHtml}
<<<END_SOURCE_HTML>>>

أعد الحقول المنظمة: content (HTML كامل مع الإضافات)، وexcerpt (نص عادي بدون HTML أو null حسب البند 8 أعلاه).
PROMPT;
    }

    /**
     * @param  array{
     *     user_notes?: string|null,
     *     tone?: string,
     *     language?: string,
     *     update_excerpt?: bool
     * }  $options
     */
    private function buildRefinePrompt(string $rawHtml, array $options): string
    {
        $tone = $options['tone'] ?? 'professional';
        $language = $options['language'] ?? 'ar';
        $userNotes = trim((string) ($options['user_notes'] ?? ''));
        $updateExcerpt = (bool) ($options['update_excerpt'] ?? false);

        $toneMap = [
            'professional' => 'احترافي وواضح',
            'friendly' => 'ودود ومبسّط',
            'technical' => 'تقني ودقيق',
            'casual' => 'عادي',
            'formal' => 'رسمي',
        ];
        $toneLabel = $toneMap[$tone] ?? $toneMap['professional'];

        $langLine = $language === 'en'
            ? 'Output documentation body in clear English where appropriate; keep code and identifiers as needed.'
            : 'المخرجات بالعربية الفصحى الواضحة حيث يناسب؛ احتفظ بالرموز البرمجية كما هي.';

        $notesBlock = $userNotes !== ''
            ? "تعليمات المحرر (نفّذها بدقة على كامل المستند؛ تجاهل ما يتعارض مع الدقة التقنية):\n{$userNotes}\n\n"
            : '';

        $fullDocumentRule = "قبل الإخراج النهائي: اقرأ وحلّل المحتوى بين علامتي SOURCE_HTML وEND_SOURCE_HTML بالكامل (كل الأقسام والفقرات والجداول والأكواد). لا تقتصر المراجعة أو إعادة الصياغة على جزء واحد من المستند.\n\n";

        $instructionsPriorityRule = $userNotes !== ''
            ? "عند وجود تعليمات المحرر أعلاه، فليكن تطبيقها على كامل SOURCE_HTML أولويةً على أي تحسين عام «زائد» متى لم يتعارض ذلك مع صحة المعلومات التقنية أو معنى الكود.\n\n"
            : '';

        $devRequirementsRule = "إذا وردت في تعليمات المحرر متطلبات تطوير أو مواصفات أو سيناريوهات استخدام، ادمجها في التوثيق بوضوح واحترافية مع الحفاظ على دقة الوصف التقني وعدم اختلاق تفاصيل غير مذكورة.\n\n";

        $styleGuide = $this->documentationStyleGuideBlock();

        $excerptInstruction = $updateExcerpt
            ? 'أعد في الحقل المنظم excerpt نصاً عادياً قصيراً (جملة أو اثنتان) يلخص الصفحة بدون HTML.'
            : 'اجعل excerpt قيمة null (لا تقترح مقتطفاً).';

        return <<<PROMPT
أنت محرر توثيق تقني خبير. لديك HTML لصفحة توثيق (قد يكون فوضوياً أو ناقص التنسيق).

{$fullDocumentRule}{$notesBlock}{$instructionsPriorityRule}{$devRequirementsRule}المطلوب:
1. أعد هيكلة المحتوى وصقل اللغة مع الحفاظ على المعنى التقني والصحة.
2. أزل التكرار والحشو والعناصر الفارغة غير المفيدة ما لم تمنع تعليمات المحرر أعلاه.
3. طبّق تنسيق HTML حسب الدليل أدناه (استخدم الأقسام والكلاسات المناسبة).
4. صحح الأخطاء الإملائية البسيطة عندما لا تغيّر معنى الكود.
5. الأسلوب المطلوب: {$toneLabel}
6. {$langLine}
7. {$excerptInstruction}

دليل التنسيق (HTML فقط داخل content):
{$styleGuide}

المحتوى الأصلي (HTML) بين علامات:
<<<SOURCE_HTML>>>
{$rawHtml}
<<<END_SOURCE_HTML>>>

أعد الحقول المنظمة: content (HTML كامل محسّن)، وexcerpt (نص عادي بدون HTML أو null حسب البند 7 أعلاه).
PROMPT;
    }

    /**
     * @return array<string, mixed>
     */
    private function executeRefineDraft(
        string $prompt,
        ?Authenticatable $user,
        ?LaravelAiModel $model,
        int $timeout,
        array $logContext,
        string $mode = 'refine',
    ): array {
        $model ??= LaravelAiModel::query()->activeOrdered()->forCapability('docs.refine')->first()
            ?? LaravelAiModel::query()->activeOrdered()->first();

        if (! $model) {
            throw new \RuntimeException('لا يوجد موديل Laravel AI نشط. أضف موديلاً من لوحة «موديلات Laravel AI SDK» مع القدرة docs.refine إن رغبت.');
        }

        $started = hrtime(true);
        $operation = $mode === 'enhance' ? 'docs.enhance' : 'docs.refine';

        try {
            /** @var StructuredAgentResponse $response */
            $response = $this->providerManager->runWithModel($model, function () use ($model, $prompt, $timeout, $mode) {
                $agent = $mode === 'enhance'
                    ? new DocumentationEnhanceContentAgent
                    : new DocumentationRefineContentAgent;

                return $this->promptRunner->runStructured($model, $agent, $prompt, $timeout);
            });

            $latency = (int) ((hrtime(true) - $started) / 1_000_000);
            $structured = $response->toArray();
            if (empty($structured['content']) && $response->text !== '') {
                $structured['text'] = $response->text;
            }

            $this->logger->logSuccess(
                $model,
                $user,
                $operation,
                $logContext,
                $structured,
                $latency,
            );

            return $structured;
        } catch (Throwable $e) {
            $latency = (int) ((hrtime(true) - $started) / 1_000_000);
            Log::error('LaravelAiDocumentationService refine failed', ['exception' => $e->getMessage()]);

            $this->logger->logFailure(
                $model,
                $user,
                $operation,
                $logContext,
                $e->getMessage(),
                $latency,
            );

            if (str_contains(strtolower($e->getMessage()), 'too large')) {
                throw new \RuntimeException(
                    'طلب التحسين تجاوز حد الحجم أو الرموز عند المزود. جرّب تقسيم المحتوى أو خفّض max_tokens في الموديل.',
                    0,
                    $e
                );
            }

            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $structured
     * @param  array{update_excerpt?: bool}  $options
     * @return array{content: string, excerpt?: string}
     */
    private function expandRefinePayload(array $structured, array $options): array
    {
        $updateExcerpt = (bool) ($options['update_excerpt'] ?? false);
        $unwrapped = $this->resultNormalizer->unwrapPayload($structured);
        $content = trim((string) ($unwrapped['content'] ?? $unwrapped['html'] ?? ''));
        $content = $this->resultNormalizer->normalizeHtmlString($content);

        if (($content === '' || ! $this->resultNormalizer->isPlausibleHtml($content)) && ! empty($structured['text'])) {
            $fromText = $this->resultNormalizer->unwrapPayload((string) $structured['text']);
            $content = trim((string) ($fromText['content'] ?? $fromText['html'] ?? ''));
            $content = $this->resultNormalizer->normalizeHtmlString($content);
            if ($content === '' || ! $this->resultNormalizer->isPlausibleHtml($content)) {
                $content = $this->resultNormalizer->extractSectionHtml((string) $structured['text']);
            }
        }

        if ($content === '' || ! $this->resultNormalizer->isPlausibleHtml($content)) {
            throw new \RuntimeException('لم يُرجع الموديل محتوى HTML صالحاً. حاول مجدداً أو قلّل حجم النص.');
        }

        $result = ['content' => $content];

        if ($updateExcerpt) {
            $rawExcerpt = trim((string) ($unwrapped['excerpt'] ?? ($structured['excerpt'] ?? '')));
            if ($rawExcerpt === '' || $this->resultNormalizer->looksLikeJsonBlob($rawExcerpt)) {
                $rawExcerpt = $this->resultNormalizer->excerptFromHtml($content);
            }
            $result['excerpt'] = Str::limit(strip_tags($rawExcerpt), 500);
        }

        return $result;
    }

    /**
     * @return array{title: string, slug: string, excerpt: string, content: string}
     */
    private function executeWizardDraft(
        string $prompt,
        ?Authenticatable $user,
        ?LaravelAiModel $model,
        int $timeout,
        array $logContext,
    ): array {
        $model ??= LaravelAiModel::query()->activeOrdered()->forCapability('docs.refine')->first()
            ?? LaravelAiModel::query()->activeOrdered()->first();

        if (! $model) {
            throw new \RuntimeException('لا يوجد موديل Laravel AI نشط. أضف موديلاً من لوحة «موديلات Laravel AI SDK» مع القدرة docs.refine إن رغبت.');
        }

        $started = hrtime(true);
        $operation = 'docs.refine';

        try {
            /** @var StructuredAgentResponse $response */
            $response = $this->providerManager->runWithModel($model, function () use ($model, $prompt, $timeout) {
                $agent = new DocumentationPageWizardAgent;

                return $this->promptRunner->runStructured($model, $agent, $prompt, $timeout);
            });

            $latency = (int) ((hrtime(true) - $started) / 1_000_000);
            $structured = $response->toArray();

            $this->logger->logSuccess(
                $model,
                $user,
                $operation,
                $logContext,
                $structured,
                $latency,
            );

            return $structured;
        } catch (Throwable $e) {
            $latency = (int) ((hrtime(true) - $started) / 1_000_000);
            Log::error('LaravelAiDocumentationService wizard failed', ['exception' => $e->getMessage()]);

            $this->logger->logFailure(
                $model,
                $user,
                $operation,
                $logContext,
                $e->getMessage(),
                $latency,
            );

            if (str_contains(strtolower($e->getMessage()), 'too large')) {
                throw new \RuntimeException(
                    'طلب التوليد تجاوز حد الحجم أو الرموز عند المزود (مثلاً Groq). جرّب «طول محتوى» أقصر، أو موديلاً آخر.',
                    0,
                    $e
                );
            }

            throw $e;
        }
    }

    /**
     * @param  array{
     *     content_length?: string,
     *     tone?: string,
     *     language?: string,
     *     category?: DocumentationCategory|null,
     *     parent?: DocumentationPage|null,
     * }  $options
     */
    private function buildWizardPrompt(string $topic, array $options): string
    {
        $contentLength = $options['content_length'] ?? 'medium';
        $tone = $options['tone'] ?? 'professional';
        $language = $options['language'] ?? 'ar';
        /** @var DocumentationCategory|null $category */
        $category = $options['category'] ?? null;
        /** @var DocumentationPage|null $parent */
        $parent = $options['parent'] ?? null;

        $lengthMap = [
            'short' => '500-800 كلمة تقريباً',
            'medium' => '1000-1500 كلمة تقريباً',
            'long' => '2000-3000 كلمة تقريباً',
        ];
        $lengthText = $lengthMap[$contentLength] ?? $lengthMap['medium'];

        $toneMap = [
            'professional' => 'احترافي وواضح',
            'friendly' => 'ودود ومبسّط',
            'technical' => 'تقني ومفصّل',
            'casual' => 'عادي',
            'formal' => 'رسمي',
        ];
        $toneText = $toneMap[$tone] ?? $toneMap['professional'];

        $langLine = $language === 'en'
            ? 'Write the page in clear English unless the topic requires Arabic terms.'
            : 'اكتب الصفحة بالعربية الفصحى الواضحة.';

        $categoryLine = $category ? "القسم/المجلد: {$category->name}. " : '';
        $parentLine = $parent ? "هذه الصفحة فرع من صفحة بعنوان: «{$parent->title}» — اربط المحتوى بسياقها. " : '';

        $styleGuide = 'استخدم HTML فقط داخل حقل content مع هذه الأنماط (متوافقة مع واجهة التوثيق العامة):'."\n".$this->documentationStyleGuideBlock();

        return <<<PROMPT
أنت كاتب توثيق تقني. اكتب صفحة توثيق واحدة شاملة.

الموضوع أو المطلوب: {$topic}
{$categoryLine}{$parentLine}
الطول المستهدف: {$lengthText}
الأسلوب: {$toneText}
{$langLine}

{$styleGuide}

أعد الحقول المنظمة (title, slug, excerpt بدون HTML, content بصيغة HTML كما في الدليل).
مهم: slug بسيط (أحرف عربية أو لاتينية وأرقام وشرطات) بدون مسافات.
PROMPT;
    }

    private function documentationStyleGuideBlock(): string
    {
        return <<<'GUIDE'
- لفّ كل قسم رئيسي بـ: <section class="content-section"> ... </section>
- عناوين فرعية: <h2 class="section-title">النص</h2> وعند الحاجة <h3 class="subsection-title">...</h3>
- فقرات توضيحية: <div class="text-block">...</div>
- تنبيهات: <div class="info-box info|warning|success|error"><div class="info-box-title">عنوان</div><p>...</p></div>
- جداول: <table class="styled-table"><thead><tr><th>...</th></tr></thead><tbody><tr><td>...</td></tr></tbody></table>
- أكواد: <pre><code class="language-php">...</code></pre> (أو language-bash, language-json, language-html حسب الحاجة) بدون div code-block
- قوائم عند الحاجة: <ul class="styled-list"><li>...</li></ul>
GUIDE;
    }

    /**
     * @param  array<string, mixed>  $structured
     * @param  array{generate_meta?: bool}  $options
     * @return array<string, mixed>
     */
    private function expandWizardPayload(array $structured, string $topic, array $options): array
    {
        $shaped = $this->resultNormalizer->assertWizardShape($structured, $topic);

        $slug = $this->normalizeSlugFromTitle(
            $shaped['slug'] ?? (isset($structured['slug']) ? trim((string) $structured['slug']) : null),
            $shaped['title']
        );

        $result = [
            'title' => $shaped['title'],
            'slug' => $slug,
            'excerpt' => $shaped['excerpt'],
            'content' => $shaped['content'],
        ];

        if ($options['generate_meta'] ?? true) {
            $result['meta_title'] = $shaped['meta_title'];
            $result['meta_description'] = $shaped['meta_description'];
        }

        return $result;
    }

    private function normalizeGeneratedHtmlContent(string $content): string
    {
        $normalized = $this->resultNormalizer->normalizeHtmlString($content);
        if ($this->resultNormalizer->looksLikeJsonBlob($normalized)) {
            $extracted = $this->resultNormalizer->extractSectionHtml($normalized);

            return $extracted;
        }

        return $normalized;
    }

    private function normalizeSlugFromTitle(?string $slug, string $title): string
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
        if ($s === '') {
            $s = 'doc-'.time();
        }

        return $s;
    }
}
