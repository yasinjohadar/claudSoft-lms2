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
        Log::info('Starting question generation API call', [
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

        Log::info('Question generation API response received', [
            'response_length' => strlen($response ?? ''),
            'response_empty' => empty($response),
            'last_error' => $provider->getLastError(),
        ]);

        if (!$response || empty($response)) {
            $lastError = $provider->getLastError() ?? 'فشل في توليد الأسئلة - لم يتم الحصول على رد من API';
            Log::error('Question generation failed - empty response', [
                'last_error' => $lastError,
            ]);
            throw new \Exception($lastError);
        }

        // حفظ الرد الكامل في logs للتصحيح
        Log::info('Full AI response received', [
            'response_length' => strlen($response),
            'response_preview' => substr($response, 0, 1000),
            'response_full' => $response, // حفظ الرد الكامل
        ]);

        // تحليل الاستجابة
        $questions = $this->parseGeneratedQuestions($response);

        Log::info('Questions parsed successfully', [
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
            Log::info('Starting to save questions to bank', [
                'total_questions' => count($generatedQuestions),
            ]);

            foreach ($generatedQuestions as $index => $questionData) {
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

                if (!$questionTypeId) {
                    Log::warning('Question type not found, skipping question', [
                        'type' => $typeName,
                        'question' => substr($questionData['question'] ?? '', 0, 100)
                    ]);
                    continue;
                }

                Log::info('Creating question', [
                    'question_type_id' => $questionTypeId,
                    'question_text_length' => strlen($questionData['question'] ?? ''),
                ]);

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
                        if (!$isCorrect && $questionTypeId) {
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
                } elseif ($questionTypeId && $questionTypeId == QuestionType::where('name', 'true_false')->first()?->id) {
                    // إذا كان السؤال من نوع true_false ولم تكن هناك خيارات، أنشئ خيارين افتراضيين
                    $correctAnswer = $questionData['correct_answer'] ?? '';
                    
                    Log::info('Creating default true/false options', [
                        'question_id' => $question->id,
                        'correct_answer' => $correctAnswer,
                    ]);
                    
                    $trueIsCorrect = $this->isOptionCorrect('صح', $correctAnswer, $questionTypeId);
                    $falseIsCorrect = $this->isOptionCorrect('خطأ', $correctAnswer, $questionTypeId);
                    
                    Log::info('True/False options correctness', [
                        'question_id' => $question->id,
                        'true_is_correct' => $trueIsCorrect,
                        'false_is_correct' => $falseIsCorrect,
                    ]);
                    
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

                $savedQuestions->push($question);
                
                // التحقق من حفظ الخيارات بشكل صحيح
                $optionsCount = $question->options()->count();
                $correctOptionsCount = $question->options()->where('is_correct', true)->count();
                
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
                    'options' => $options->map(function($opt) {
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
        if (!$isCorrect && $questionTypeId) {
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
        if (!mb_check_encoding($response, 'UTF-8')) {
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
            return $this->validateGeneratedQuestions($decoded);
        }

        // محاولة 2: استخراج JSON array من النص
        if (preg_match('/\[\s*\{.*?\}\s*\]/s', $cleanedResponse, $matches)) {
            $jsonString = $matches[0];
            $decoded = json_decode($jsonString, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                Log::info('JSON parsed successfully (regex array)', ['count' => count($decoded)]);
                return $this->validateGeneratedQuestions($decoded);
            }
        }

        // محاولة 3: البحث عن [ و ] يدوياً
        $jsonStart = strpos($cleanedResponse, '[');
        $jsonEnd = strrpos($cleanedResponse, ']');

        if ($jsonStart !== false && $jsonEnd !== false && $jsonEnd > $jsonStart) {
            $jsonString = substr($cleanedResponse, $jsonStart, $jsonEnd - $jsonStart + 1);
            $decoded = json_decode($jsonString, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                Log::info('JSON parsed successfully (manual extraction)', ['count' => count($decoded)]);
                return $this->validateGeneratedQuestions($decoded);
            }
        }

        // محاولة 4: البحث عن JSON object واحد
        if (preg_match('/\{[^{}]*"question"[^{}]*\}/s', $cleanedResponse, $matches)) {
            $decoded = json_decode('[' . $matches[0] . ']', true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                Log::info('JSON parsed successfully (single object)', ['count' => count($decoded)]);
                return $this->validateGeneratedQuestions($decoded);
            }
        }

        // محاولة 5: تحليل نص غير JSON (fallback)
        $questions = $this->parseTextBasedQuestions($cleanedResponse);
        if (!empty($questions)) {
            Log::info('Questions parsed from text format', ['count' => count($questions)]);
            return $this->validateGeneratedQuestions($questions);
        }

        Log::error('Failed to parse questions from response', [
            'json_error' => json_last_error_msg(),
            'response' => substr($cleanedResponse, 0, 1000),
        ]);

        throw new \Exception('فشل في تحليل استجابة AI: ' . json_last_error_msg());
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
                
                if (!empty($questions)) {
                    break;
                }
            }
        }

        return $questions;
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

