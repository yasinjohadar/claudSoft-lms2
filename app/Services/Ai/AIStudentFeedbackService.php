<?php

namespace App\Services\AI;

use App\Models\AIStudentFeedback;
use App\Models\AIModel;
use App\Models\User;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\Log;

class AIStudentFeedbackService
{
    public function __construct(
        private AIModelService $modelService,
        private AIPromptService $promptService
    ) {}

    /**
     * توليد ملاحظات عامة للطالب
     */
    public function generateFeedback(User $student, ?QuizAttempt $attempt = null, ?AIModel $model = null, array $options = []): AIStudentFeedback
    {
        // زيادة وقت التنفيذ إلى 3 دقائق للطلبات الطويلة
        set_time_limit(180);
        
        if (!$model) {
            $model = $this->modelService->getBestModelFor('question_solving');
        }

        if (!$model) {
            throw new \Exception('لا يوجد موديل AI متاح لتوليد الملاحظات');
        }

        try {
            $data = $this->gatherStudentData($student, $attempt);
            
            // إضافة التعليمات المخصصة إذا وجدت
            if (!empty($options['custom_prompt'])) {
                $data['custom_instructions'] = $options['custom_prompt'];
            }
            
            $prompt = $this->promptService->getStudentFeedbackPrompt($student, $data);
            
            $provider = AIProviderFactory::create($model);
            $response = $provider->generateText($prompt, [
                'max_tokens' => $model->max_tokens,
                'temperature' => 0.6,
            ]);

            if (empty($response)) {
                throw new \Exception('لم يتم الحصول على رد من الذكاء الاصطناعي');
            }

            $tokensUsed = $provider->estimateTokens($prompt . $response);
            $cost = $model->getCost($tokensUsed);

            // تحليل الاستجابة
            $parsed = $this->parseFeedbackResponse($response);

            // تحديد نوع الملاحظات
            $feedbackType = $options['feedback_type'] ?? ($attempt ? 'performance' : 'general');

            $feedback = AIStudentFeedback::create([
                'student_id' => $student->id,
                'quiz_attempt_id' => $attempt?->id,
                'feedback_type' => $feedbackType,
                'feedback_text' => $parsed['feedback'] ?? $response,
                'suggestions' => $parsed['suggestions'] ?? [],
                'ai_model_id' => $model->id,
                'tokens_used' => $tokensUsed,
                'cost' => $cost,
            ]);

            return $feedback;
        } catch (\Exception $e) {
            Log::error('Error generating student feedback: ' . $e->getMessage(), [
                'student_id' => $student->id,
                'attempt_id' => $attempt?->id,
            ]);
            throw $e;
        }
    }

    /**
     * توليد ملاحظات الأداء
     */
    public function generatePerformanceFeedback(User $student, array $quizResults, ?AIModel $model = null): AIStudentFeedback
    {
        if (!$model) {
            $model = $this->modelService->getBestModelFor('question_solving');
        }

        if (!$model) {
            throw new \Exception('لا يوجد موديل AI متاح لتوليد الملاحظات');
        }

        try {
            $data = [
                'quiz_results' => $quizResults,
                'type' => 'performance',
            ];
            
            $prompt = $this->promptService->getStudentFeedbackPrompt($student, $data);
            
            $provider = AIProviderFactory::create($model);
            $response = $provider->generateText($prompt, [
                'max_tokens' => $model->max_tokens,
                'temperature' => 0.6,
            ]);

            $tokensUsed = $provider->estimateTokens($prompt . $response);
            $cost = $model->getCost($tokensUsed);

            $parsed = $this->parseFeedbackResponse($response);

            $feedback = AIStudentFeedback::create([
                'student_id' => $student->id,
                'feedback_type' => 'performance',
                'feedback_text' => $parsed['feedback'] ?? $response,
                'suggestions' => $parsed['suggestions'] ?? [],
                'ai_model_id' => $model->id,
                'tokens_used' => $tokensUsed,
                'cost' => $cost,
            ]);

            return $feedback;
        } catch (\Exception $e) {
            Log::error('Error generating performance feedback: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * توليد اقتراحات التحسين
     */
    public function generateImprovementSuggestions(User $student, array $weakAreas, ?AIModel $model = null): AIStudentFeedback
    {
        if (!$model) {
            $model = $this->modelService->getBestModelFor('question_solving');
        }

        if (!$model) {
            throw new \Exception('لا يوجد موديل AI متاح لتوليد الاقتراحات');
        }

        try {
            $data = [
                'weak_areas' => $weakAreas,
                'type' => 'improvement',
            ];
            
            $prompt = $this->promptService->getStudentFeedbackPrompt($student, $data);
            
            $provider = AIProviderFactory::create($model);
            $response = $provider->generateText($prompt, [
                'max_tokens' => $model->max_tokens,
                'temperature' => 0.6,
            ]);

            $tokensUsed = $provider->estimateTokens($prompt . $response);
            $cost = $model->getCost($tokensUsed);

            $parsed = $this->parseFeedbackResponse($response);

            $feedback = AIStudentFeedback::create([
                'student_id' => $student->id,
                'feedback_type' => 'improvement',
                'feedback_text' => $parsed['feedback'] ?? $response,
                'suggestions' => $parsed['suggestions'] ?? [],
                'ai_model_id' => $model->id,
                'tokens_used' => $tokensUsed,
                'cost' => $cost,
            ]);

            return $feedback;
        } catch (\Exception $e) {
            Log::error('Error generating improvement suggestions: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * جمع بيانات الطالب
     */
    private function gatherStudentData(User $student, ?QuizAttempt $attempt = null): array
    {
        $data = [
            'student_name' => $student->name,
            'student_email' => $student->email,
            'type' => $attempt ? 'performance' : 'general',
        ];

        if ($attempt) {
            // بيانات اختبار محدد
            $data['quiz_title'] = $attempt->quiz->title ?? '';
            $data['score'] = $attempt->score ?? 0;
            $data['max_score'] = $attempt->max_score ?? 0;
            $data['percentage'] = $attempt->percentage ?? 0;
            $data['correct_answers'] = $attempt->questions_correct ?? 0;
            $data['total_questions'] = $attempt->quiz->questions()->count() ?? 0;
            $data['attempt_date'] = $attempt->created_at?->format('Y-m-d H:i');
        } else {
            // بيانات عامة شاملة
            $quizAttempts = \App\Models\QuizAttempt::where('student_id', $student->id)
                                                    ->where('status', 'completed')
                                                    ->get();
            
            $data['total_quizzes'] = $quizAttempts->count();
            $data['completed_quizzes'] = $quizAttempts->count();
            $data['average_score'] = round($quizAttempts->avg('percentage') ?? 0, 2);
            
            // أفضل وأسوأ أداء
            $bestAttempt = $quizAttempts->sortByDesc('percentage')->first();
            $worstAttempt = $quizAttempts->sortBy('percentage')->first();
            
            if ($bestAttempt) {
                $data['best_score'] = $bestAttempt->percentage;
                $data['best_quiz'] = $bestAttempt->quiz->title ?? 'غير محدد';
            }
            
            if ($worstAttempt) {
                $data['worst_score'] = $worstAttempt->percentage;
                $data['worst_quiz'] = $worstAttempt->quiz->title ?? 'غير محدد';
            }
            
            // إحصائيات إضافية
            $data['total_courses'] = $student->enrollments()->count() ?? 0;
            $data['last_activity'] = $student->last_login_at?->format('Y-m-d H:i') ?? 'غير متاح';
        }

        return $data;
    }

    /**
     * تحليل استجابة AI
     */
    private function parseFeedbackResponse(string $response): array
    {
        // محاولة استخراج JSON
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');

        if ($jsonStart !== false && $jsonEnd !== false) {
            $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
            $decoded = json_decode($jsonString, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return [
                    'feedback' => $decoded['feedback'] ?? $decoded['text'] ?? $response,
                    'suggestions' => $decoded['suggestions'] ?? $decoded['recommendations'] ?? [],
                ];
            }
        }

        // إذا فشل، استخراج الاقتراحات من النص
        $suggestions = $this->extractSuggestions($response);

        return [
            'feedback' => $response,
            'suggestions' => $suggestions,
        ];
    }

    /**
     * استخراج الاقتراحات من النص
     */
    private function extractSuggestions(string $text): array
    {
        $suggestions = [];
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match('/^[-•*]\s*(.+)$/', $line, $matches) ||
                preg_match('/^\d+[\.\)]\s*(.+)$/', $line, $matches)) {
                $suggestions[] = $matches[1];
            }
        }

        return $suggestions;
    }
}

