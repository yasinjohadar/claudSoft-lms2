<?php

namespace App\Services\Ai;

use App\Models\AIModel;
use App\Services\Ai\Concerns\ParsesAiJsonResponse;
use Illuminate\Support\Facades\Log;

class AIFrontendCourseService
{
    use ParsesAiJsonResponse;

    public const MAX_TOPIC_CHARS = 500;

    /**
     * @param  array{
     *   tone?: string,
     *   language?: string,
     *   level?: string,
     *   category_name?: string|null,
     *   target_sections?: int,
     *   lessons_per_section_hint?: int,
     *   generate_advanced_seo?: bool
     * }  $options
     * @return array<string, mixed>
     */
    public function generateCourseOutline(string $topic, AIModel $model, array $options = []): array
    {
        $topic = trim($topic);
        if ($topic === '') {
            throw new \InvalidArgumentException('الموضوع مطلوب.');
        }
        if (mb_strlen($topic) > self::MAX_TOPIC_CHARS) {
            throw new \InvalidArgumentException('الموضوع أطول من المسموح.');
        }

        $tone = $options['tone'] ?? 'professional';
        $language = $options['language'] ?? 'ar';
        $level = $options['level'] ?? 'beginner';
        $categoryName = trim((string) ($options['category_name'] ?? ''));
        $targetSections = max(2, min(12, (int) ($options['target_sections'] ?? 4)));
        $lessonsHint = max(1, min(8, (int) ($options['lessons_per_section_hint'] ?? 3)));
        $advancedSeo = (bool) ($options['generate_advanced_seo'] ?? true);

        $toneMap = [
            'professional' => 'احترافي',
            'friendly' => 'ودود',
            'technical' => 'تقني',
            'casual' => 'عادي',
            'formal' => 'رسمي',
        ];
        $toneLabel = $toneMap[$tone] ?? $toneMap['professional'];

        $levelMap = [
            'beginner' => 'مبتدئ',
            'intermediate' => 'متوسط',
            'advanced' => 'متقدم',
        ];
        $levelLabel = $levelMap[$level] ?? $levelMap['beginner'];

        $langLine = $language === 'en'
            ? 'Write course text primarily in clear English unless the topic requires Arabic.'
            : 'اكتب النصوص بالعربية الفصحى الواضحة.';

        $catLine = $categoryName !== '' ? "التصنيف المختار: {$categoryName}. " : '';

        $seoBlock = $advancedSeo
            ? 'أدرج أيضاً: og_title, og_description, og_type (مثل website أو article), twitter_title, twitter_description, focus_keyword (كلمة رئيسية واحدة أو عبارة قصيرة), reading_time (عدد صحيح بالدقائق تقديراً لقراءة الوصف), robots (مثل index, follow), author (اسم مختصر). اترك og_image و twitter_image كسلسلة فارغة "" إن لم تكن هناك روابط.'
            : 'لا تُدرج حقول og_* أو twitter_* إضافية؛ اكتفِ بـ meta_title و meta_description و meta_keywords.';

        $jsonExample = <<<'JSON'
{
  "title": "عنوان الكورس",
  "subtitle": "سطر فرعي قصير",
  "description": "وصف تفصيلي للكورس بفقرات يمكن أن تحتوي على HTML بسيط مثل <p> و <strong> و <ul><li>",
  "requirements": "متطلبات سابقة كنص",
  "what_you_learn": ["نقطة 1", "نقطة 2", "نقطة 3"],
  "meta_title": "",
  "meta_description": "",
  "meta_keywords": "كلمة1, كلمة2",
  "duration": 10.5,
  "sections": [
    {
      "title": "عنوان المحور",
      "description": "وصف المحور",
      "lessons": [
        {
          "title": "عنوان الدرس",
          "description": "ملخص قصير للدرس",
          "type": "video",
          "duration": 15
        }
      ]
    }
  ]
}
JSON;

        $prompt = <<<PROMPT
أنت خبير تعليم إلكتروني وكتابة محتوى كورسات للواجهة الأمامية لمنصة تعليمية.

{$catLine}موضوع/فكرة الكورس: {$topic}
المستوى المستهدف: {$levelLabel}
الأسلوب: {$toneLabel}
{$langLine}

المطلوب: هيكل كورس كامل جاهز للنشر (بدون روابط فيديو حقيقية؛ يمكن وصف ما سيُغطى في الدرس).
- عدد المحاور (sections) تقريباً: {$targetSections} (يمكن ±1).
- لكل محور حوالي {$lessonsHint} دروس (يمكن ±1 حسب المنطق التعليمي).
- أنواع الدروس type واحدة من: video, text, file, quiz, live — غالباً video أو text للدروس النظرية.
- duration لكل درس: عدد صحيح بالدقائق (معقول بين 5 و 45).
- what_you_learn: مصفوفة من 4 إلى 10 نقاط واضحة (ما سيتعلمه الطالب).
- requirements: نص متطلبات مسبقة أو "لا يوجد" إن كان للمبتدئين.
- duration في جذر JSON: تقدير إجمالي ساعات الكورس (رقم عشري مثل 8.5).

{$seoBlock}

أعد JSON فقط بدون markdown أو شرح خارج JSON. الشكل يشبه (مع تعبئة القيم الحقيقية):
{$jsonExample}

مهم: keys بالإنجليزية كما في المثال. المحتوى النصي بالعربية (أو الإنجليزية إن طُلبت اللغة en).
PROMPT;

        set_time_limit(500);

        $provider = AIProviderFactory::create($model);
        $response = $provider->generateText($prompt, [
            'max_tokens' => min(16000, (int) ($model->max_tokens ?? 12000)),
            'temperature' => (float) ($model->temperature ?? 0.55),
        ]);

        if (empty($response)) {
            throw new \Exception('لم يتم استلام استجابة من موديل AI.');
        }

        $data = $this->parseJSONResponse($response);

        if (! is_array($data) || empty($data['title']) || empty($data['description'])) {
            Log::warning('AIFrontendCourseService: weak JSON', ['preview' => mb_substr($response, 0, 400)]);

            throw new \Exception('لم يُستخرج هيكل كورس صالح من الاستجابة. جرّب مرة أخرى أو قلّل التعقيد.');
        }

        return $this->normalizeOutline($data, $advancedSeo);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeOutline(array $data, bool $advancedSeo): array
    {
        $whatYouLearn = $data['what_you_learn'] ?? [];
        if (! is_array($whatYouLearn)) {
            $whatYouLearn = [];
        }
        $whatYouLearn = array_values(array_filter(array_map(function ($item) {
            return is_string($item) ? trim($item) : '';
        }, $whatYouLearn)));

        $sections = $data['sections'] ?? [];
        if (! is_array($sections)) {
            $sections = [];
        }

        $normalizedSections = [];
        foreach ($sections as $section) {
            if (! is_array($section)) {
                continue;
            }
            $st = trim((string) ($section['title'] ?? ''));
            if ($st === '') {
                continue;
            }
            $lessonsIn = $section['lessons'] ?? [];
            if (! is_array($lessonsIn)) {
                $lessonsIn = [];
            }
            $lessonsOut = [];
            foreach ($lessonsIn as $lesson) {
                if (! is_array($lesson)) {
                    continue;
                }
                $lt = trim((string) ($lesson['title'] ?? ''));
                if ($lt === '') {
                    continue;
                }
                $type = strtolower((string) ($lesson['type'] ?? 'video'));
                if (! in_array($type, ['video', 'text', 'file', 'quiz', 'live'], true)) {
                    $type = 'video';
                }
                $dur = (int) ($lesson['duration'] ?? 15);
                $dur = max(1, min(180, $dur));
                $lessonsOut[] = [
                    'title' => $lt,
                    'description' => trim((string) ($lesson['description'] ?? '')),
                    'type' => $type,
                    'duration' => $dur,
                ];
            }
            if ($lessonsOut === []) {
                $lessonsOut[] = [
                    'title' => 'مقدمة',
                    'description' => '',
                    'type' => 'video',
                    'duration' => 10,
                ];
            }
            $normalizedSections[] = [
                'title' => $st,
                'description' => trim((string) ($section['description'] ?? '')),
                'lessons' => $lessonsOut,
            ];
        }

        if ($normalizedSections === []) {
            $normalizedSections[] = [
                'title' => 'الوحدة الأولى',
                'description' => '',
                'lessons' => [
                    ['title' => 'درس تمهيدي', 'description' => '', 'type' => 'video', 'duration' => 15],
                ],
            ];
        }

        $out = [
            'title' => trim((string) $data['title']),
            'subtitle' => trim((string) ($data['subtitle'] ?? '')),
            'description' => trim((string) $data['description']),
            'requirements' => trim((string) ($data['requirements'] ?? '')),
            'what_you_learn' => $whatYouLearn,
            'meta_title' => trim((string) ($data['meta_title'] ?? '')),
            'meta_description' => trim((string) ($data['meta_description'] ?? '')),
            'meta_keywords' => trim((string) ($data['meta_keywords'] ?? '')),
            'duration' => isset($data['duration']) ? (float) $data['duration'] : null,
            'sections' => $normalizedSections,
        ];

        if ($advancedSeo) {
            $out['og_title'] = trim((string) ($data['og_title'] ?? ''));
            $out['og_description'] = trim((string) ($data['og_description'] ?? ''));
            $out['og_type'] = trim((string) ($data['og_type'] ?? 'website')) ?: 'website';
            $out['twitter_title'] = trim((string) ($data['twitter_title'] ?? ''));
            $out['twitter_description'] = trim((string) ($data['twitter_description'] ?? ''));
            $out['focus_keyword'] = trim((string) ($data['focus_keyword'] ?? ''));
            $out['reading_time'] = isset($data['reading_time']) ? (int) $data['reading_time'] : null;
            $out['robots'] = trim((string) ($data['robots'] ?? ''));
            $out['author'] = trim((string) ($data['author'] ?? ''));
        }

        return $out;
    }
}
