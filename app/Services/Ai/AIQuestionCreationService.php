<?php

namespace App\Services\Ai;

use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\QuestionType;
use App\Models\ProgrammingLanguage;
use App\Models\Lesson;
use App\Models\User;
use App\Models\AIModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\Ai\AIProviderFactory;

class AIQuestionCreationService
{
    public function __construct(
        private AIQuestionGenerationService $generationService,
        private AIModelService $modelService
    ) {}

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
        if (!$courseId && $lesson->module && $lesson->module->section) {
            $courseId = $lesson->module->section->course_id;
        }

        return $this->createQuestionsFromText(
            $content,
            $programmingLanguage,
            $questionTypes,
            array_merge($options, [
                'lesson_id' => $lesson->id,
                'course_id' => $courseId,
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
        $model = $options['model'] ?? $this->modelService->getBestModelFor('question_generation');
        $numberOfQuestions = $options['number_of_questions'] ?? 5;
        $difficultyLevel = $options['difficulty_level'] ?? 'mixed';

        if (!$model) {
            throw new \Exception('لا يوجد موديل AI متاح لتوليد الأسئلة');
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
                $model
            );

            // حفظ الأسئلة في بنك الأسئلة
            return $this->saveQuestionsToBank(
                $generatedQuestions,
                $programmingLanguage,
                $questionTypes,
                $options
            );
        } catch (\Exception $e) {
            Log::error('Error creating questions: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * توليد الأسئلة باستخدام AI
     */
    private function generateQuestionsWithAI(
        string $text,
        ProgrammingLanguage $programmingLanguage,
        Collection $questionTypes,
        int $numberOfQuestions,
        string $difficultyLevel,
        AIModel $model
    ): array {
        // بناء prompt مع أنواع الأسئلة المطلوبة
        $questionTypeNames = $questionTypes->pluck('display_name')->toArray();
        $questionTypeNamesStr = implode('، ', $questionTypeNames);

        $prompt = $this->buildPrompt(
            $text,
            $programmingLanguage->display_name,
            $questionTypeNamesStr,
            $numberOfQuestions,
            $difficultyLevel
        );

        // استخدام نفس منطق AIQuestionGenerationService
        $provider = AIProviderFactory::create($model);
        $response = $provider->generateText($prompt, [
            'max_tokens' => $model->max_tokens ?? 4000,
            'temperature' => $model->temperature ?? 0.7,
        ]);

        // تحليل الاستجابة
        $questions = $this->parseGeneratedQuestions($response);

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

يرجى إنشاء الأسئلة بالصيغة JSON التالية:
[
  {
    \"type\": \"نوع السؤال (single_choice, multiple_choice, true_false, short_answer)\",
    \"question\": \"نص السؤال\",
    \"options\": [\"الخيار 1\", \"الخيار 2\", ...],
    \"correct_answer\": \"الإجابة الصحيحة\",
    \"explanation\": \"شرح الإجابة\",
    \"difficulty\": \"easy|medium|hard\",
    \"points\": 10
  }
]

ملاحظات:
- تأكد من أن الأسئلة متنوعة في الأنواع المطلوبة
- الإجابات الصحيحة يجب أن تكون دقيقة
- الشرح يجب أن يكون واضحاً ومفيداً
- استخدم مصطلحات متعلقة بـ {$languageName}";
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
        $savedQuestions = collect();

        // خريطة أنواع الأسئلة
        $questionTypeMap = $questionTypes->keyBy('name')->map(function($type) {
            return $type->id;
        });

        DB::beginTransaction();
        try {
            foreach ($generatedQuestions as $questionData) {
                // تحديد نوع السؤال
                $typeName = $questionData['type'] ?? 'single_choice';
                
                // تحويل نوع السؤال إلى question_type_id
                $questionTypeId = $this->mapQuestionTypeToId($typeName, $questionTypeMap);

                if (!$questionTypeId) {
                    Log::warning('Question type not found, skipping question', [
                        'type' => $typeName,
                        'question' => substr($questionData['question'] ?? '', 0, 100)
                    ]);
                    continue;
                }

                // إنشاء السؤال
                $question = QuestionBank::create([
                    'course_id' => $courseId,
                    'question_type_id' => $questionTypeId,
                    'question_text' => $questionData['question'] ?? '',
                    'explanation' => $questionData['explanation'] ?? '',
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

                // إضافة الخيارات إذا كانت موجودة
                if (isset($questionData['options']) && is_array($questionData['options']) && !empty($questionData['options'])) {
                    $correctAnswer = $questionData['correct_answer'] ?? '';
                    foreach ($questionData['options'] as $index => $optionText) {
                        $isCorrect = $this->isOptionCorrect($optionText, $correctAnswer);

                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_text' => $optionText,
                            'is_correct' => $isCorrect,
                            'option_order' => $index + 1,
                            'grade_percentage' => $isCorrect ? 100 : 0,
                        ]);
                    }
                } elseif ($questionTypeId && $questionTypeId == QuestionType::where('name', 'true_false')->first()?->id) {
                    // إذا كان السؤال من نوع true_false ولم تكن هناك خيارات، أنشئ خيارين افتراضيين
                    $correctAnswer = $questionData['correct_answer'] ?? '';
                    
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => 'صح',
                        'is_correct' => $this->isOptionCorrect('صح', $correctAnswer),
                        'option_order' => 1,
                        'grade_percentage' => 0,
                    ]);
                    
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => 'خطأ',
                        'is_correct' => $this->isOptionCorrect('خطأ', $correctAnswer),
                        'option_order' => 2,
                        'grade_percentage' => 0,
                    ]);
                }

                $savedQuestions->push($question);
            }

            DB::commit();
            
            Log::info('Questions created successfully', [
                'saved_count' => $savedQuestions->count(),
                'programming_language' => $programmingLanguage->name,
            ]);
            
            return $savedQuestions;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving questions: ' . $e->getMessage(), [
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
            'true_false' => 'true_false',
            'short_answer' => 'short_answer',
            'essay' => 'essay',
        ];

        $mappedName = $typeMapping[$typeName] ?? $typeName;
        
        // البحث في قاعدة البيانات
        $questionType = QuestionType::where('name', $mappedName)->first();
        if ($questionType) {
            return $questionType->id;
        }

        return null;
    }

    /**
     * التحقق من صحة الخيار
     */
    private function isOptionCorrect(string $optionText, $correctAnswer): bool
    {
        if (is_array($correctAnswer)) {
            return in_array(trim($optionText), array_map('trim', $correctAnswer));
        }
        
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
        
        return trim($optionText) === trim($correctAnswer);
    }

    /**
     * تحليل JSON للأسئلة المولدة
     */
    private function parseGeneratedQuestions(string $response): array
    {
        Log::info('Parsing AI response for questions', [
            'response_length' => strlen($response),
            'response_preview' => substr($response, 0, 500),
        ]);

        // محاولة استخراج JSON من الاستجابة
        $jsonStart = strpos($response, '[');
        $jsonEnd = strrpos($response, ']');
        
        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
        } else {
            // محاولة استخراج JSON من code blocks
            if (preg_match('/```(?:json)?\s*(\[.*?\])\s*```/s', $response, $matches)) {
                $jsonString = $matches[1];
            } else {
                $jsonString = $response;
            }
        }

        $decoded = json_decode($jsonString, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('JSON decode error', [
                'error' => json_last_error_msg(),
                'json_string' => substr($jsonString, 0, 1000),
            ]);
            throw new \Exception('فشل في تحليل استجابة AI: ' . json_last_error_msg());
        }

        if (!is_array($decoded)) {
            throw new \Exception('الاستجابة ليست مصفوفة من الأسئلة');
        }

        return $this->validateGeneratedQuestions($decoded);
    }

    /**
     * التحقق من صحة الأسئلة المولدة
     */
    private function validateGeneratedQuestions(array $questions): array
    {
        $validated = [];

        foreach ($questions as $question) {
            if (!isset($question['question']) || empty($question['question'])) {
                continue;
            }

            $validated[] = [
                'type' => $question['type'] ?? 'single_choice',
                'question' => $question['question'],
                'options' => $question['options'] ?? [],
                'correct_answer' => $question['correct_answer'] ?? '',
                'explanation' => $question['explanation'] ?? '',
                'difficulty' => $question['difficulty'] ?? 'medium',
                'points' => $question['points'] ?? 10,
            ];
        }

        return $validated;
    }
}

