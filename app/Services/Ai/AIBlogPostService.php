<?php

namespace App\Services\Ai;

use App\Exceptions\Ai\AiProviderException;
use App\Models\AIModel;
use App\Models\BlogCategory;
use App\Services\Ai\Concerns\ParsesAiJsonResponse;
use Illuminate\Support\Facades\Log;
use App\Services\Ai\AIProviderFactory;
use Illuminate\Support\Str;

class AIBlogPostService
{
    use ParsesAiJsonResponse;

    private ?BlogAiResultNormalizer $resultNormalizer = null;

    private ?AiErrorClassifier $errorClassifier = null;

    private ?BlogHtmlRepairer $repairer = null;

    public function __construct(
        private AIModelService $modelService
    ) {}

    private function resultNormalizer(): BlogAiResultNormalizer
    {
        return $this->resultNormalizer ??= new BlogAiResultNormalizer;
    }

    private function errorClassifier(): AiErrorClassifier
    {
        return $this->errorClassifier ??= new AiErrorClassifier;
    }

    private function repairer(): BlogHtmlRepairer
    {
        return $this->repairer ??= new BlogHtmlRepairer;
    }

    /**
     * توليد مقال كامل بالذكاء الاصطناعي
     */
    public function generateBlogPost(
        string $topic,
        AIModel $model,
        array $options = []
    ): array {
        $contentLength = $options['content_length'] ?? 'medium';
        $tone = $options['tone'] ?? 'professional';
        $language = $options['language'] ?? 'ar';
        $category = $options['category'] ?? null;

        // زيادة وقت التنفيذ إلى 500 ثانية لتوليد المقالات الطويلة
        set_time_limit(500);

        try {
            // توليد المحتوى الرئيسي
            $contentData = $this->generateContent($topic, $model, $contentLength, $tone, $language, $category);
            
            $title = $contentData['title'] ?? $topic;
            $content = $contentData['content'] ?? '';
            $excerpt = $contentData['excerpt'] ?? $this->generateExcerpt($content, $language);
            $slug = $this->generateSlug($title);

            $result = [
                'title' => $title,
                'slug' => $slug,
                'excerpt' => $excerpt,
                'content' => $content,
            ];

            return $this->expandGeneratedPost($result, $topic, $model, $options);

        } catch (\Exception $e) {
            Log::error('Error generating blog post: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'topic' => $topic,
                'model' => $model->name ?? 'unknown',
                'options' => $options,
            ]);
            
            // تحسين رسالة الخطأ
            $errorMessage = $e->getMessage();
            if (strpos($errorMessage, 'timeout') !== false || strpos($errorMessage, 'Timeout') !== false) {
                throw new \Exception('انتهت مهلة الاتصال. يرجى المحاولة مرة أخرى أو تقليل طول المحتوى المطلوب.');
            }
            
            throw $e;
        }
    }

    /**
     * Append SEO/OG/Twitter/Schema/synonyms/canonical/reading-time to an already
     * assembled {title, slug, excerpt, content} post — shared tail for both the
     * one-shot path (generateBlogPost) and the staged outline+sections path
     * (BlogAiPipelineService), so both engines produce an identical final shape.
     *
     * @param  array{title: string, slug: string, excerpt: string, content: string}  $result
     */
    public function expandGeneratedPost(array $result, string $topic, AIModel $model, array $options = []): array
    {
        $language = $options['language'] ?? 'ar';
        $title = $result['title'] ?? '';
        $content = $result['content'] ?? '';
        $excerpt = $result['excerpt'] ?? '';

        // توليد حقول SEO إذا كانت مفعلة
        if ($options['generate_seo'] ?? true) {
            $seoData = $this->generateSEOFields($title, $content, $topic, $model, $language);
            $result = array_merge($result, $seoData);
        }

        // توليد Open Graph إذا كان مفعلاً
        if ($options['generate_og'] ?? true) {
            $ogData = $this->generateOpenGraph($title, $content, $excerpt, $model, $language);
            $result = array_merge($result, $ogData);
        }

        // توليد Twitter Card إذا كان مفعلاً
        if ($options['generate_twitter'] ?? true) {
            $twitterData = $this->generateTwitterCard($title, $content, $excerpt, $model, $language);
            $result = array_merge($result, $twitterData);
        }

        // توليد Schema.org إذا كان مفعلاً
        if ($options['generate_schema'] ?? true) {
            $schemaData = $this->generateSchema($title, $content, $excerpt, $model, $language);
            $result = array_merge($result, $schemaData);
        }

        // توليد Focus Keyword Synonyms إذا كان مفعلاً
        if ($options['generate_keyword_synonyms'] ?? true && isset($result['focus_keyword'])) {
            $synonyms = $this->generateKeywordSynonyms($result['focus_keyword'], $model, $language);
            $result['focus_keyword_synonyms'] = $synonyms;
        }

        // Canonical URL
        $result['canonical_url'] = url('/blog/'.$result['slug']);

        // Reading time
        $wordCount = str_word_count(strip_tags($content));
        $result['reading_time'] = max(1, ceil($wordCount / 200));

        return $result;
    }

    /**
     * Plan a blog article as an outline (for staged medium/long generation).
     *
     * @param  array{
     *   content_length?: string,
     *   tone?: string,
     *   language?: string,
     *   category?: BlogCategory|null
     * }  $options
     * @return array{title?: string, slug?: string, excerpt?: string, sections?: list<array{heading?: string, brief?: string}>}
     */
    public function generateBlogOutline(
        string $topic,
        AIModel $model,
        array $options,
        int $sectionTarget,
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

أعد النتيجة كـ JSON فقط بهذا الشكل:
{
  "title": "عنوان المقال",
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
            $model->temperature ?? 0.6,
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
     * Generate one blog article section as HTML (legacy staged path).
     *
     * @param  array<string, mixed>  $outline
     * @param  list<string>  $priorHeadings
     * @param  array{tone?: string, language?: string, content_length?: string}  $options
     * @param  list<string>  $laterHeadings
     */
    public function generateBlogSectionHtml(
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
        $articleTitle = trim((string) ($outline['title'] ?? '')) ?: $topic;
        $langLine = $language === 'en' ? 'Write this section in English.' : 'اكتب هذا القسم بالعربية.';
        $prior = $priorHeadings === [] ? '(لا يوجد بعد)' : implode(' | ', $priorHeadings);
        $styleGuide = BlogHtmlStyleGuide::block();
        $contentLength = (string) ($options['content_length'] ?? 'medium');
        $budgetLine = BlogHtmlStyleGuide::sectionBudget($contentLength, $compact);
        $laterLine = $laterHeadings === []
            ? ''
            : 'أقسام لاحقة (لا تتناولها هنا): '.implode(' | ', $laterHeadings);

        // Never a JSON envelope: asking a model to escape HTML inside a JSON
        // string is what made it write whole programs on a single line.
        $outputRule = "أعد HTML فقط. ابدأ مباشرة بـ <h2>{$heading}</h2>.\n"
            .'ممنوع JSON، ممنوع علامات markdown، ممنوع أي شرح قبل أو بعد HTML.';

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

استخدم HTML فقط وفق الدليل:
{$styleGuide}

ابدأ بـ <h2>{$heading}</h2> ولا تُرجع أقساماً أخرى.

{$outputRule}
PROMPT;

        $response = $this->callProvider(
            $model,
            $prompt,
            $maxTokens ?? $this->tokensForStage($model, 'section', $compact, $contentLength),
            $model->temperature ?? 0.7,
        );

        $rawHtml = $response;

        $html = $this->resultNormalizer()->extractSectionHtml($rawHtml);
        if ($html === '') {
            $html = $this->resultNormalizer()->extractSectionHtml(
                $this->resultNormalizer()->normalizeHtmlString($rawHtml)
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
     * Cap per-stage completion tokens — same shape as
     * AIDocumentationPageService::tokensForStage() but reads the ai.blog.*
     * config block and uses blog's shorter section budgets.
     */
    public function tokensForStage(
        AIModel $model,
        string $stage,
        bool $compact = false,
        string $contentLength = 'medium',
    ): int {
        $sectionCap = (int) config('ai.blog.section_max_tokens', 4096);
        $cap = match ($stage) {
            'outline' => (int) config('ai.blog.outline_max_tokens', 2048),
            default => match ($contentLength) {
                'short' => min($sectionCap, 2048),
                'long' => $sectionCap,
                default => min($sectionCap, 3072),
            },
        };
        if ($compact) {
            $cap = (int) max(512, floor($cap * 0.6));
        }

        $db = (int) ($model->max_tokens ?? 0);
        $effective = $db > 0 ? min($db, $cap) : $cap;

        return max(256, $effective);
    }

    /**
     * توليد المحتوى الرئيسي
     */
    private function generateContent(
        string $topic,
        AIModel $model,
        string $contentLength,
        string $tone,
        string $language,
        ?BlogCategory $category
    ): array {
        $lengthMap = [
            'short' => '500-800 كلمة',
            'medium' => '1000-1500 كلمة',
            'long' => '2000-3000 كلمة',
        ];

        $toneMap = [
            'professional' => 'احترافي ومهني',
            'friendly' => 'ودود وسهل',
            'technical' => 'تقني ومفصل',
            'casual' => 'عادي ومرن',
            'formal' => 'رسمي ومهذب',
        ];

        $categoryContext = $category ? "التصنيف: {$category->name}. " : '';

        $prompt = "أنت كاتب محترف للمدونات. اكتب مقالاً شاملاً ومتكاملاً باللغة العربية حول الموضوع التالي:

الموضوع: {$topic}
{$categoryContext}
الطول المطلوب: {$lengthMap[$contentLength]}
الأسلوب: {$toneMap[$tone]}

المتطلبات:
1. اكتب عنوان جذاب ومحسن لـ SEO (50-60 حرف)
2. اكتب محتوى شامل ومنظم مع:
   - مقدمة جذابة
   - فقرات منظمة مع عناوين فرعية
   - معلومات قيمة ومفيدة
   - خاتمة تلخص النقاط الرئيسية
3. استخدم HTML tags مناسبة (h2, h3, p, ul, ol, strong, em)
4. أضف أمثلة عملية عند الحاجة
5. استخدم لغة عربية فصيحة وسليمة

يرجى إرجاع النتيجة بصيغة JSON بالشكل التالي:
{
    \"title\": \"عنوان المقال\",
    \"content\": \"المحتوى الكامل مع HTML tags\",
    \"excerpt\": \"مقتطف قصير من المقال (100-150 كلمة)\"
}";

        try {
            $provider = AIProviderFactory::create($model);
            $response = $provider->generateText($prompt, [
                'max_tokens' => $model->max_tokens ?? 4000,
                'temperature' => $model->temperature ?? 0.7,
            ]);

            // التحقق من أن الاستجابة ليست فارغة
            if (empty($response)) {
                Log::warning('Empty response from AI provider', [
                    'topic' => $topic,
                    'model' => $model->name,
                ]);
                throw new \Exception('لم يتم استلام استجابة من موديل AI. يرجى المحاولة مرة أخرى.');
            }

            // Parse JSON response
            $data = $this->parseJSONResponse($response);
            
            if (!isset($data['title']) || !isset($data['content'])) {
                // Fallback: use topic as title and response as content
                Log::info('Using fallback for content generation', [
                    'topic' => $topic,
                    'response_length' => strlen($response),
                ]);
                $data = [
                    'title' => $topic,
                    'content' => $response,
                    'excerpt' => $this->generateExcerpt($response, $language),
                ];
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('Error in generateContent: ' . $e->getMessage(), [
                'topic' => $topic,
                'model' => $model->name ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \Exception('خطأ في توليد المحتوى: ' . $e->getMessage());
        }
    }

    /**
     * توليد جميع حقول SEO
     */
    private function generateSEOFields(
        string $title,
        string $content,
        string $topic,
        AIModel $model,
        string $language
    ): array {
        $prompt = "أنت خبير SEO محترف. قم بإنشاء حقول SEO محسنة بدقة للمقال التالي:

العنوان: {$title}
الموضوع: {$topic}

المتطلبات:
1. Meta Title: 50-60 حرف بالضبط، جذاب ويحتوي على الكلمة المفتاحية الرئيسية، بدون رموز غريبة
2. Meta Description: 150-160 حرف بالضبط، وصف جذاب ومشوق للمقال، بدون رموز غريبة
3. Meta Keywords: 8-12 كلمة مفتاحية ذات صلة قوية بالموضوع، مفصولة بفواصل فقط، بدون أرقام أو رموز، كل كلمة كاملة وصحيحة
4. Focus Keyword: كلمة مفتاحية رئيسية واحدة أو عبارة قصيرة (2-4 كلمات)، بدون رموز أو علامات

مهم جداً:
- استخدم فقط الكلمات العربية والإنجليزية الصحيحة والكاملة
- لا تستخدم أي رموز غريبة مثل ? أو * أو [ أو ]
- لا تستخدم كلمات مكسورة أو غير مكتملة
- تأكد من أن جميع الكلمات واضحة ومفهومة

يرجى إرجاع النتيجة بصيغة JSON فقط بدون أي نص إضافي:
{
    \"meta_title\": \"عنوان SEO\",
    \"meta_description\": \"وصف SEO\",
    \"meta_keywords\": \"كلمة1, كلمة2, كلمة3\",
    \"focus_keyword\": \"الكلمة المفتاحية الرئيسية\"
}";

        try {
            $provider = AIProviderFactory::create($model);
            $response = $provider->generateText($prompt, [
                'max_tokens' => 500,
                'temperature' => 0.5,
            ]);

            $data = $this->parseJSONResponse($response);

            // تنظيف البيانات قبل الإرجاع
            $metaKeywords = $data['meta_keywords'] ?? $this->extractKeywords($content);
            $focusKeyword = $data['focus_keyword'] ?? $this->extractMainKeyword($topic, $content);
            
            // تنظيف الكلمات المفتاحية
            $metaKeywords = $this->cleanKeywords($metaKeywords);
            $focusKeyword = trim(preg_replace('/[^\p{Arabic}\p{L}\p{N}\s-]/u', '', $focusKeyword));
            $focusKeyword = preg_replace('/\s+/u', ' ', $focusKeyword);
            
            // Fallbacks مع تحسين
            return [
                'meta_title' => $this->cleanText($data['meta_title'] ?? Str::limit($title, 60)),
                'meta_description' => $this->cleanText($data['meta_description'] ?? Str::limit(strip_tags($content), 160)),
                'meta_keywords' => $metaKeywords,
                'focus_keyword' => $focusKeyword,
            ];
        } catch (\Exception $e) {
            Log::warning('Error generating SEO fields, using fallbacks: ' . $e->getMessage());
            // استخدام fallbacks عند فشل توليد SEO مع تنظيف
            $metaKeywords = $this->cleanKeywords($this->extractKeywords($content));
            $focusKeyword = $this->extractMainKeyword($topic, $content);
            $focusKeyword = trim(preg_replace('/[^\p{Arabic}\p{L}\p{N}\s-]/u', '', $focusKeyword));
            $focusKeyword = preg_replace('/\s+/u', ' ', $focusKeyword);
            
            return [
                'meta_title' => $this->cleanText(Str::limit($title, 60)),
                'meta_description' => $this->cleanText(Str::limit(strip_tags($content), 160)),
                'meta_keywords' => $metaKeywords,
                'focus_keyword' => $focusKeyword,
            ];
        }
    }

    /**
     * توليد Open Graph tags
     */
    private function generateOpenGraph(
        string $title,
        string $content,
        string $excerpt,
        AIModel $model,
        string $language
    ): array {
        return [
            'og_title' => Str::limit($title, 60),
            'og_description' => Str::limit($excerpt ?: strip_tags($content), 200),
            'og_type' => 'article',
            'og_locale' => $language === 'ar' ? 'ar_SA' : 'en_US',
        ];
    }

    /**
     * توليد Twitter Card tags
     */
    private function generateTwitterCard(
        string $title,
        string $content,
        string $excerpt,
        AIModel $model,
        string $language
    ): array {
        return [
            'twitter_card' => 'summary_large_image',
            'twitter_title' => Str::limit($title, 70),
            'twitter_description' => Str::limit($excerpt ?: strip_tags($content), 200),
        ];
    }

    /**
     * توليد Schema.org markup
     */
    private function generateSchema(
        string $title,
        string $content,
        string $excerpt,
        AIModel $model,
        string $language
    ): array {
        return [
            'schema_type' => 'Article',
            'schema_headline' => $title,
            'schema_description' => $excerpt ?: Str::limit(strip_tags($content), 200),
        ];
    }

    /**
     * توليد مرادفات الكلمة المفتاحية
     */
    private function generateKeywordSynonyms(
        string $keyword,
        AIModel $model,
        string $language
    ): string {
        $prompt = "أنت خبير في اللغة العربية. أعطني 8-12 مرادفاً أو كلمة مشابهة للكلمة المفتاحية التالية باللغة العربية:

الكلمة المفتاحية: {$keyword}

المتطلبات:
- استخدم فقط كلمات عربية صحيحة وكاملة
- لا تستخدم أي رموز غريبة مثل ? أو * أو [ أو ]
- لا تستخدم كلمات مكسورة أو غير مكتملة
- كل كلمة يجب أن تكون واضحة ومفهومة
- الكلمات يجب أن تكون ذات صلة قوية بالكلمة المفتاحية

يرجى إرجاع النتيجة كقائمة مفصولة بفواصل فقط، بدون أرقام أو نقاط أو رموز.";

        try {
            $provider = AIProviderFactory::create($model);
            $response = $provider->generateText($prompt, [
                'max_tokens' => 200,
                'temperature' => 0.6,
            ]);

            // تنظيف الاستجابة باستخدام cleanKeywords
            $synonyms = $this->cleanKeywords($response);
            
            return $synonyms;
        } catch (\Exception $e) {
            Log::warning('Error generating keyword synonyms: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * توليد مقتطف من المحتوى
     */
    private function generateExcerpt(string $content, string $language): string
    {
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);
        return Str::limit($text, 150);
    }

    /**
     * توليد slug من العنوان
     */
    private function generateSlug(string $title): string
    {
        $slug = preg_replace('/\s+/', '-', trim($title));
        $slug = preg_replace('/[^\p{Arabic}a-zA-Z0-9-]/u', '', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    /**
     * تنظيف النص من الرموز الغريبة
     */
    private function cleanText(string $text): string
    {
        if (empty($text)) {
            return '';
        }

        // التحقق من ترميز UTF-8
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        }

        // إزالة BOM
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);
        
        // إزالة الأحرف غير الصالحة (الحفاظ على العربية والإنجليزية والأرقام والمسافات وعلامات الترقيم الأساسية)
        $text = preg_replace('/[^\p{Arabic}\p{L}\p{N}\s.,!?;:()\-\'"]/u', '', $text);
        
        // تنظيف المسافات المتعددة
        $text = preg_replace('/\s+/u', ' ', $text);
        
        return trim($text);
    }

    /**
     * استخراج كلمات مفتاحية من المحتوى
     */
    private function extractKeywords(string $content, int $count = 10): string
    {
        $text = strip_tags($content);
        
        // تنظيف النص أولاً
        $text = $this->cleanText($text);
        
        // استخراج الكلمات (العربية والإنجليزية)
        $words = preg_split('/[\s\p{P}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        // Remove common Arabic stop words
        $stopWords = [
            'في', 'من', 'إلى', 'على', 'هذا', 'هذه', 'التي', 'الذي', 'كان', 'كانت', 
            'يكون', 'تكون', 'أن', 'إن', 'ما', 'لا', 'لم', 'لن', 'لكن', 'أو', 'و', 
            'مع', 'عن', 'عند', 'بين', 'خلال', 'حول', 'بعد', 'قبل', 'أثناء', 
            'لأن', 'لكي', 'حتى', 'إذا', 'إذ', 'إذن', 'إلا', 'إما', 'هو', 'هي', 
            'هم', 'هن', 'أنت', 'أنتم', 'أنتن', 'أنا', 'نحن', 'ذلك', 'تلك', 
            'هؤلاء', 'هناك', 'هنا', 'الآن', 'قد', 'قد', 'كل', 'بعض', 'أكثر', 'أقل'
        ];
        
        // تصفية الكلمات
        $filteredWords = [];
        foreach ($words as $word) {
            $word = trim($word);
            
            // تجاهل الكلمات القصيرة جداً (أقل من 3 أحرف)
            if (mb_strlen($word) < 3) {
                continue;
            }
            
            // تجاهل كلمات التوقف
            if (in_array($word, $stopWords, true)) {
                continue;
            }
            
            // تجاهل الكلمات التي تحتوي على أرقام فقط
            if (preg_match('/^\d+$/', $word)) {
                continue;
            }
            
            // تجاهل الكلمات التي تحتوي على رموز غريبة
            if (preg_match('/[?؟*+^$<>{}[\]()\\\]/u', $word)) {
                continue;
            }
            
            $filteredWords[] = $word;
        }
        
        // حساب تكرار الكلمات
        $wordFreq = array_count_values($filteredWords);
        arsort($wordFreq);
        
        // أخذ أكثر الكلمات تكراراً
        $keywords = array_slice(array_keys($wordFreq), 0, $count);
        
        // تنظيف الكلمات المفتاحية
        $cleanedKeywords = [];
        foreach ($keywords as $keyword) {
            $keyword = trim($keyword);
            if (mb_strlen($keyword) >= 2) {
                $cleanedKeywords[] = $keyword;
            }
        }
        
        return implode(', ', $cleanedKeywords);
    }

    /**
     * استخراج الكلمة المفتاحية الرئيسية
     */
    private function extractMainKeyword(string $topic, string $content): string
    {
        // Use topic as main keyword, or extract from content
        $topicWords = explode(' ', trim($topic));
        if (count($topicWords) <= 3) {
            return $topic;
        }
        
        // Extract first meaningful words from topic
        return implode(' ', array_slice($topicWords, 0, 3));
    }

    /**
     * تنظيف الكلمات المفتاحية من الرموز الغريبة والكلمات المكسورة
     */
    private function cleanKeywords(string $keywords): string
    {
        if (empty($keywords)) {
            return '';
        }

        // التحقق من ترميز UTF-8
        if (!mb_check_encoding($keywords, 'UTF-8')) {
            $keywords = mb_convert_encoding($keywords, 'UTF-8', 'auto');
        }

        // إزالة BOM إذا كان موجوداً
        $keywords = preg_replace('/^\xEF\xBB\xBF/', '', $keywords);

        // تقسيم الكلمات المفتاحية
        $keywordArray = preg_split('/[,،\n\r\t|]/u', $keywords);

        $cleanedKeywords = [];
        foreach ($keywordArray as $keyword) {
            // تنظيف كل كلمة
            $keyword = trim($keyword);
            
            // إزالة الأحرف غير الصالحة (الحفاظ على العربية والإنجليزية والأرقام والمسافات)
            $keyword = preg_replace('/[^\p{Arabic}\p{L}\p{N}\s-]/u', '', $keyword);
            
            // إزالة المسافات المتعددة
            $keyword = preg_replace('/\s+/u', ' ', $keyword);
            $keyword = trim($keyword);

            // تجاهل الكلمات القصيرة جداً (أقل من 2 حرف) والكلمات الفارغة
            if (mb_strlen($keyword) < 2 || empty($keyword)) {
                continue;
            }

            // تجاهل الكلمات التي تحتوي على رموز مشبوهة
            if (preg_match('/[?؟*+^$<>{}[\]()\\\]/u', $keyword)) {
                continue;
            }

            // تجاهل الكلمات التي تبدأ أو تنتهي برموز غريبة
            if (preg_match('/^[^\p{Arabic}\p{L}\p{N}]|[^\p{Arabic}\p{L}\p{N}]$/u', $keyword)) {
                $keyword = preg_replace('/^[^\p{Arabic}\p{L}\p{N}]+|[^\p{Arabic}\p{L}\p{N}]+$/u', '', $keyword);
                $keyword = trim($keyword);
                if (mb_strlen($keyword) < 2) {
                    continue;
                }
            }

            // إضافة الكلمة المطهرة فقط إذا لم تكن موجودة بالفعل
            if (!empty($keyword) && !in_array($keyword, $cleanedKeywords)) {
                $cleanedKeywords[] = $keyword;
            }
        }

        // إرجاع الكلمات المفتاحية مفصولة بفواصل
        return implode(', ', $cleanedKeywords);
    }
}

