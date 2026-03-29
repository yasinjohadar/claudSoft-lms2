<?php

namespace App\Services\Ai;

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
        $content = isset($data['content']) ? trim((string) $data['content']) : '';

        if ($content === '') {
            $content = trim(strip_tags($response)) !== '' ? $response : '';
            if ($content === '') {
                throw new \Exception('لم يُستخرج محتوى صالح من الاستجابة. حاول مجدداً أو قلّل حجم النص.');
            }
        }

        $result = ['content' => $content];

        if ($updateExcerpt && ! empty($data['excerpt'])) {
            $result['excerpt'] = Str::limit(trim(strip_tags((string) $data['excerpt'])), 500);
        }

        return $result;
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

            $title = $contentData['title'] ?? $topic;
            $content = $contentData['content'] ?? '';
            $excerpt = $contentData['excerpt'] ?? $this->generateExcerpt($content, $language);
            $slug = $this->normalizeSlugFromTitle($contentData['slug'] ?? null, $title);

            $result = [
                'title' => $title,
                'slug' => $slug,
                'excerpt' => $excerpt,
                'content' => $content,
            ];

            if ($options['generate_meta'] ?? true) {
                $meta = $this->generateMetaFields($title, $content, $topic, $model, $language);
                $result['meta_title'] = $meta['meta_title'];
                $result['meta_description'] = $meta['meta_description'];
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
        $lengthMap = [
            'short' => '500-800 كلمة تقريباً',
            'medium' => '1000-1500 كلمة تقريباً',
            'long' => '2000-3000 كلمة تقريباً',
        ];

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

        $categoryLine = $category ? "القسم/المجلد: {$category->name}. " : '';
        $parentLine = $parent ? "هذه الصفحة فرع من صفحة بعنوان: «{$parent->title}» — اربط المحتوى بسياقها. " : '';

        $styleGuide = 'استخدم HTML فقط داخل حقل content مع هذه الأنماط (متوافقة مع واجهة التوثيق العامة):'."\n".$this->documentationStyleGuideBlock();

        $prompt = "أنت كاتب توثيق تقني. اكتب صفحة توثيق واحدة شاملة.

الموضوع أو المطلوب: {$topic}
{$categoryLine}{$parentLine}
الطول المستهدف: {$lengthMap[$contentLength]}
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
        $response = $provider->generateText($prompt, [
            'max_tokens' => $model->max_tokens ?? 8000,
            'temperature' => $model->temperature ?? 0.65,
        ]);

        if (empty($response)) {
            throw new \Exception('لم يتم استلام استجابة من موديل AI. يرجى المحاولة مرة أخرى.');
        }

        $data = $this->parseJSONResponse($response);

        if (! isset($data['title']) || ! isset($data['content'])) {
            return [
                'title' => $topic,
                'content' => $response,
                'excerpt' => $this->generateExcerpt($response, $language),
            ];
        }

        return $data;
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

            return [
                'meta_title' => Str::limit(trim($data['meta_title'] ?? $title), 255),
                'meta_description' => Str::limit(trim($data['meta_description'] ?? strip_tags($content)), 500),
            ];
        } catch (\Exception $e) {
            Log::warning('Doc meta generation fallback: '.$e->getMessage());

            return [
                'meta_title' => Str::limit($title, 60),
                'meta_description' => Str::limit(strip_tags($content), 160),
            ];
        }
    }

    private function generateExcerpt(string $content, string $language): string
    {
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);

        return Str::limit(trim($text), 200);
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
