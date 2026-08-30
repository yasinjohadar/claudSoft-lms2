<?php

namespace App\Services\Ai;

use App\Exceptions\Ai\AiProviderException;
use App\Models\AIModel;
use App\Models\DocumentationCategory;
use App\Models\DocumentationPage;
use App\Services\Ai\Concerns\ParsesAiJsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AIDocumentationPageService
{
    use ParsesAiJsonResponse;

    /** الحد الأقصى لطول HTML المصدر عند التحسين (حماية من تجاوز سياق الموديل). */
    public const MAX_REFINE_SOURCE_CHARS = 180000;

    private ?DocumentationAiResultNormalizer $resultNormalizer = null;

    private ?AiErrorClassifier $errorClassifier = null;

    private ?DocumentationHtmlRepairer $repairer = null;

    private function resultNormalizer(): DocumentationAiResultNormalizer
    {
        return $this->resultNormalizer ??= new DocumentationAiResultNormalizer;
    }

    private function errorClassifier(): AiErrorClassifier
    {
        return $this->errorClassifier ??= new AiErrorClassifier;
    }

    private function repairer(): DocumentationHtmlRepairer
    {
        return $this->repairer ??= new DocumentationHtmlRepairer;
    }

    /**
     * إعادة صياغة وتحسين محتوى توثيق موجود (HTML).
     *
     * @param  array{
     *   user_notes?: string|null,
     *   tone?: string,
     *   language?: string,
     *   update_excerpt?: bool
     * }  $options
     * @return array{content: string, excerpt?: string}
     */
    public function refineDocumentationContent(
        string $rawHtml,
        AIModel $model,
        array $options = []
    ): array {
        $len = mb_strlen($rawHtml);
        if ($len > self::MAX_REFINE_SOURCE_CHARS) {
            throw new \InvalidArgumentException(
                'المحتوى أطول من الحد المسموح ('.number_format(self::MAX_REFINE_SOURCE_CHARS).' حرف، طولك الحالي: '.number_format($len).'). قلّص النص أو قسّمه إلى أجزاء.'
            );
        }

        $tone = $options['tone'] ?? 'professional';
        $language = $options['language'] ?? 'ar';
        $userNotes = trim((string) ($options['user_notes'] ?? ''));
        $updateExcerpt = (bool) ($options['update_excerpt'] ?? false);

        set_time_limit(500);

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

        $styleGuide = $this->documentationStyleGuideBlock();

        $excerptInstruction = $updateExcerpt
            ? 'أدرج في JSON مفتاح excerpt: نص عادي قصير (جملة أو اثنتان) يلخص الصفحة بدون HTML.'
            : 'لا تُدرج مفتاح excerpt في JSON.';

        $jsonShape = $updateExcerpt
            ? '{"content":"...","excerpt":"..."}'
            : '{"content":"..."}';

        $prompt = <<<PROMPT
أنت محرر توثيق تقني خبير. لديك HTML لصفحة توثيق (قد يكون فوضوياً أو ناقص التنسيق).

{$fullDocumentRule}{$notesBlock}{$instructionsPriorityRule}المطلوب:
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

أعد JSON فقط بدون markdown أو شرح، بالشكل:
{$jsonShape}
PROMPT;

        $provider = AIProviderFactory::create($model);
        $response = $provider->generateText($prompt, [
            'max_tokens' => $model->max_tokens ?? 8000,
            'temperature' => $model->temperature ?? 0.5,
        ]);

        if (empty($response)) {
            throw new \Exception('لم يتم استلام استجابة من موديل AI. يرجى المحاولة مرة أخرى.');
        }

        $data = $this->parseJSONResponse($response);
        $unwrapped = $this->resultNormalizer()->unwrapPayload(
            $data !== [] ? $data : $response
        );

        $content = trim((string) ($unwrapped['content'] ?? $unwrapped['html'] ?? ''));
        $content = $this->repairer()->repairDocument(
            $this->resultNormalizer()->normalizeHtmlString($content)
        );

        if ($content === '' || ! $this->resultNormalizer()->isPlausibleHtml($content)) {
            throw new \Exception('لم يُستخرج محتوى HTML صالح من الاستجابة. حاول مجدداً أو قلّل حجم النص.');
        }

        $result = ['content' => $content];

        if ($updateExcerpt) {
            $excerpt = trim((string) ($unwrapped['excerpt'] ?? ''));
            if ($excerpt === '' || $this->resultNormalizer()->looksLikeJsonBlob($excerpt)) {
                $excerpt = $this->resultNormalizer()->excerptFromHtml($content);
            }
            $result['excerpt'] = Str::limit(strip_tags($excerpt), 500);
        }

        return $result;
    }

    /**
     * إضافة أفكار ومحتوى جديد مع الحفاظ على المحتوى القديم (وضع enhance).
     *
     * @param  array{
     *   user_notes?: string|null,
     *   tone?: string,
     *   language?: string,
     *   update_excerpt?: bool
     * }  $options
     * @return array{content: string, excerpt?: string, stats: array<string, int>}
     */
    public function enhanceDocumentationContent(
        string $rawHtml,
        AIModel $model,
        array $options = []
    ): array {
        $len = mb_strlen($rawHtml);
        if ($len > self::MAX_REFINE_SOURCE_CHARS) {
            throw new \InvalidArgumentException(
                'المحتوى أطول من الحد المسموح ('.number_format(self::MAX_REFINE_SOURCE_CHARS).' حرف، طولك الحالي: '.number_format($len).'). قلّص النص أو قسّمه إلى أجزاء.'
            );
        }

        $userNotes = trim((string) ($options['user_notes'] ?? ''));
        if ($userNotes === '' || mb_strlen($userNotes) < 10) {
            throw new \InvalidArgumentException('صف الأفكار أو الإضافات المطلوبة (10 أحرف على الأقل).');
        }

        $tone = $options['tone'] ?? 'professional';
        $language = $options['language'] ?? 'ar';
        $updateExcerpt = (bool) ($options['update_excerpt'] ?? false);

        set_time_limit(500);

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
            ? 'أدرج في JSON مفتاح excerpt: نص عادي قصير (جملة أو اثنتان) يلخص الصفحة بدون HTML.'
            : 'لا تُدرج مفتاح excerpt في JSON.';

        $jsonShape = $updateExcerpt
            ? '{"content":"...","excerpt":"..."}'
            : '{"content":"..."}';

        $prompt = <<<PROMPT
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

أعد JSON فقط بدون markdown أو شرح، بالشكل:
{$jsonShape}
PROMPT;

        $provider = AIProviderFactory::create($model);
        $response = $provider->generateText($prompt, [
            'max_tokens' => $model->max_tokens ?? 8000,
            'temperature' => min(0.35, (float) ($model->temperature ?? 0.35)),
        ]);

        if (empty($response)) {
            throw new \Exception('لم يتم استلام استجابة من موديل AI. يرجى المحاولة مرة أخرى.');
        }

        $data = $this->parseJSONResponse($response);
        $unwrapped = $this->resultNormalizer()->unwrapPayload(
            $data !== [] ? $data : $response
        );

        $content = trim((string) ($unwrapped['content'] ?? $unwrapped['html'] ?? ''));
        $content = $this->repairer()->repairDocument(
            $this->resultNormalizer()->normalizeHtmlString($content)
        );

        if ($content === '' || ! $this->resultNormalizer()->isPlausibleHtml($content)) {
            throw new \Exception('لم يُستخرج محتوى HTML صالح من الاستجابة. حاول مجدداً أو قلّل حجم النص.');
        }

        $result = [
            'content' => $content,
            'stats' => self::computeEnhanceStats($rawHtml, $content),
        ];

        if ($updateExcerpt) {
            $excerpt = trim((string) ($unwrapped['excerpt'] ?? ''));
            if ($excerpt === '' || $this->resultNormalizer()->looksLikeJsonBlob($excerpt)) {
                $excerpt = $this->resultNormalizer()->excerptFromHtml($content);
            }
            $result['excerpt'] = Str::limit(strip_tags($excerpt), 500);
        }

        return $result;
    }

    /**
     * @return array{old_length: int, new_length: int, old_sections: int, new_sections: int}
     */
    public static function computeEnhanceStats(string $oldHtml, string $newHtml): array
    {
        return [
            'old_length' => mb_strlen($oldHtml),
            'new_length' => mb_strlen($newHtml),
            'old_sections' => self::countDocumentationSections($oldHtml),
            'new_sections' => self::countDocumentationSections($newHtml),
        ];
    }

    private static function countDocumentationSections(string $html): int
    {
        $lower = strtolower($html);
        $sections = substr_count($lower, 'content-section');
        if ($sections > 0) {
            return $sections;
        }

        return substr_count($lower, '<section');
    }

    private function documentationStyleGuideBlock(): string
    {
        return DocumentationHtmlStyleGuide::block();
    }

    /**
     * @param  array{
     *   content_length?: string,
     *   tone?: string,
     *   language?: string,
     *   category?: DocumentationCategory|null,
     *   parent?: DocumentationPage|null,
     *   generate_meta?: bool
     * }  $options
     * @return array<string, mixed>
     */
    public function generateDocumentationPage(
        string $topic,
        AIModel $model,
        array $options = []
    ): array {
        $contentLength = $options['content_length'] ?? 'medium';
        $tone = $options['tone'] ?? 'professional';
        $language = $options['language'] ?? 'ar';
        $category = $options['category'] ?? null;
        $parent = $options['parent'] ?? null;

        set_time_limit(500);

        try {
            $contentData = $this->generateContent(
                $topic,
                $model,
                $contentLength,
                $tone,
                $language,
                $category,
                $parent
            );

            $shaped = $this->resultNormalizer()->assertWizardShape([
                'title' => $contentData['title'] ?? null,
                'slug' => $contentData['slug'] ?? null,
                'excerpt' => $contentData['excerpt'] ?? null,
                'content' => $contentData['content'] ?? ($contentData['html'] ?? null),
                'meta_title' => $contentData['meta_title'] ?? null,
                'meta_description' => $contentData['meta_description'] ?? null,
            ], $topic);

            $title = $shaped['title'];
            $content = $this->repairer()->repairDocument($shaped['content']);
            $excerpt = $shaped['excerpt'];
            $slug = $this->normalizeSlugFromTitle($shaped['slug'] ?? ($contentData['slug'] ?? null), $title);

            $result = [
                'title' => $title,
                'slug' => $slug,
                'excerpt' => $excerpt,
                'content' => $content,
            ];

            if ($options['generate_meta'] ?? true) {
                $meta = $this->generateMetaFields($title, $content, $topic, $model, $language);
                $metaTitle = trim((string) ($meta['meta_title'] ?? ''));
                $metaDesc = trim((string) ($meta['meta_description'] ?? ''));
                if ($metaTitle === '' || $this->resultNormalizer()->looksLikeInstructionPrompt($metaTitle, $topic) || $this->resultNormalizer()->looksLikeJsonBlob($metaTitle)) {
                    $metaTitle = $shaped['meta_title'];
                }
                if ($metaDesc === '' || $this->resultNormalizer()->looksLikeJsonBlob($metaDesc)) {
                    $metaDesc = $shaped['meta_description'];
                }
                $result['meta_title'] = $metaTitle;
                $result['meta_description'] = $metaDesc;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Error generating documentation page: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'topic' => $topic,
                'model' => $model->name ?? 'unknown',
                'options' => $options,
            ]);

            throw $e;
        }
    }

    /**
     * Plan a documentation page as an outline (for staged medium/long generation).
     *
     * @param  array{
     *   content_length?: string,
     *   tone?: string,
     *   language?: string,
     *   category?: DocumentationCategory|null,
     *   parent?: DocumentationPage|null
     * }  $options
     * @return array{title?: string, slug?: string, excerpt?: string, sections?: list<array{heading?: string, brief?: string}>}
     */
    public function generateDocumentationOutline(
        string $topic,
        AIModel $model,
        array $options,
        int $sectionTarget,
        ?int $maxTokens = null,
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
        // "medium"/"long" means nothing to the model on its own; spell out the size.
        $pageBudget = DocumentationHtmlStyleGuide::pageBudget((string) $length);
        $topicForPrompt = Str::limit(trim($topic), 1500);

        $prompt = <<<PROMPT
خطط صفحة توثيق شاملة ثم قسّمها إلى أقسام.

الموضوع: {$topicForPrompt}
{$categoryLine}{$parentLine}
عدد الأقسام المستهدف تقريباً: {$sectionTarget}
طول المحتوى الإجمالي المستهدف: {$pageBudget}
الأسلوب: {$tone}
{$langLine}

غطِّ الموضوع بالكامل: المقدمة والأساسيات، الاستخدام العملي، الحالات المتقدمة، الأخطاء الشائعة، وأفضل الممارسات. لا تكرر أقساماً بنفس المعنى.

أعد النتيجة كـ JSON فقط بهذا الشكل:
{
  "title": "عنوان الصفحة",
  "slug": "slug-simple",
  "excerpt": "ملخص قصير بدون HTML",
  "sections": [
    {"heading": "عنوان القسم", "brief": "جملة قصيرة عما سيغطيه القسم"}
  ]
}

مهم: brief جملة قصيرة فقط. لا تكتب HTML هنا.
PROMPT;

        $response = $this->callProvider(
            $model,
            $prompt,
            $maxTokens ?? $this->tokensForStage($model, 'outline'),
            $model->temperature ?? 0.55,
        );

        $data = $this->parseJSONResponse($response);
        if ($data === []) {
            $data = $this->resultNormalizer()->unwrapPayload($response);
        }

        $sections = $data['sections'] ?? null;
        if (! is_array($sections) || count($sections) < 2) {
            throw new AiProviderException(
                'فشل بناء مخطط الأقسام من الموديل. غالباً قُطعت الاستجابة قبل اكتمال قائمة الأقسام.',
                AiProviderException::KIND_TOO_LARGE,
            );
        }

        return [
            'title' => trim((string) ($data['title'] ?? '')),
            'slug' => trim((string) ($data['slug'] ?? '')),
            'excerpt' => trim((string) ($data['excerpt'] ?? '')),
            'sections' => array_values($sections),
        ];
    }

    /**
     * Generate one documentation section as HTML (legacy staged path).
     *
     * @param  array<string, mixed>  $outline
     * @param  list<string>  $priorHeadings
     * @param  array{tone?: string, language?: string, content_length?: string}  $options
     * @param  list<string>  $laterHeadings
     */
    public function generateDocumentationSectionHtml(
        string $topic,
        array $outline,
        string $heading,
        string $brief,
        array $priorHeadings,
        AIModel $model,
        array $options = [],
        bool $compact = false,
        ?int $maxTokens = null,
        array $laterHeadings = [],
    ): string {
        $language = $options['language'] ?? 'ar';
        $tone = $options['tone'] ?? 'professional';
        $pageTitle = trim((string) ($outline['title'] ?? '')) ?: $topic;
        $langLine = $language === 'en' ? 'Write this section in English.' : 'اكتب هذا القسم بالعربية.';
        $prior = $priorHeadings === [] ? '(لا يوجد بعد)' : implode(' | ', $priorHeadings);
        $styleGuide = $this->documentationStyleGuideBlock();
        $contentLength = (string) ($options['content_length'] ?? 'medium');
        $budgetLine = DocumentationHtmlStyleGuide::sectionBudget($contentLength, $compact);
        $laterLine = $laterHeadings === []
            ? ''
            : 'أقسام لاحقة (لا تتناولها هنا): '.implode(' | ', $laterHeadings);

        // Never a JSON envelope: asking a model to escape HTML inside a JSON
        // string is what made it write whole programs on a single line.
        $outputRule = "أعد HTML فقط. ابدأ مباشرة بـ <section class=\"content-section\"> وانتهِ بـ </section>.\n"
            .'ممنوع JSON، ممنوع علامات markdown، ممنوع أي شرح قبل أو بعد HTML.';

        $prompt = <<<PROMPT
اكتب قسماً واحداً فقط من صفحة توثيق.

موضوع الصفحة: {$topic}
عنوان الصفحة: {$pageTitle}
القسم الحالي: {$heading}
ملخص القسم: {$brief}
أقسام سابقة (للتماسك، لا تكررها): {$prior}
{$laterLine}
الأسلوب: {$tone}
{$langLine}
{$budgetLine}

استخدم HTML فقط وفق الدليل:
{$styleGuide}

لفّ القسم بـ <section class="content-section"> وابدأ بـ <h2 class="section-title">{$heading}</h2>
لا تُرجع أقساماً أخرى.

{$outputRule}
PROMPT;

        $response = $this->callProvider(
            $model,
            $prompt,
            $maxTokens ?? $this->tokensForStage($model, 'section', $compact, $contentLength),
            $model->temperature ?? 0.65,
        );

        $rawHtml = $response;

        $html = $this->resultNormalizer()->extractSectionHtml($rawHtml);
        if ($html === '') {
            $html = $this->resultNormalizer()->extractSectionHtml(
                $this->normalizeGeneratedHtmlContent($rawHtml)
            );
        }

        if ($html === '') {
            throw new AiProviderException(
                'استجابة القسم لا تحتوي HTML صالحاً (غالباً قُطعت بسبب حد الرموز).',
                AiProviderException::KIND_TOO_LARGE,
            );
        }

        return $html;
    }

    /**
     * Single provider round-trip that turns the legacy "empty string + getLastError()"
     * convention into a classified exception the staged generator can act on.
     */
    private function callProvider(AIModel $model, string $prompt, int $maxTokens, float $temperature): string
    {
        $provider = AIProviderFactory::create($model);

        try {
            $response = $provider->generateText($prompt, [
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
            ]);
        } catch (AiProviderException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw $this->errorClassifier()->fromThrowable($e);
        }

        if (trim((string) $response) === '') {
            throw $this->errorClassifier()->toException($provider->getLastError());
        }

        return $response;
    }

    /**
     * Cap per-stage completion tokens.
     *
     * The cap sits on top of the model's own max_tokens and is sized by how long
     * the page is meant to be. A flat 4096 for every model truncated long sections
     * mid-tag — which is what drove the retry ladder down to its compact rungs and
     * produced pages that were both short and malformed.
     */
    public function tokensForStage(
        AIModel $model,
        string $stage,
        bool $compact = false,
        string $contentLength = 'medium',
    ): int {
        $sectionCap = (int) config('ai.docs.section_max_tokens', 8192);
        $cap = match ($stage) {
            'outline' => (int) config('ai.docs.outline_max_tokens', 3072),
            default => match ($contentLength) {
                'short' => min($sectionCap, 4096),
                'long' => $sectionCap,
                default => min($sectionCap, 6144),
            },
        };
        if ($compact) {
            $cap = (int) max(1024, floor($cap * 0.6));
        }

        $db = (int) ($model->max_tokens ?? 0);
        $effective = $db > 0 ? min($db, $cap) : $cap;

        return max(256, $effective);
    }

    /**
     * @return array{title?: string, content?: string, excerpt?: string, slug?: string}
     */
    private function generateContent(
        string $topic,
        AIModel $model,
        string $contentLength,
        string $tone,
        string $language,
        ?DocumentationCategory $category,
        ?DocumentationPage $parent
    ): array {
        $toneMap = [
            'professional' => 'احترافي وواضح',
            'friendly' => 'ودود ومبسّط',
            'technical' => 'تقني ومفصّل',
            'casual' => 'عادي',
            'formal' => 'رسمي',
        ];

        $langLine = $language === 'en'
            ? 'Write the page in clear English unless the topic requires Arabic terms.'
            : 'اكتب الصفحة بالعربية الفصحى الواضحة.';

        $pageBudget = DocumentationHtmlStyleGuide::pageBudget($contentLength);
        $categoryLine = $category ? "القسم/المجلد: {$category->name}. " : '';
        $parentLine = $parent ? "هذه الصفحة فرع من صفحة بعنوان: «{$parent->title}» — اربط المحتوى بسياقها. " : '';

        $styleGuide = 'استخدم HTML فقط داخل حقل content مع هذه الأنماط (متوافقة مع واجهة التوثيق العامة):'."\n".$this->documentationStyleGuideBlock();

        $prompt = "أنت كاتب توثيق تقني. اكتب صفحة توثيق واحدة شاملة.

الموضوع أو المطلوب: {$topic}
{$categoryLine}{$parentLine}
الطول المستهدف: {$pageBudget}
الأسلوب: {$toneMap[$tone]}
{$langLine}

{$styleGuide}

أعد النتيجة كـ JSON فقط بهذا الشكل (بدون markdown خارج JSON):
{
    \"title\": \"عنوان الصفحة القصير والواضح\",
    \"slug\": \"slug-latin-or-arabic-words-separated-by-hyphens\",
    \"excerpt\": \"جملة أو جملتان تلخصان الصفحة (بدون HTML)\",
    \"content\": \"المحتوى الكامل بصيغة HTML كما في الدليل أعلاه\"
}

مهم: slug يجب أن يكون بسيطاً (أحرف عربية أو لاتينية وأرقام وشرطات) بدون مسافات.";

        $provider = AIProviderFactory::create($model);
        $maxTokens = (int) ($model->max_tokens ?? 0);
        if ($maxTokens < 8000) {
            $maxTokens = max($maxTokens, 8000);
        }
        if ($maxTokens < 1) {
            $maxTokens = 8000;
        }

        $response = $provider->generateText($prompt, [
            'max_tokens' => $maxTokens,
            'temperature' => $model->temperature ?? 0.65,
        ]);

        if (empty($response)) {
            throw new \Exception('لم يتم استلام استجابة من موديل AI. يرجى المحاولة مرة أخرى.');
        }

        $data = $this->parseJSONResponse($response);
        $unwrapped = $this->resultNormalizer()->unwrapPayload(
            $data !== [] ? $data : $response
        );

        if (empty($unwrapped['title']) || (empty($unwrapped['content']) && empty($unwrapped['html']))) {
            throw new \Exception(
                'لم يُستخرج عنوان ومحتوى صالحان من الاستجابة. غالباً قُطعت الاستجابة بسبب حد الرموز (max_tokens). '
                .'جرّب طول «متوسط» أو «طويل» (توليد بالأقسام على مراحل)، أو ارفع max_tokens للموديل.'
            );
        }

        try {
            return $this->resultNormalizer()->assertWizardShape($unwrapped, $topic);
        } catch (\RuntimeException $e) {
            throw new \Exception($e->getMessage(), 0, $e);
        }
    }

    /**
     * @return array{meta_title: string, meta_description: string}
     */
    private function generateMetaFields(
        string $title,
        string $content,
        string $topic,
        AIModel $model,
        string $language
    ): array {
        $prompt = $language === 'en'
            ? "You are an SEO assistant. For this documentation page title: {$title}\nTopic: {$topic}\nReturn JSON only:\n{\"meta_title\":\"...\",\"meta_description\":\"...\"}\nmeta_title max 60 chars, meta_description max 160 chars."
            : "أنت مساعد SEO. لصفحة التوثيق التالية:
العنوان: {$title}
الموضوع: {$topic}

أعد JSON فقط:
{
    \"meta_title\": \"عنوان meta قصير 50-60 حرف\",
    \"meta_description\": \"وصف 150-160 حرف\"
}";

        try {
            $provider = AIProviderFactory::create($model);
            $response = $provider->generateText($prompt, [
                'max_tokens' => 400,
                'temperature' => 0.45,
            ]);
            $data = $this->parseJSONResponse($response);
            $metaTitle = trim((string) ($data['meta_title'] ?? $title));
            $metaDesc = trim((string) ($data['meta_description'] ?? ''));
            if ($metaTitle === '' || $this->resultNormalizer()->looksLikeInstructionPrompt($metaTitle) || $this->resultNormalizer()->looksLikeJsonBlob($metaTitle)) {
                $metaTitle = $title;
            }
            if ($metaDesc === '' || $this->resultNormalizer()->looksLikeJsonBlob($metaDesc)) {
                $metaDesc = $this->resultNormalizer()->excerptFromHtml($content);
            }

            return [
                'meta_title' => Str::limit($metaTitle, 255),
                'meta_description' => Str::limit($metaDesc, 500),
            ];
        } catch (\Exception $e) {
            Log::warning('Doc meta generation fallback: '.$e->getMessage());

            return [
                'meta_title' => Str::limit($title, 60),
                'meta_description' => Str::limit($this->resultNormalizer()->excerptFromHtml($content), 160),
            ];
        }
    }

    private function generateExcerpt(string $content, string $language): string
    {
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);

        return Str::limit(trim($text), 200);
    }

    private function normalizeGeneratedHtmlContent(string $content): string
    {
        return $this->resultNormalizer()->normalizeHtmlString($content);
    }

    private function normalizeSlugFromTitle(?string $slug, string $title): string
    {
        if ($slug !== null && trim($slug) !== '') {
            $s = preg_replace('/\s+/', '-', trim($slug));
            $s = preg_replace('/[^\p{Arabic}a-zA-Z0-9-]/u', '', $s);
            $s = preg_replace('/-+/', '-', $s);
            $s = trim($s, '-');
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
