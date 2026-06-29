<?php

namespace App\Services\Ai;

use App\Ai\Agents\QuestionGenerationPlainAgent;
use App\Models\AIQuestionGeneration;
use App\Models\Course;
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

class AIQuestionGenerationService
{
    public function __construct(
        private AIModelService $modelService,
        private AIPromptService $promptService,
        private AIQuestionCreationService $creationService,
        private LaravelAiProviderManager $providerManager,
        private LaravelAiPromptRunner $promptRunner,
    ) {}

    /**
     * توليد أسئلة من درس
     */
    public function generateFromLesson(Lesson $lesson, array $options = []): AIQuestionGeneration
    {
        $content = $lesson->description ?? $lesson->title;

        // جمع محتوى إضافي من الدرس
        if ($lesson->attachments) {
            // يمكن إضافة محتوى من المرفقات
        }

        // Get course from lesson through module and section
        $courseId = null;
        if ($lesson->module && $lesson->module->section) {
            $courseId = $lesson->module->section->course_id;
        }

        return $this->generateFromText($content, array_merge($options, [
            'lesson_id' => $lesson->id,
            'course_id' => $courseId,
            'source_type' => 'lesson_content',
        ]));
    }

    /**
     * توليد أسئلة من نص
     */
    public function generateFromText(string $text, array $options = []): AIQuestionGeneration
    {
        $user = $options['user'] ?? auth()->user();
        $laraModel = $options['laravel_model'] ?? null;

        if ($laraModel instanceof LaravelAiModel) {
            $generation = AIQuestionGeneration::create([
                'user_id' => $user->id,
                'course_id' => $options['course_id'] ?? null,
                'lesson_id' => $options['lesson_id'] ?? null,
                'source_type' => $options['source_type'] ?? 'manual_text',
                'source_content' => $text,
                'question_type' => $options['question_type'] ?? 'mixed',
                'number_of_questions' => $options['number_of_questions'] ?? 5,
                'difficulty_level' => $options['difficulty_level'] ?? 'mixed',
                'ai_model_id' => null,
                'laravel_ai_model_id' => $laraModel->id,
                'status' => 'pending',
            ]);
        } else {
            $model = $options['model'] ?? $this->modelService->getBestModelFor('question_generation');

            if (! $model) {
                throw new \Exception('لا يوجد موديل AI متاح لتوليد الأسئلة');
            }

            $generation = AIQuestionGeneration::create([
                'user_id' => $user->id,
                'course_id' => $options['course_id'] ?? null,
                'lesson_id' => $options['lesson_id'] ?? null,
                'source_type' => $options['source_type'] ?? 'manual_text',
                'source_content' => $text,
                'question_type' => $options['question_type'] ?? 'mixed',
                'number_of_questions' => $options['number_of_questions'] ?? 5,
                'difficulty_level' => $options['difficulty_level'] ?? 'mixed',
                'ai_model_id' => $model->id,
                'laravel_ai_model_id' => null,
                'status' => 'pending',
            ]);
        }

        $this->processGeneration($generation);

        return $generation;
    }

    /**
     * توليد أسئلة من موضوع
     */
    public function generateFromTopic(string $topic, array $options = []): AIQuestionGeneration
    {
        return $this->generateFromText($topic, array_merge($options, [
            'source_type' => 'topic',
        ]));
    }

    /**
     * إنشاء طلب توليد من بنك الأسئلة (معاينة ثم حفظ).
     */
    public function createQuestionBankGeneration(array $options): AIQuestionGeneration
    {
        $user = $options['user'] ?? auth()->user();
        $laraModel = $options['laravel_model'] ?? null;
        $questionTypeIds = array_values(array_map('intval', $options['question_type_ids'] ?? []));
        $lessonNameRaw = isset($options['lesson_name']) ? trim((string) $options['lesson_name']) : '';
        $lessonName = $lessonNameRaw !== '' ? $lessonNameRaw : null;

        $base = [
            'user_id' => $user->id,
            'course_id' => $options['course_id'] ?? null,
            'lesson_id' => $options['lesson_id'] ?? null,
            'lesson_name' => $lessonName,
            'programming_language_id' => $options['programming_language_id'],
            'source_type' => $options['source_type'] ?? 'manual_text',
            'source_content' => $options['source_content'],
            'question_type' => 'mixed',
            'question_type_ids' => $questionTypeIds,
            'number_of_questions' => $options['number_of_questions'] ?? 5,
            'difficulty_level' => $options['difficulty_level'] ?? 'mixed',
            'default_grade' => $options['default_grade'] ?? 1,
            'status' => 'pending',
            'saved_indices' => [],
            'saved_question_ids' => [],
        ];

        if ($laraModel instanceof LaravelAiModel) {
            $generation = AIQuestionGeneration::create(array_merge($base, [
                'ai_model_id' => null,
                'laravel_ai_model_id' => $laraModel->id,
            ]));
        } else {
            $model = $options['model'] ?? $this->modelService->getBestModelFor('question_generation');
            if (! $model) {
                throw new \Exception('لا يوجد موديل AI متاح لتوليد الأسئلة');
            }

            $generation = AIQuestionGeneration::create(array_merge($base, [
                'ai_model_id' => $model->id,
                'laravel_ai_model_id' => null,
            ]));
        }

        $this->processGeneration($generation);

        return $generation->fresh();
    }

    /**
     * معالجة التوليد
     */
    public function processGeneration(AIQuestionGeneration $generation): array
    {
        set_time_limit(180);

        if ($generation->laravel_ai_model_id) {
            $laraModel = $generation->laravelAiModel ?? LaravelAiModel::query()->find($generation->laravel_ai_model_id);
            if (! $laraModel || ! $laraModel->is_active) {
                $generation->update([
                    'status' => 'failed',
                    'error_message' => 'موديل Laravel AI غير متاح أو غير نشط.',
                ]);
                throw new \Exception('موديل Laravel AI غير متاح أو غير نشط.');
            }

            return $this->processGenerationWithLaravelSdk($generation, $laraModel);
        }

        $generation->update(['status' => 'processing']);

        try {
            $model = $generation->model;
            if (! $model) {
                throw new \Exception('الموديل غير موجود');
            }

            // بناء الـ prompt
            $prompt = $this->resolvePrompt($generation);

            // حساب max_tokens بناءً على عدد الأسئلة (تقريباً 800 token لكل سؤال للأسئلة الطويلة)
            // زيادة العدد لضمان عدم قطع الاستجابة
            $requiredTokens = max(4000, $generation->number_of_questions * 800);
            $maxTokens = min($requiredTokens, $model->max_tokens ?: 16000);

            Log::info('Question generation tokens calculation', [
                'generation_id' => $generation->id,
                'required_questions' => $generation->number_of_questions,
                'calculated_tokens' => $requiredTokens,
                'max_tokens' => $maxTokens,
                'model_max_tokens' => $model->max_tokens,
            ]);

            // التحقق من API Key
            $apiKey = $model->getDecryptedApiKey();
            if (! $apiKey) {
                throw new \Exception('API Key غير موجود للموديل المحدد. يرجى إعداد API Key أولاً.');
            }

            Log::info('Starting question generation API call', [
                'generation_id' => $generation->id,
                'model_id' => $model->id,
                'model_name' => $model->name,
                'provider' => $model->provider,
                'has_api_key' => ! empty($apiKey),
                'prompt_length' => strlen($prompt),
            ]);

            // إرسال الطلب
            $provider = AIProviderFactory::create($model);
            $response = $provider->generateText($prompt, [
                'max_tokens' => $maxTokens,
                'temperature' => 0.7, // درجة حرارة معتدلة للتنوع مع الدقة
            ]);

            Log::info('Question generation API response', [
                'generation_id' => $generation->id,
                'response_length' => strlen($response ?? ''),
                'response_empty' => empty($response),
                'last_error' => $provider->getLastError(),
            ]);

            if (! $response || empty($response)) {
                // محاولة الحصول على معلومات أكثر من آخر خطأ
                $lastError = $provider->getLastError() ?? 'فشل في توليد الأسئلة - لم يتم الحصول على رد من API';
                throw new \Exception($lastError);
            }

            // حفظ الرد الكامل في logs للتصحيح
            Log::info('Full AI response received', [
                'generation_id' => $generation->id,
                'response_length' => strlen($response),
                'response_preview' => substr($response, 0, 1000),
                'response_full' => $response, // حفظ الرد الكامل
            ]);

            // محاولة تحليل JSON
            $questions = $this->parseGeneratedQuestions($response);

            // التحقق من صحة الأسئلة
            $validatedQuestions = $this->validateGeneratedQuestions($questions);

            // التحقق من العدد المطلوب
            $requiredCount = $generation->number_of_questions;
            $actualCount = count($validatedQuestions);
            $warningMessage = null;

            if ($actualCount < $requiredCount) {
                $missingCount = $requiredCount - $actualCount;
                $warningMessage = "تم توليد {$actualCount} سؤال فقط من {$requiredCount} المطلوبة. ({$missingCount} سؤال مفقود)";

                Log::warning('Question generation incomplete', [
                    'generation_id' => $generation->id,
                    'required' => $requiredCount,
                    'actual' => $actualCount,
                    'missing' => $missingCount,
                    'response_length' => strlen($response),
                ]);
            }

            // حفظ النتائج مع رسالة التحذير إن وجدت
            $generation->update([
                'status' => 'completed',
                'generated_questions' => $validatedQuestions,
                'prompt' => $prompt,
                'tokens_used' => $provider->estimateTokens($prompt.$response),
                'cost' => $model->getCost($provider->estimateTokens($prompt.$response)),
                'error_message' => $warningMessage, // نستخدم error_message لحفظ التحذير
            ]);

            return $validatedQuestions;
        } catch (\Exception $e) {
            Log::error('Error processing question generation: '.$e->getMessage(), [
                'generation_id' => $generation->id,
            ]);

            $generation->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function processGenerationWithLaravelSdk(AIQuestionGeneration $generation, LaravelAiModel $laraModel): array
    {
        $generation->update(['status' => 'processing']);

        try {
            $prompt = $this->resolvePrompt($generation);

            Log::info('Question generation (Laravel AI SDK) starting', [
                'generation_id' => $generation->id,
                'laravel_ai_model_id' => $laraModel->id,
                'prompt_length' => strlen($prompt),
            ]);

            $response = $this->providerManager->runWithModel($laraModel, function () use ($laraModel, $prompt) {
                return $this->promptRunner->runPlain($laraModel, new QuestionGenerationPlainAgent, $prompt, 180);
            });

            $responseText = trim((string) $response->text);
            if ($responseText === '') {
                throw new \Exception('لم يُرجع الموديل أي نص. جرّب موديلاً آخر أو زد max_tokens في إعدادات موديل Laravel AI.');
            }

            Log::info('Question generation (Laravel AI SDK) response', [
                'generation_id' => $generation->id,
                'response_length' => strlen($responseText),
                'preview' => mb_substr($responseText, 0, 500),
            ]);

            $questions = $this->parseGeneratedQuestions($responseText);
            $validatedQuestions = $this->validateGeneratedQuestions($questions);

            $requiredCount = $generation->number_of_questions;
            $actualCount = count($validatedQuestions);
            $warningMessage = null;

            if ($actualCount < $requiredCount) {
                $missingCount = $requiredCount - $actualCount;
                $warningMessage = "تم توليد {$actualCount} سؤال فقط من {$requiredCount} المطلوبة. ({$missingCount} سؤال مفقود)";

                Log::warning('Question generation incomplete (Laravel AI SDK)', [
                    'generation_id' => $generation->id,
                    'required' => $requiredCount,
                    'actual' => $actualCount,
                    'missing' => $missingCount,
                ]);
            }

            $tokensUsed = ($response->usage->promptTokens ?? 0) + ($response->usage->completionTokens ?? 0);

            $generation->update([
                'status' => 'completed',
                'generated_questions' => $validatedQuestions,
                'tokens_used' => $tokensUsed,
                'cost' => 0,
                'error_message' => $warningMessage,
            ]);

            return $validatedQuestions;
        } catch (\Exception $e) {
            Log::error('Error processing question generation (Laravel AI SDK): '.$e->getMessage(), [
                'generation_id' => $generation->id,
            ]);

            $generation->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * حفظ الأسئلة المولدة
     */
    public function saveGeneratedQuestions(AIQuestionGeneration $generation, ?array $selectedIndices = null): Collection
    {
        if ($generation->status !== 'completed') {
            throw new \Exception('التوليد لم يكتمل بعد');
        }

        $allQuestions = $generation->generated_questions ?? [];
        if (empty($allQuestions)) {
            return collect();
        }

        $alreadySaved = $generation->getSavedIndices();
        $indicesToProcess = $this->resolveIndicesToSave($allQuestions, $alreadySaved, $selectedIndices);

        if (empty($indicesToProcess)) {
            return collect();
        }

        $questionsToSave = [];
        foreach ($indicesToProcess as $index) {
            $questionData = $allQuestions[$index] ?? null;
            if (! is_array($questionData)) {
                continue;
            }

            if (! isset($questionData['points'])) {
                $questionData['points'] = (float) ($generation->default_grade ?? 1);
            }

            $questionsToSave[] = $questionData;
        }

        if (empty($questionsToSave)) {
            return collect();
        }

        if ($generation->usesQuestionBankFields()) {
            $programmingLanguage = $generation->programmingLanguage
                ?? ProgrammingLanguage::findOrFail($generation->programming_language_id);
            $questionTypes = QuestionType::whereIn('id', $generation->question_type_ids ?? [])->get();

            if ($questionTypes->isEmpty()) {
                throw new \Exception('أنواع الأسئلة المحددة غير متوفرة');
            }

            $lessonName = $generation->lesson_name;
            if (! $lessonName && $generation->lesson) {
                $lessonName = $generation->lesson->title;
            }

            $savedQuestions = $this->creationService->saveParsedQuestionsToBank(
                $questionsToSave,
                $programmingLanguage,
                $questionTypes,
                [
                    'user' => $generation->user,
                    'course_id' => $generation->course_id,
                    'lesson_name' => $lessonName,
                ]
            );
        } else {
            $savedQuestions = $this->saveLegacyGeneratedQuestions($generation, $questionsToSave);
        }

        $this->markIndicesAsSaved($generation, $indicesToProcess, $savedQuestions);

        return $savedQuestions;
    }

    public function saveSingleQuestion(AIQuestionGeneration $generation, int $index): ?QuestionBank
    {
        $saved = $this->saveGeneratedQuestions($generation, [$index]);

        return $saved->first();
    }

    /**
     * @param  array<int, array<string, mixed>>  $allQuestions
     * @param  array<int, int>  $alreadySaved
     * @param  array<int, int>|null  $selectedIndices
     * @return array<int, int>
     */
    private function resolveIndicesToSave(array $allQuestions, array $alreadySaved, ?array $selectedIndices): array
    {
        $candidateIndices = $selectedIndices !== null
            ? array_map('intval', $selectedIndices)
            : array_map('intval', array_keys($allQuestions));

        $indices = [];
        foreach ($candidateIndices as $index) {
            if (! array_key_exists($index, $allQuestions)) {
                continue;
            }
            if (in_array($index, $alreadySaved, true)) {
                continue;
            }
            $indices[] = $index;
        }

        return $indices;
    }

    private function markIndicesAsSaved(
        AIQuestionGeneration $generation,
        array $indicesToProcess,
        Collection $savedQuestions
    ): void {
        $savedIndices = $generation->getSavedIndices();
        $savedQuestionIds = $generation->saved_question_ids ?? [];

        foreach ($indicesToProcess as $position => $index) {
            $question = $savedQuestions->get($position);
            if (! $question instanceof QuestionBank) {
                continue;
            }

            if (! in_array($index, $savedIndices, true)) {
                $savedIndices[] = $index;
            }

            $savedQuestionIds[(string) $index] = $question->id;
        }

        $generation->update([
            'saved_indices' => array_values(array_unique($savedIndices)),
            'saved_question_ids' => $savedQuestionIds,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $questions
     */
    private function saveLegacyGeneratedQuestions(AIQuestionGeneration $generation, array $questions): Collection
    {
        $questionTypeMap = [
            'single_choice' => 1,
            'multiple_choice' => 2,
            'true_false' => 3,
            'short_answer' => 4,
            'essay' => 5,
        ];

        $savedQuestions = collect();

        DB::beginTransaction();
        try {
            foreach ($questions as $questionData) {
                $type = $questionData['type'] ?? 'single_choice';
                $questionTypeId = $questionTypeMap[$type] ?? 1;

                $question = QuestionBank::create([
                    'course_id' => $generation->course_id,
                    'question_type_id' => $questionTypeId,
                    'question_text' => $questionData['question'] ?? '',
                    'explanation' => $questionData['explanation'] ?? '',
                    'difficulty_level' => $questionData['difficulty'] ?? 'medium',
                    'default_grade' => $questionData['points'] ?? 10,
                    'is_active' => true,
                    'created_by' => $generation->user_id,
                    'tags' => ['ai_generated'],
                    'metadata' => [
                        'ai_generation_id' => $generation->id,
                        'original_type' => $type,
                    ],
                ]);

                if (isset($questionData['options']) && is_array($questionData['options'])) {
                    $correctAnswer = $questionData['correct_answer'] ?? '';
                    foreach ($questionData['options'] as $optionIndex => $optionText) {
                        $isCorrect = false;
                        if (is_array($correctAnswer)) {
                            $isCorrect = in_array($optionText, $correctAnswer);
                        } else {
                            $isCorrect = trim($optionText) === trim($correctAnswer);
                        }

                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_text' => $optionText,
                            'is_correct' => $isCorrect,
                            'option_order' => $optionIndex + 1,
                            'grade_percentage' => $isCorrect ? 100 : 0,
                        ]);
                    }
                }

                $savedQuestions->push($question);
            }

            DB::commit();

            Log::info('Questions saved successfully (legacy generation)', [
                'generation_id' => $generation->id,
                'saved_count' => $savedQuestions->count(),
            ]);

            return $savedQuestions;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving generated questions: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function resolvePrompt(AIQuestionGeneration $generation): string
    {
        if ($generation->usesQuestionBankFields()) {
            $programmingLanguage = $generation->programmingLanguage
                ?? ProgrammingLanguage::findOrFail($generation->programming_language_id);
            $questionTypes = QuestionType::whereIn('id', $generation->question_type_ids ?? [])->get();

            return $this->creationService->buildQuestionGenerationPrompt(
                $generation->source_content,
                $programmingLanguage,
                $questionTypes,
                $generation->number_of_questions,
                $generation->difficulty_level
            );
        }

        return $this->promptService->getQuestionGenerationPrompt(
            $generation->source_content,
            [
                'question_type' => $generation->question_type,
                'number_of_questions' => $generation->number_of_questions,
                'difficulty_level' => $generation->difficulty_level,
            ]
        );
    }

    /**
     * التحقق من صحة الأسئلة المولدة
     */
    public function validateGeneratedQuestions(array $questions): array
    {
        $validated = [];

        foreach ($questions as $question) {
            if (! isset($question['question']) || empty($question['question'])) {
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

    /**
     * تحليل JSON للأسئلة المولدة
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

            return $decoded;
        }

        // محاولة 2: استخراج JSON array من النص
        if (preg_match('/\[\s*\{.*?\}\s*\]/s', $cleanedResponse, $matches)) {
            $jsonString = $matches[0];
            $decoded = json_decode($jsonString, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                Log::info('JSON parsed successfully (regex array)', ['count' => count($decoded)]);

                return $decoded;
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

                return $decoded;
            }
        }

        // محاولة 4: البحث عن JSON object واحد
        if (preg_match('/\{[^{}]*"question"[^{}]*\}/s', $cleanedResponse, $matches)) {
            $decoded = json_decode('['.$matches[0].']', true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                Log::info('JSON parsed successfully (single object)', ['count' => count($decoded)]);

                return $decoded;
            }
        }

        // محاولة 5: تحليل نص غير JSON (fallback)
        $questions = $this->parseTextBasedQuestions($cleanedResponse);
        if (! empty($questions)) {
            Log::info('Questions parsed from text format', ['count' => count($questions)]);

            return $questions;
        }

        Log::warning('Failed to parse questions from response', [
            'json_error' => json_last_error_msg(),
            'response' => substr($cleanedResponse, 0, 1000),
        ]);

        return [];
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
