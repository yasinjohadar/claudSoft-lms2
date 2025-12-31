<?php

namespace App\Services\AI;

use App\Models\Quiz;
use App\Models\Course;
use App\Models\QuestionBank;
use App\Models\AIRequest;
use App\Services\AI\AIManager;
use App\Services\AI\QuestionGenerationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuizGenerationService
{
    protected AIManager $aiManager;
    protected PromptService $promptService;
    protected QuestionGenerationService $questionGenerator;

    public function __construct(
        AIManager $aiManager,
        PromptService $promptService,
        QuestionGenerationService $questionGenerator
    ) {
        $this->aiManager = $aiManager;
        $this->promptService = $promptService;
        $this->questionGenerator = $questionGenerator;
    }

    /**
     * Generate complete quiz
     *
     * @param int $courseId
     * @param array $specifications
     * @param string|null $providerName
     * @return array
     */
    public function generateCompleteQuiz(int $courseId, array $specifications, ?string $providerName = null): array
    {
        try {
            $course = Course::findOrFail($courseId);

            // Create AI request record
            $aiRequest = AIRequest::create([
                'provider_id' => $this->getProviderId($providerName),
                'user_id' => auth()->id(),
                'request_type' => 'quiz_generation',
                'input_data' => [
                    'course_id' => $courseId,
                    'specifications' => $specifications,
                ],
                'status' => 'processing',
            ]);

            $startTime = microtime(true);

            // Generate quiz structure using AI
            $prompt = $this->promptService->getQuizGenerationPrompt(
                $specifications['total_questions'] ?? 10,
                $specifications['question_types'] ?? ['multiple_choice_single'],
                $specifications['difficulty'] ?? 'medium',
                $course->title,
                $course->description ?? '',
                $specifications['time_limit'] ?? 60
            );

            $provider = $providerName ? $this->aiManager->provider($providerName) : $this->aiManager->getDefaultProvider();
            $response = $provider->generateText($prompt, [
                'temperature' => 0.7,
                'max_tokens' => 4000,
            ]);

            $quizData = $this->parseQuizResponse($response['content']);

            // Generate questions if not included in response
            if (empty($quizData['questions']) || count($quizData['questions']) < ($specifications['total_questions'] ?? 10)) {
                $questions = $this->questionGenerator->generateQuestions(
                    $courseId,
                    null,
                    $specifications['total_questions'] ?? 10,
                    $specifications['difficulty'] ?? 'medium',
                    $specifications['question_types'] ?? [],
                    $providerName
                );

                if (isset($questions['questions'])) {
                    $quizData['questions'] = $questions['questions'];
                }
            }

            $responseTime = (microtime(true) - $startTime) * 1000;

            // Update AI request
            $aiRequest->markAsCompleted(
                ['quiz' => $quizData],
                $response['tokens_used']['total'] ?? 0,
                $provider->calculateCost(
                    $response['tokens_used']['input'] ?? 0,
                    $response['tokens_used']['output'] ?? 0
                ),
                $response['model_used'] ?? null
            );
            $aiRequest->update(['response_time_ms' => (int)$responseTime]);

            return [
                'success' => true,
                'quiz' => $quizData,
                'ai_request_id' => $aiRequest->id,
            ];
        } catch (\Exception $e) {
            Log::error('Quiz generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (isset($aiRequest)) {
                $aiRequest->markAsFailed($e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * Generate questions for existing quiz
     *
     * @param int $quizId
     * @param array $requirements
     * @param string|null $providerName
     * @return array
     */
    public function generateQuestionsForQuiz(int $quizId, array $requirements, ?string $providerName = null): array
    {
        $quiz = Quiz::with('course')->findOrFail($quizId);

        $questions = $this->questionGenerator->generateQuestions(
            $quiz->course_id,
            $quiz->lesson_id,
            $requirements['count'] ?? 10,
            $requirements['difficulty'] ?? 'medium',
            $requirements['question_types'] ?? [],
            $providerName
        );

        return $questions;
    }

    /**
     * Balance quiz difficulty
     *
     * @param int $quizId
     * @param string|null $providerName
     * @return array
     */
    public function balanceQuiz(int $quizId, ?string $providerName = null): array
    {
        $quiz = Quiz::with('questions.question')->findOrFail($quizId);

        $questions = $quiz->questions->map(function ($quizQuestion) {
            return [
                'id' => $quizQuestion->question_id,
                'text' => $quizQuestion->question->question_text ?? '',
                'difficulty' => $quizQuestion->question->difficulty_level ?? 'medium',
            ];
        })->toArray();

        $prompt = "قم بتحليل وتوازن صعوبة الاختبار التالي:\n\n";
        $prompt .= "الأسئلة الحالية:\n";
        foreach ($questions as $index => $q) {
            $prompt .= ($index + 1) . ". {$q['text']} (الصعوبة: {$q['difficulty']})\n";
        }
        $prompt .= "\nيرجى:\n";
        $prompt .= "1. تحليل توزيع الصعوبة\n";
        $prompt .= "2. اقتراح تعديلات لتحقيق التوازن\n";
        $prompt .= "3. اقتراح إضافة أو حذف أسئلة إذا لزم الأمر\n";

        $provider = $providerName ? $this->aiManager->provider($providerName) : $this->aiManager->getDefaultProvider();
        $response = $provider->generateText($prompt);

        return [
            'analysis' => $response['content'],
            'suggestions' => $this->extractSuggestions($response['content']),
        ];
    }

    /**
     * Save generated quiz to database
     *
     * @param array $quizData
     * @param int $courseId
     * @param int|null $lessonId
     * @param int|null $aiRequestId
     * @return Quiz
     */
    public function saveQuiz(array $quizData, int $courseId, ?int $lessonId = null, ?int $aiRequestId = null): Quiz
    {
        return DB::transaction(function () use ($quizData, $courseId, $lessonId, $aiRequestId) {
            // Create quiz
            $quiz = Quiz::create([
                'course_id' => $courseId,
                'lesson_id' => $lessonId,
                'title' => $quizData['quiz_title'] ?? 'اختبار جديد',
                'description' => $quizData['quiz_description'] ?? '',
                'instructions' => $quizData['instructions'] ?? '',
                'quiz_type' => 'graded',
                'max_score' => $quizData['total_points'] ?? 10,
                'time_limit' => $quizData['estimated_time'] ?? 60,
                'is_published' => false,
                'is_visible' => false,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            // Save questions and link to quiz
            if (isset($quizData['questions']) && is_array($quizData['questions'])) {
                $questionIds = $this->questionGenerator->saveQuestions(
                    $quizData['questions'],
                    $courseId,
                    $aiRequestId
                );

                // Link questions to quiz
                foreach ($questionIds as $index => $questionId) {
                    $quiz->questions()->create([
                        'question_id' => $questionId,
                        'question_order' => $index + 1,
                        'question_grade' => 1.00,
                    ]);
                }
            }

            return $quiz;
        });
    }

    /**
     * Parse AI response to extract quiz data
     */
    protected function parseQuizResponse(string $response): array
    {
        // Try to parse as JSON first
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');
        
        if ($jsonStart !== false && $jsonEnd !== false) {
            $json = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
            $data = json_decode($json, true);
            
            if ($data && isset($data['quiz_title'])) {
                return $data;
            }
        }

        // Fallback: extract basic info from text
        return [
            'quiz_title' => $this->extractTitle($response),
            'quiz_description' => $this->extractDescription($response),
            'questions' => [],
        ];
    }

    /**
     * Extract title from response
     */
    protected function extractTitle(string $response): string
    {
        if (preg_match('/عنوان[:\s]+(.+)/i', $response, $matches)) {
            return trim($matches[1]);
        }
        return 'اختبار جديد';
    }

    /**
     * Extract description from response
     */
    protected function extractDescription(string $response): string
    {
        if (preg_match('/وصف[:\s]+(.+?)(?:\n|$)/i', $response, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }

    /**
     * Extract suggestions from response
     */
    protected function extractSuggestions(string $response): array
    {
        $suggestions = [];
        $lines = explode("\n", $response);
        
        foreach ($lines as $line) {
            if (preg_match('/^[0-9]+[\.\)]\s*(.+)/', trim($line), $matches)) {
                $suggestions[] = $matches[1];
            }
        }

        return $suggestions;
    }

    /**
     * Get provider ID
     */
    protected function getProviderId(?string $providerName): ?int
    {
        if (!$providerName) {
            $provider = \App\Models\AIProvider::where('is_default', true)->first();
            return $provider?->id;
        }

        $provider = \App\Models\AIProvider::where('name', $providerName)->first();
        return $provider?->id;
    }
}

