<?php

namespace App\Services\Ai;

use App\Models\AIModel;
use App\Models\BlogCategory;
use Illuminate\Support\Facades\Log;
use App\Services\Ai\AIProviderFactory;
use Illuminate\Support\Str;

class AIBlogPostService
{
    public function __construct(
        private AIModelService $modelService
    ) {}

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
            $result['canonical_url'] = url('/blog/' . $slug);

            // Reading time
            $wordCount = str_word_count(strip_tags($content));
            $result['reading_time'] = max(1, ceil($wordCount / 200));

            return $result;

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
        $prompt = "أنت خبير SEO. قم بإنشاء حقول SEO محسنة للمقال التالي:

العنوان: {$title}
الموضوع: {$topic}

يرجى إنشاء:
1. Meta Title (50-60 حرف، جذاب ويحتوي على الكلمة المفتاحية)
2. Meta Description (150-160 حرف، وصف جذاب للمقال)
3. Meta Keywords (5-10 كلمات مفتاحية مفصولة بفواصل)
4. Focus Keyword (الكلمة المفتاحية الرئيسية)

يرجى إرجاع النتيجة بصيغة JSON:
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

            // Fallbacks مع تحسين
            return [
                'meta_title' => $data['meta_title'] ?? Str::limit($title, 60),
                'meta_description' => $data['meta_description'] ?? Str::limit(strip_tags($content), 160),
                'meta_keywords' => $data['meta_keywords'] ?? $this->extractKeywords($content),
                'focus_keyword' => $data['focus_keyword'] ?? $this->extractMainKeyword($topic, $content),
            ];
        } catch (\Exception $e) {
            Log::warning('Error generating SEO fields, using fallbacks: ' . $e->getMessage());
            // استخدام fallbacks عند فشل توليد SEO
            return [
                'meta_title' => Str::limit($title, 60),
                'meta_description' => Str::limit(strip_tags($content), 160),
                'meta_keywords' => $this->extractKeywords($content),
                'focus_keyword' => $this->extractMainKeyword($topic, $content),
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
        $prompt = "أعطني 5-10 مرادفات أو كلمات مشابهة للكلمة المفتاحية التالية باللغة العربية:

الكلمة المفتاحية: {$keyword}

يرجى إرجاع النتيجة كقائمة مفصولة بفواصل فقط، بدون أرقام أو نقاط.";

        try {
            $provider = AIProviderFactory::create($model);
            $response = $provider->generateText($prompt, [
                'max_tokens' => 200,
                'temperature' => 0.6,
            ]);

            // Clean response
            $synonyms = trim($response);
            $synonyms = preg_replace('/^[0-9.\-]+/', '', $synonyms); // Remove numbers
            $synonyms = preg_replace('/\n+/', ', ', $synonyms); // Replace newlines with commas
            
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
     * استخراج كلمات مفتاحية من المحتوى
     */
    private function extractKeywords(string $content, int $count = 10): string
    {
        $text = strip_tags($content);
        $words = str_word_count($text, 1, 'أبتثجحخدذرزسشصضطظعغفقكلمنهوي');
        
        // Remove common Arabic stop words
        $stopWords = ['في', 'من', 'إلى', 'على', 'هذا', 'هذه', 'التي', 'الذي', 'كان', 'كانت', 'يكون', 'يكون', 'أن', 'إن', 'ما', 'لا', 'لم', 'لن', 'لكن', 'أو', 'و', 'مع', 'عن', 'عند', 'بين', 'خلال', 'حول', 'بعد', 'قبل', 'أثناء', 'لأن', 'لكي', 'حتى', 'إذا', 'إذ', 'إذن', 'إلا', 'إما', 'إما', 'إلى', 'على', 'في', 'من', 'عن', 'مع', 'بين', 'خلال', 'حول', 'بعد', 'قبل', 'أثناء', 'لأن', 'لكي', 'حتى', 'إذا', 'إذ', 'إذن', 'إلا', 'إما', 'إما'];
        
        $words = array_filter($words, function($word) use ($stopWords) {
            return mb_strlen($word) > 3 && !in_array($word, $stopWords);
        });
        
        $wordFreq = array_count_values($words);
        arsort($wordFreq);
        
        $keywords = array_slice(array_keys($wordFreq), 0, $count);
        return implode(', ', $keywords);
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
     * Parse JSON response from AI
     */
    private function parseJSONResponse(string $response): array
    {
        // Try to extract JSON from response
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');
        
        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
            $decoded = json_decode($jsonString, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }
        
        // If JSON parsing fails, try to extract data from text
        return [];
    }
}

