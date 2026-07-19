<?php

namespace App\Services\Ai;

use App\Ai\Agents\QuestionGenerationPlainAgent;
use App\Models\AIModel;
use App\Models\LaravelAiModel;
use App\Models\Lesson;
use App\Models\ProgrammingLanguage;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\QuestionType;
use App\Services\AiNew\LaravelAiPromptRunner;
use App\Services\AiNew\LaravelAiProviderManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AIQuestionCreationService
{
    public function __construct(
        private AIModelService $modelService,
        private LaravelAiProviderManager $providerManager,
        private LaravelAiPromptRunner $promptRunner,
    ) {}

    /**
     * بناء prompt لتوليد الأسئلة (للاستخدام من مسار المعاينة في بنك الأسئلة).
     */
    public function buildQuestionGenerationPrompt(
        string $text,
        ProgrammingLanguage $programmingLanguage,
        Collection $questionTypes,
        int $numberOfQuestions,
        string $difficultyLevel
    ): string {
        $questionTypeNamesStr = $questionTypes->pluck('display_name')->implode('، ');

        return $this->buildPrompt(
            $text,
            $programmingLanguage->display_name ?? $programmingLanguage->name,
            $questionTypeNamesStr,
            $numberOfQuestions,
            $difficultyLevel
        );
    }

    /**
     * حفظ أسئلة مُحلَّلة في بنك الأسئلة مع دعم كل الأنواع.
     */
    public function saveParsedQuestionsToBank(
        array $generatedQuestions,
        ProgrammingLanguage $programmingLanguage,
        Collection $questionTypes,
        array $options = []
    ): Collection {
        return $this->saveQuestionsToBank(
            $generatedQuestions,
            $programmingLanguage,
            $questionTypes,
            $options
        );
    }

    /**
     * إنشاء أسئلة من درس
     */
    public function createQuestionsFromLesson(
        Lesson $lesson,
        ProgrammingLanguage $programmingLanguage,
        Collection $questionTypes,
        array $options = []
    ): Collection {
        $content = $lesson->description ?? $lesson->title;

        // Get course from lesson through module and section
        $courseId = $options['course_id'] ?? null;
        if (! $courseId && $lesson->module && $lesson->module->section) {
            $courseId = $lesson->module->section->course_id;
        }

        $lessonName = $options['lesson_name'] ?? $lesson->title;

        return $this->createQuestionsFromText(
            $content,
            $programmingLanguage,
            $questionTypes,
            array_merge($options, [
                'lesson_id' => $lesson->id,
                'course_id' => $courseId,
                'lesson_name' => $lessonName,
                'source_type' => 'lesson_content',
            ])
        );
    }

    /**
     * إنشاء أسئلة من موضوع
     */
    public function createQuestionsFromTopic(
        string $topic,
        ProgrammingLanguage $programmingLanguage,
        Collection $questionTypes,
        array $options = []
    ): Collection {
        return $this->createQuestionsFromText(
            $topic,
            $programmingLanguage,
            $questionTypes,
            array_merge($options, [
                'source_type' => 'topic',
            ])
        );
    }

    /**
     * إنشاء أسئلة من نص
     */
    public function createQuestionsFromText(
        string $text,
        ProgrammingLanguage $programmingLanguage,
        Collection $questionTypes,
        array $options = []
    ): Collection {
        $user = $options['user'] ?? auth()->user();
        $laraModel = $options['laravel_model'] ?? null;
        $numberOfQuestions = $options['number_of_questions'] ?? 5;
        $difficultyLevel = $options['difficulty_level'] ?? 'mixed';

        if (! ($laraModel instanceof LaravelAiModel)) {
            $model = $options['model'] ?? $this->modelService->getBestModelFor('question_generation');
            if (! $model) {
                throw new \Exception('لا يوجد موديل AI متاح لتوليد الأسئلة');
            }
            $options['model'] = $model;
        }

        // زيادة وقت التنفيذ
        set_time_limit(180);

        try {
            // توليد الأسئلة باستخدام AI
            $generatedQuestions = $this->generateQuestionsWithAI(
                $text,
                $programmingLanguage,
                $questionTypes,
                $numberOfQuestions,
                $difficultyLevel,
                $options
            );

            // حفظ الأسئلة في بنك الأسئلة
            return $this->saveQuestionsToBank(
                $generatedQuestions,
                $programmingLanguage,
                $questionTypes,
                $options
            );
        } catch (\Exception $e) {
            Log::error('Error creating questions: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * توليد الأسئلة باستخدام AI (Laravel AI SDK أو المزود التقليدي)
     */
    private function generateQuestionsWithAI(
        string $text,
        ProgrammingLanguage $programmingLanguage,
        Collection $questionTypes,
        int $numberOfQuestions,
        string $difficultyLevel,
        array $options
    ): array {
        $questionTypeNames = $questionTypes->pluck('display_name')->toArray();
        $questionTypeNamesStr = implode('، ', $questionTypeNames);

        $prompt = $this->buildPrompt(
            $text,
            $programmingLanguage->display_name,
            $questionTypeNamesStr,
            $numberOfQuestions,
            $difficultyLevel
        );

        $laraModel = $options['laravel_model'] ?? null;
        if ($laraModel instanceof LaravelAiModel) {
            return $this->generateQuestionsWithLaravelSdk($laraModel, $prompt, $numberOfQuestions);
        }

        /** @var AIModel $model */
        $model = $options['model'];

        Log::info('Starting question creation API call (legacy)', [
            'model_id' => $model->id,
            'model_name' => $model->name,
            'provider' => $model->provider,
            'prompt_length' => strlen($prompt),
            'number_of_questions' => $numberOfQuestions,
        ]);

        $provider = AIProviderFactory::create($model);
        $response = $provider->generateText($prompt, [
            'max_tokens' => $model->max_tokens ?? 4000,
            'temperature' => $model->temperature ?? 0.7,
        ]);

        Log::info('Question creation API response received', [
            'response_length' => strlen($response ?? ''),
            'response_empty' => empty($response),
            'last_error' => $provider->getLastError(),
        ]);

        if (! $response || $response === '') {
            $lastError = $provider->getLastError() ?? 'فشل في توليد الأسئلة - لم يتم الحصول على رد من API';
            Log::error('Question creation failed - empty response', [
                'last_error' => $lastError,
            ]);
            throw new \Exception($lastError);
        }

        Log::info('Full AI response received', [
            'response_length' => strlen($response),
            'response_preview' => substr($response, 0, 1000),
            'response_full' => $response,
        ]);

        $questions = $this->parseGeneratedQuestions($response);

        Log::info('Questions parsed successfully', [
            'questions_count' => count($questions),
        ]);

        return $questions;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function generateQuestionsWithLaravelSdk(LaravelAiModel $laraModel, string $prompt, int $numberOfQuestions): array
    {
        Log::info('Question creation (Laravel AI SDK) starting', [
            'laravel_ai_model_id' => $laraModel->id,
            'prompt_length' => strlen($prompt),
            'number_of_questions' => $numberOfQuestions,
        ]);

        $response = $this->providerManager->runWithModel($laraModel, function () use ($laraModel, $prompt) {
            return $this->promptRunner->runPlain($laraModel, new QuestionGenerationPlainAgent, $prompt, 180);
        });

        $responseText = trim((string) $response->text);
        if ($responseText === '') {
            throw new \Exception('لم يُرجع الموديل أي نص. جرّب موديلاً آخر أو زد max_tokens في إعدادات موديل Laravel AI.');
        }

        Log::info('Question creation (Laravel AI SDK) response', [
            'response_length' => strlen($responseText),
            'preview' => mb_substr($responseText, 0, 500),
        ]);

        $questions = $this->parseGeneratedQuestions($responseText);

        Log::info('Questions parsed successfully (Laravel AI SDK)', [
            'questions_count' => count($questions),
        ]);

        return $questions;
    }

    /**
     * بناء prompt للتوليد
     */
    private function buildPrompt(
        string $text,
        string $languageName,
        string $questionTypes,
        int $numberOfQuestions,
        string $difficultyLevel
    ): string {
        $difficultyMap = [
            'easy' => 'سهل',
            'medium' => 'متوسط',
            'hard' => 'صعب',
            'mixed' => 'مختلط',
        ];
        $difficultyText = $difficultyMap[$difficultyLevel] ?? 'متوسط';

        return "أنت مساعد متخصص في إنشاء أسئلة تعليمية عالية الجودة.

المحتوى المصدر:
{$text}

المتطلبات:
- اللغة: {$languageName}
- أنواع الأسئلة المطلوبة: {$questionTypes}
- عدد الأسئلة: {$numberOfQuestions}
- مستوى الصعوبة: {$difficultyText}

يرجى إنشاء الأسئلة بالصيغة JSON التالية. يجب أن تكون الأسئلة متنوعة وتغطي الأنواع المطلوبة:

أنواع الأسئلة المتاحة:

1. **single_choice** (اختيار من متعدد - إجابة واحدة):
{
  \"type\": \"single_choice\",
  \"question\": \"نص السؤال\",
  \"options\": [\"الخيار 1\", \"الخيار 2\", \"الخيار 3\", \"الخيار 4\"],
  \"correct_answer\": \"الخيار الصحيح (نص الخيار)\",
  \"explanation\": \"شرح الإجابة\",
  \"difficulty\": \"easy|medium|hard\",
  \"points\": 10
}

2. **multiple_choice** (اختيار من متعدد - إجابات متعددة):
{
  \"type\": \"multiple_choice\",
  \"question\": \"نص السؤال\",
  \"options\": [\"الخيار 1\", \"الخيار 2\", \"الخيار 3\", \"الخيار 4\"],
  \"correct_answer\": [\"الخيار الصحيح 1\", \"الخيار الصحيح 2\"],
  \"explanation\": \"شرح الإجابة\",
  \"difficulty\": \"easy|medium|hard\",
  \"points\": 10
}

3. **true_false** (صح / خطأ):
{
  \"type\": \"true_false\",
  \"question\": \"نص السؤال\",
  \"options\": [\"صح\", \"خطأ\"],
  \"correct_answer\": \"صح\" أو \"خطأ\" (يجب أن يكون بالعربية فقط، وليس true/false),
  \"explanation\": \"شرح واضح يوضح لماذا الإجابة صحيحة (جملة أو أكثر — ممنوع كتابة «صح وخطأ» أو اسم نوع السؤال فقط)\",
  \"difficulty\": \"easy|medium|hard\",
  \"points\": 10
}

4. **short_answer** (إجابة قصيرة):
{
  \"type\": \"short_answer\",
  \"question\": \"نص السؤال\",
  \"correct_answer\": \"الإجابة الصحيحة\",
  \"explanation\": \"شرح الإجابة\",
  \"difficulty\": \"easy|medium|hard\",
  \"points\": 10
}

5. **essay** (مقالي - إجابة طويلة):
{
  \"type\": \"essay\",
  \"question\": \"نص السؤال\",
  \"explanation\": \"نموذج إجابة أو نقاط مهمة\",
  \"difficulty\": \"easy|medium|hard\",
  \"points\": 10
}

6. **matching** (مطابقة):
{
  \"type\": \"matching\",
  \"question\": \"وصف المهمة (مثل: قم بمطابقة العناصر)\",
  \"pairs\": [
    {\"question\": \"العنصر 1\", \"answer\": \"المطابق 1\"},
    {\"question\": \"العنصر 2\", \"answer\": \"المطابق 2\"},
    {\"question\": \"العنصر 3\", \"answer\": \"المطابق 3\"}
  ],
  \"explanation\": \"شرح الإجابة\",
  \"difficulty\": \"easy|medium|hard\",
  \"points\": 10
}

7. **ordering** (ترتيب):
{
  \"type\": \"ordering\",
  \"question\": \"نص السؤال\",
  \"items\": [\"العنصر الأول\", \"العنصر الثاني\", \"العنصر الثالث\", \"العنصر الرابع\"],
  \"correct_order\": [\"العنصر الأول\", \"العنصر الثاني\", \"العنصر الثالث\", \"العنصر الرابع\"],
  \"explanation\": \"شرح الترتيب الصحيح\",
  \"difficulty\": \"easy|medium|hard\",
  \"points\": 10
}

8. **fill_blanks** (ملء الفراغات — قوائم منسدلة مشتركة، بدون كتابة حرة):
{
  \"type\": \"fill_blanks\",
  \"question\": \"نص السؤال مع [[blank]] لكل فراغ (استخدم [[blank]] وليس [___])\",
  \"dropdown_options\": [\"خيار1\", \"خيار2\", \"خيار3\", \"خيار4\"],
  \"blank_answers\": [\"خيار1\", \"خيار3\"],
  \"explanation\": \"شرح الإجابات بجمل واضحة\",
  \"difficulty\": \"easy|medium|hard\",
  \"points\": 10
}
ملاحظة fill_blanks:
- dropdown_options: قائمة خيارات مشتركة تظهر في كل فراغ للطالب (يجب أن تتضمن الإجابات الصحيحة + مشتتات).
- blank_answers: الإجابة الصحيحة لكل فراغ بالترتيب (طول المصفوفة = عدد [[blank]] في السؤال).
- كل عنصر في blank_answers يجب أن يكون موجوداً داخل dropdown_options.
- لا تستخدم كتابة حرة؛ الطالب يختار من القائمة فقط.

9. **numerical** (إجابة رقمية):
{
  \"type\": \"numerical\",
  \"question\": \"نص السؤال\",
  \"expected_value\": 42.5,
  \"tolerance\": 0.1,
  \"explanation\": \"شرح الإجابة\",
  \"difficulty\": \"easy|medium|hard\",
  \"points\": 10
}

10. **calculated** (محسوب - معادلات):
{
  \"type\": \"calculated\",
  \"question\": \"نص السؤال مع متغيرات\",
  \"formula\": \"{a} * {b} + {c}\",
  \"variables\": [
    {\"name\": \"a\", \"min\": 1, \"max\": 10},
    {\"name\": \"b\", \"min\": 1, \"max\": 10},
    {\"name\": \"c\", \"min\": 1, \"max\": 10}
  ],
  \"explanation\": \"شرح الحل\",
  \"difficulty\": \"easy|medium|hard\",
  \"points\": 10
}

ملاحظات مهمة:
- تأكد من إنشاء الأسئلة بالأنواع المطلوبة فقط: {$questionTypes}
- إذا كان النوع المطلوب \"مطابقة\"، استخدم type: \"matching\" مع pairs
- إذا كان النوع المطلوب \"ترتيب\"، استخدم type: \"ordering\" مع items و correct_order
- إذا كان النوع المطلوب \"ملء الفراغات\"، استخدم type: \"fill_blanks\" مع dropdown_options و blank_answers وضع [[blank]] في نص السؤال لكل فراغ (قائمة منسدلة مشتركة بدون كتابة حرة)
- إذا كان النوع المطلوب \"إجابة رقمية\"، استخدم type: \"numerical\" مع expected_value
- إذا كان النوع المطلوب \"محسوب\"، استخدم type: \"calculated\" مع formula و variables
- الإجابات الصحيحة يجب أن تكون دقيقة ومتسقة مع المحتوى المصدر
- الشرح يجب أن يكون واضحاً ومفيداً ويفسر **لماذا** الإجابة صحيحة (ممنوع كتابة «صح وخطأ» أو اسم نوع السؤال فقط)
- استخدم مصطلحات متعلقة بـ {$languageName}
- أنشئ عدد الأسئلة المطلوب ({$numberOfQuestions}) مع التنويع في الأنواع المحددة";
    }

    /**
     * حفظ الأسئلة في بنك الأسئلة
     */
    private function saveQuestionsToBank(
        array $generatedQuestions,
        ProgrammingLanguage $programmingLanguage,
        Collection $questionTypes,
        array $options
    ): Collection {
        $user = $options['user'] ?? auth()->user();
        $courseId = $options['course_id'] ?? null;
        $lessonNameRaw = isset($options['lesson_name']) ? trim((string) $options['lesson_name']) : '';
        $lessonName = $lessonNameRaw !== '' ? $lessonNameRaw : null;
        $savedQuestions = collect();

        // خريطة أنواع الأسئلة
        $questionTypeMap = $questionTypes->keyBy('name')->map(function ($type) {
            return $type->id;
        });

        DB::beginTransaction();
        try {
            Log::info('Starting to save questions to bank', [
                'total_questions' => count($generatedQuestions),
            ]);

            foreach ($generatedQuestions as $index => $rawQuestionData) {
                $questionData = GeneratedQuestionValidator::normalize($rawQuestionData);

                Log::info('Processing question', [
                    'index' => $index + 1,
                    'type' => $questionData['type'] ?? 'unknown',
                    'has_options' => isset($questionData['options']) && is_array($questionData['options']),
                    'options_count' => isset($questionData['options']) ? count($questionData['options']) : 0,
                    'has_correct_answer' => isset($questionData['correct_answer']),
                ]);

                // تحديد نوع السؤال
                $typeName = $questionData['type'] ?? 'single_choice';

                // تحويل نوع السؤال إلى question_type_id
                $questionTypeId = $this->mapQuestionTypeToId($typeName, $questionTypeMap);

                if (! $questionTypeId) {
                    Log::warning('Question type not found, skipping question', [
                        'type' => $typeName,
                        'question' => substr($questionData['question'] ?? '', 0, 100),
                    ]);

                    continue;
                }

                $questionTypeName = QuestionType::find($questionTypeId)->name ?? '';
                $explanation = GeneratedQuestionValidator::sanitizeExplanation($questionData['explanation'] ?? null);

                Log::info('Creating question', [
                    'question_type_id' => $questionTypeId,
                    'question_text_length' => strlen($questionData['question'] ?? ''),
                ]);

                // إنشاء السؤال
                $question = QuestionBank::create([
                    'course_id' => $courseId,
                    'lesson_name' => $lessonName,
                    'question_type_id' => $questionTypeId,
                    'question_text' => $questionData['question'] ?? '',
                    'explanation' => $explanation,
                    'difficulty_level' => $questionData['difficulty'] ?? 'medium',
                    'default_grade' => $questionData['points'] ?? 10,
                    'is_active' => true,
                    'created_by' => $user->id,
                    'tags' => ['ai_generated', $programmingLanguage->slug],
                    'metadata' => [
                        'ai_created' => true,
                        'programming_language' => $programmingLanguage->name,
                        'original_type' => $typeName,
                    ],
                ]);

                // ربط السؤال باللغة
                $question->programmingLanguages()->attach($programmingLanguage->id);

                // معالجة خاصة لكل نوع من أنواع الأسئلة
                // Matching questions
                if ($questionTypeName === 'matching' && isset($questionData['pairs']) && is_array($questionData['pairs'])) {
                    $pairOrder = 1;
                    foreach ($questionData['pairs'] as $pair) {
                        if (isset($pair['question']) && isset($pair['answer'])) {
                            QuestionOption::create([
                                'question_id' => $question->id,
                                'option_text' => $pair['question'],
                                'is_correct' => true,
                                'option_order' => $pairOrder,
                                'match_pair_id' => $pairOrder,
                                'feedback' => $pair['answer'], // Store matching answer in feedback
                                'grade_percentage' => 100,
                            ]);
                            $pairOrder++;
                        }
                    }
                }
                // Ordering questions
                elseif ($questionTypeName === 'ordering' && isset($questionData['items']) && is_array($questionData['items'])) {
                    $correctOrder = $questionData['correct_order'] ?? $questionData['items'];
                    foreach ($questionData['items'] as $index => $item) {
                        $correctIndex = array_search($item, $correctOrder);
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_text' => $item,
                            'is_correct' => true,
                            'option_order' => ($correctIndex !== false ? $correctIndex + 1 : $index + 1),
                            'grade_percentage' => 100,
                        ]);
                    }
                    // Store correct order in metadata
                    $metadata = $question->metadata ?? [];
                    $metadata['correct_order'] = $correctOrder;
                    $question->update(['metadata' => $metadata]);
                }
                // Fill blanks — shared dropdown pool + per-blank correct answers
                elseif ($questionTypeName === 'fill_blanks') {
                    $blankAnswers = $questionData['blank_answers']
                        ?? $questionData['correct_answers']
                        ?? [];
                    $dropdownOptions = $questionData['dropdown_options'] ?? [];

                    if (! is_array($blankAnswers)) {
                        $blankAnswers = [];
                    }
                    if (! is_array($dropdownOptions) || count($dropdownOptions) < 1) {
                        $dropdownOptions = $blankAnswers;
                    }

                    if (! empty($blankAnswers) || ! empty($dropdownOptions)) {
                        $question->syncFillBlanksDropdownOptions($dropdownOptions, $blankAnswers);
                    }
                }
                // Numerical questions
                elseif ($questionTypeName === 'numerical') {
                    $expectedValue = $questionData['expected_value'] ?? $questionData['correct_answer'] ?? null;
                    $tolerance = $questionData['tolerance'] ?? 0.1;
                    if ($expectedValue !== null && $expectedValue !== '') {
                        $numericValue = floatval(str_replace(',', '.', (string) $expectedValue));
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_text' => (string) $expectedValue,
                            'is_correct' => true,
                            'option_order' => 1,
                            'grade_percentage' => 100,
                        ]);
                        $metadata = $question->metadata ?? [];
                        $metadata['correct_answer'] = $numericValue;
                        $metadata['expected_value'] = $numericValue;
                        $metadata['tolerance'] = floatval(str_replace(',', '.', (string) $tolerance));
                        $question->update(['metadata' => $metadata]);
                    }
                }
                // Calculated questions
                elseif ($questionTypeName === 'calculated') {
                    $formula = $questionData['formula'] ?? '';
                    $variables = $questionData['variables'] ?? [];
                    // Store formula and variables in metadata
                    $metadata = $question->metadata ?? [];
                    $metadata['formula'] = $formula;
                    $metadata['variables'] = $variables;
                    $question->update(['metadata' => $metadata]);
                }
                // Essay questions - no options needed
                elseif ($questionTypeName === 'essay') {
                    // Essay questions don't need options
                }
                // True/false — always صح / خطأ (matches manual creation)
                elseif ($questionTypeName === 'true_false') {
                    $this->createTrueFalseOptions($question, $questionData['correct_answer'] ?? null);
                }
                // Short answer — persist acceptable answers as options
                elseif ($questionTypeName === 'short_answer') {
                    $this->createShortAnswerOptions($question, $questionData['correct_answer'] ?? null);
                }
                // Regular questions with options (multiple choice, etc.)
                elseif (isset($questionData['options']) && is_array($questionData['options']) && ! empty($questionData['options'])) {
                    $correctAnswer = $questionData['correct_answer'] ?? '';

                    Log::info('Creating options for question', [
                        'question_id' => $question->id,
                        'options_count' => count($questionData['options']),
                        'correct_answer' => is_array($correctAnswer) ? json_encode($correctAnswer) : $correctAnswer,
                    ]);

                    foreach ($questionData['options'] as $index => $optionText) {
                        // استخدام منطق GLM البسيط
                        $isCorrect = false;
                        if (is_array($correctAnswer)) {
                            $isCorrect = in_array(trim($optionText), array_map('trim', $correctAnswer));
                        } else {
                            $isCorrect = trim($optionText) === trim($correctAnswer);
                        }

                        // إذا لم يطابق، جرب true/false variants للأسئلة من نوع true_false
                        if (! $isCorrect && $questionTypeId) {
                            $isCorrect = $this->isOptionCorrect($optionText, $correctAnswer, $questionTypeId);
                        }

                        Log::info('Creating option', [
                            'question_id' => $question->id,
                            'option_index' => $index + 1,
                            'option_text' => trim($optionText),
                            'is_correct' => $isCorrect,
                        ]);

                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_text' => trim($optionText),
                            'is_correct' => $isCorrect,
                            'option_order' => $index + 1,
                            'grade_percentage' => $isCorrect ? 100 : 0,
                        ]);
                    }
                }

                $savedQuestions->push($question);

                // التحقق من حفظ الخيارات بشكل صحيح
                $optionsCount = $question->options()->count();
                $correctOptionsCount = $question->options()->where('is_correct', true)->count();

                if ($questionTypeName === 'true_false' && $correctOptionsCount !== 1) {
                    Log::warning('AI true/false question saved without exactly one correct option', [
                        'question_id' => $question->id,
                        'correct_options_count' => $correctOptionsCount,
                    ]);
                }

                Log::info('Question saved with options', [
                    'question_id' => $question->id,
                    'options_count' => $optionsCount,
                    'correct_options_count' => $correctOptionsCount,
                    'question_type' => $typeName,
                ]);
            }

            DB::commit();

            Log::info('Questions created successfully', [
                'saved_count' => $savedQuestions->count(),
                'programming_language' => $programmingLanguage->name,
            ]);

            // التحقق النهائي من جميع الأسئلة المحفوظة
            foreach ($savedQuestions as $savedQuestion) {
                $options = $savedQuestion->options()->get();
                Log::info('Final verification - Question options', [
                    'question_id' => $savedQuestion->id,
                    'question_type' => $savedQuestion->questionType->name ?? 'unknown',
                    'options' => $options->map(function ($opt) {
                        return [
                            'id' => $opt->id,
                            'text' => $opt->option_text,
                            'is_correct' => $opt->is_correct,
                            'order' => $opt->option_order,
                        ];
                    })->toArray(),
                ]);
            }

            return $savedQuestions;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving questions: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * تحويل نوع السؤال إلى ID
     */
    private function mapQuestionTypeToId(string $typeName, Collection $questionTypeMap): ?int
    {
        // محاولة المطابقة المباشرة
        if ($questionTypeMap->has($typeName)) {
            return $questionTypeMap->get($typeName);
        }

        // محاولة المطابقة بالاسم المعروف
        $typeMapping = [
            'single_choice' => 'multiple_choice_single',
            'multiple_choice' => 'multiple_choice_multiple',
            'multiple_choice_single' => 'multiple_choice_single',
            'multiple_choice_multiple' => 'multiple_choice_multiple',
            'true_false' => 'true_false',
            'short_answer' => 'short_answer',
            'essay' => 'essay',
            'matching' => 'matching',
            'ordering' => 'ordering',
            'fill_blanks' => 'fill_blanks',
            'fill_blank' => 'fill_blanks',
            'numerical' => 'numerical',
            'calculated' => 'calculated',
        ];

        $mappedName = $typeMapping[$typeName] ?? $typeName;

        // البحث في قاعدة البيانات
        $questionType = QuestionType::where('name', $mappedName)->first();
        if ($questionType) {
            return $questionType->id;
        }

        // محاولة البحث بالاسم مباشرة
        $questionType = QuestionType::where('name', $typeName)->first();
        if ($questionType) {
            return $questionType->id;
        }

        return null;
    }

    /**
     * إنشاء خيارات صح/خطأ بشكل موحّد مع الإنشاء اليدوي.
     */
    private function createTrueFalseOptions(QuestionBank $question, mixed $correctAnswer): void
    {
        $normalized = GeneratedQuestionValidator::normalizeTrueFalseAnswer($correctAnswer);

        if ($normalized === null) {
            Log::warning('Unrecognized true/false correct_answer for AI question', [
                'question_id' => $question->id,
                'correct_answer' => $correctAnswer,
            ]);
            $normalized = 'صح';
        }

        $trueIsCorrect = $normalized === 'صح';
        $falseIsCorrect = $normalized === 'خطأ';

        QuestionOption::create([
            'question_id' => $question->id,
            'option_text' => 'صح',
            'is_correct' => $trueIsCorrect,
            'option_order' => 1,
            'grade_percentage' => $trueIsCorrect ? 100 : 0,
        ]);

        QuestionOption::create([
            'question_id' => $question->id,
            'option_text' => 'خطأ',
            'is_correct' => $falseIsCorrect,
            'option_order' => 2,
            'grade_percentage' => $falseIsCorrect ? 100 : 0,
        ]);
    }

    /**
     * @param  mixed  $correctAnswer  string or list of acceptable answers
     */
    private function createShortAnswerOptions(QuestionBank $question, mixed $correctAnswer): void
    {
        $answers = is_array($correctAnswer) ? $correctAnswer : [$correctAnswer];
        $order = 1;

        foreach ($answers as $answer) {
            $text = trim((string) $answer);
            if ($text === '') {
                continue;
            }

            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => $text,
                'is_correct' => true,
                'option_order' => $order++,
                'grade_percentage' => 100,
            ]);
        }
    }

    /**
     * التحقق من صحة الخيار
     * مطابق لمنطق AIQuestionGenerationService (GLM) مع دعم true/false variants
     */
    private function isOptionCorrect(string $optionText, $correctAnswer, ?int $questionTypeId = null): bool
    {
        // معالجة المصفوفات (مثل multiple_choice_multiple)
        if (is_array($correctAnswer)) {
            return in_array(trim($optionText), array_map('trim', $correctAnswer));
        }

        // منطق بسيط مثل GLM
        $isCorrect = trim($optionText) === trim($correctAnswer);

        // دعم true/false variants فقط للأسئلة من نوع true_false
        if (! $isCorrect && $questionTypeId) {
            $trueFalseTypeId = QuestionType::where('name', 'true_false')->first()?->id;
            if ($questionTypeId === $trueFalseTypeId) {
                $optionTextNormalized = strtolower(trim($optionText));
                $correctAnswerNormalized = strtolower(trim($correctAnswer));

                // دعم أشكال مختلفة لـ true/false
                $trueVariants = ['صح', 'true', '1', 'صحيح', 'نعم', 'yes'];
                $falseVariants = ['خطأ', 'false', '0', 'خاطئ', 'لا', 'no'];

                if (in_array($optionTextNormalized, $trueVariants)) {
                    return in_array($correctAnswerNormalized, $trueVariants);
                } elseif (in_array($optionTextNormalized, $falseVariants)) {
                    return in_array($correctAnswerNormalized, $falseVariants);
                }
            }
        }

        return $isCorrect;
    }

    /**
     * تحليل JSON للأسئلة المولدة
     * مطابق لمنطق AIQuestionGenerationService (GLM)
     */
    private function parseGeneratedQuestions(string $response): array
    {
        Log::info('Parsing AI response for questions', [
            'response_length' => strlen($response),
            'response_preview' => substr($response, 0, 500),
        ]);

        // محاولة إصلاح encoding issues
        if (! mb_check_encoding($response, 'UTF-8')) {
            $response = mb_convert_encoding($response, 'UTF-8', 'auto');
            Log::info('Fixed encoding issues in response');
        }

        // تنظيف الرد من markdown code blocks
        $cleanedResponse = $response;

        // إزالة ```json و ``` من البداية والنهاية
        $cleanedResponse = preg_replace('/^```(?:json)?\s*/i', '', trim($cleanedResponse));
        $cleanedResponse = preg_replace('/\s*```$/i', '', $cleanedResponse);

        // إزالة أي BOM أو characters غريبة
        $cleanedResponse = preg_replace('/^\xEF\xBB\xBF/', '', $cleanedResponse);
        $cleanedResponse = trim($cleanedResponse);

        // محاولة 1: تحليل JSON مباشرة
        $decoded = json_decode($cleanedResponse, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            Log::info('JSON parsed successfully (direct)', ['count' => count($decoded)]);

            return GeneratedQuestionValidator::validate($decoded);
        }

        // محاولة 2: استخراج JSON array باستخدام balanced bracket matching
        $extractedArray = $this->extractJsonArray($cleanedResponse);
        if ($extractedArray !== null) {
            $decoded = json_decode($extractedArray, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && count($decoded) > 0) {
                Log::info('JSON parsed successfully (balanced bracket extraction)', ['count' => count($decoded)]);

                return GeneratedQuestionValidator::validate($decoded);
            }
        }

        // محاولة 3: البحث عن [ و ] يدوياً مع validation محسّن
        $jsonStart = strpos($cleanedResponse, '[');
        $jsonEnd = strrpos($cleanedResponse, ']');

        if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
            $jsonString = substr($cleanedResponse, $jsonStart, $jsonEnd - $jsonStart + 1);

            // محاولة تحليل JSON
            $decoded = json_decode($jsonString, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && count($decoded) > 0) {
                Log::info('JSON parsed successfully (manual extraction)', ['count' => count($decoded)]);

                return GeneratedQuestionValidator::validate($decoded);
            }

            // إذا فشل، حاول استخدام balanced bracket matching على الجزء المستخرج
            $extractedArray = $this->extractJsonArray($jsonString);
            if ($extractedArray !== null) {
                $decoded = json_decode($extractedArray, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && count($decoded) > 0) {
                    Log::info('JSON parsed successfully (manual + balanced bracket)', ['count' => count($decoded)]);

                    return GeneratedQuestionValidator::validate($decoded);
                }
            }
        }

        // محاولة 4: البحث عن جميع JSON objects باستخدام balanced bracket matching (fallback)
        // هذه المحاولة تبحث عن جميع الـ objects التي تحتوي على "question"
        $objects = $this->extractAllJsonObjects($cleanedResponse);
        if (! empty($objects)) {
            Log::info('JSON parsed successfully (multiple objects)', ['count' => count($objects)]);

            return GeneratedQuestionValidator::validate($objects);
        }

        // محاولة 4.5: البحث عن JSON object واحد (fallback أخير)
        if (preg_match('/\{[^{}]*"question"[^{}]*\}/s', $cleanedResponse, $matches)) {
            $decoded = json_decode('['.$matches[0].']', true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                Log::info('JSON parsed successfully (single object fallback)', ['count' => count($decoded)]);

                return GeneratedQuestionValidator::validate($decoded);
            }
        }

        // محاولة 5: تحليل نص غير JSON (fallback)
        $questions = $this->parseTextBasedQuestions($cleanedResponse);
        if (! empty($questions)) {
            Log::info('Questions parsed from text format', ['count' => count($questions)]);

            return GeneratedQuestionValidator::validate($questions);
        }

        Log::error('Failed to parse questions from response', [
            'json_error' => json_last_error_msg(),
            'response' => substr($cleanedResponse, 0, 1000),
        ]);

        throw new \Exception('فشل في تحليل استجابة AI: '.json_last_error_msg());
    }

    /**
     * استخراج JSON array من نص باستخدام balanced bracket matching
     *
     * @param  string  $text  النص الذي يحتوي على JSON array
     * @return string|null JSON array string أو null إذا لم يتم العثور عليه
     */
    private function extractJsonArray(string $text): ?string
    {
        $startPos = strpos($text, '[');
        if ($startPos === false) {
            return null;
        }

        $depth = 0;
        $inString = false;
        $escapeNext = false;
        $length = strlen($text);

        for ($i = $startPos; $i < $length; $i++) {
            $char = $text[$i];

            if ($escapeNext) {
                $escapeNext = false;

                continue;
            }

            if ($char === '\\') {
                $escapeNext = true;

                continue;
            }

            if ($char === '"' && ! $escapeNext) {
                $inString = ! $inString;

                continue;
            }

            if ($inString) {
                continue;
            }

            if ($char === '[') {
                $depth++;
            } elseif ($char === ']') {
                $depth--;
                if ($depth === 0) {
                    // وجدنا الـ ] المقابل
                    return substr($text, $startPos, $i - $startPos + 1);
                }
            }
        }

        return null;
    }

    /**
     * استخراج جميع JSON objects من نص باستخدام balanced bracket matching
     *
     * @param  string  $text  النص الذي يحتوي على JSON objects
     * @return array قائمة من JSON objects
     */
    private function extractAllJsonObjects(string $text): array
    {
        $objects = [];
        $pos = 0;
        $length = strlen($text);

        while ($pos < $length) {
            $startPos = strpos($text, '{', $pos);
            if ($startPos === false) {
                break;
            }

            $depth = 0;
            $inString = false;
            $escapeNext = false;

            for ($i = $startPos; $i < $length; $i++) {
                $char = $text[$i];

                if ($escapeNext) {
                    $escapeNext = false;

                    continue;
                }

                if ($char === '\\') {
                    $escapeNext = true;

                    continue;
                }

                if ($char === '"' && ! $escapeNext) {
                    $inString = ! $inString;

                    continue;
                }

                if ($inString) {
                    continue;
                }

                if ($char === '{') {
                    $depth++;
                } elseif ($char === '}') {
                    $depth--;
                    if ($depth === 0) {
                        // وجدنا الـ } المقابل
                        $jsonString = substr($text, $startPos, $i - $startPos + 1);
                        $obj = json_decode($jsonString, true);

                        if (json_last_error() === JSON_ERROR_NONE && is_array($obj) && isset($obj['question'])) {
                            $objects[] = $obj;
                        }

                        $pos = $i + 1;
                        break;
                    }
                }
            }

            if ($depth !== 0) {
                // لم نجد الـ } المقابل، انتقل إلى الموضع التالي
                $pos = $startPos + 1;
            }
        }

        return $objects;
    }

    /**
     * محاولة تحليل الأسئلة من نص غير JSON
     */
    private function parseTextBasedQuestions(string $text): array
    {
        $questions = [];

        // البحث عن أنماط مثل "1. سؤال" أو "السؤال 1:"
        $patterns = [
            '/(?:سؤال|السؤال|Question)\s*(\d+)[:\.\)]\s*(.+?)(?=(?:سؤال|السؤال|Question)\s*\d+|$)/is',
            '/(\d+)[:\.\)]\s*(.+?)(?=\d+[:\.\)]|$)/s',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $questionText = trim($match[2] ?? $match[1] ?? '');
                    if (strlen($questionText) > 10) {
                        $questions[] = [
                            'type' => 'short_answer',
                            'question' => $questionText,
                            'options' => [],
                            'correct_answer' => '',
                            'explanation' => '',
                            'difficulty' => 'medium',
                            'points' => 10,
                        ];
                    }
                }

                if (! empty($questions)) {
                    break;
                }
            }
        }

        return $questions;
    }
}
