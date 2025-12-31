<?php

namespace App\Services\AI;

use App\Models\QuestionBank;
use App\Models\QuestionType;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\AIRequest;
use App\Services\AI\AIManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class QuestionGenerationService
{
    protected AIManager $aiManager;
    protected PromptService $promptService;

    public function __construct(AIManager $aiManager, PromptService $promptService)
    {
        $this->aiManager = $aiManager;
        $this->promptService = $promptService;
    }

    /**
     * Generate questions for a course/lesson
     *
     * @param int|null $courseId
     * @param int|null $lessonId
     * @param int $count
     * @param string $difficulty
     * @param array $questionTypes
     * @param string|null $providerName
     * @return array Generated questions
     */
    public function generateQuestions(
        ?int $courseId = null,
        ?int $lessonId = null,
        int $count = 5,
        string $difficulty = 'medium',
        array $questionTypes = [],
        ?string $providerName = null
    ): array {
        try {
            // Get context
            $context = $this->getContext($courseId, $lessonId);
            
            // Get question types
            if (empty($questionTypes)) {
                $questionTypes = QuestionType::active()->pluck('name')->toArray();
            }

            // Create AI request record
            $aiRequest = AIRequest::create([
                'provider_id' => $this->getProviderId($providerName),
                'user_id' => auth()->id(),
                'request_type' => 'question_generation',
                'input_data' => [
                    'course_id' => $courseId,
                    'lesson_id' => $lessonId,
                    'count' => $count,
                    'difficulty' => $difficulty,
                    'question_types' => $questionTypes,
                    'context' => $context,
                ],
                'status' => 'processing',
            ]);

            $startTime = microtime(true);

            // Generate questions for each type
            $generatedQuestions = [];
            $questionsPerType = ceil($count / count($questionTypes));

            foreach ($questionTypes as $questionType) {
                $prompt = $this->promptService->getQuestionGenerationPrompt(
                    $context['topic'] ?? 'عام',
                    $context['content'] ?? '',
                    $questionsPerType,
                    $questionType,
                    $difficulty
                );

                $provider = $providerName ? $this->aiManager->provider($providerName) : $this->aiManager->getDefaultProvider();
                
                $response = $provider->generateText($prompt, [
                    'temperature' => 0.8,
                    'max_tokens' => 3000,
                ]);

                $questions = $this->parseQuestionsResponse($response['content'], $questionType);
                $generatedQuestions = array_merge($generatedQuestions, $questions);

                // Update request with partial response
                $aiRequest->update([
                    'response_data' => ['questions' => $generatedQuestions],
                ]);
            }

            // Limit to requested count
            $generatedQuestions = array_slice($generatedQuestions, 0, $count);

            $responseTime = (microtime(true) - $startTime) * 1000;

            // Update AI request
            $aiRequest->markAsCompleted(
                ['questions' => $generatedQuestions],
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
                'questions' => $generatedQuestions,
                'ai_request_id' => $aiRequest->id,
            ];
        } catch (\Exception $e) {
            Log::error('Question generation failed', [
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
     * Generate questions from content
     *
     * @param string $content
     * @param string $questionType
     * @param int $count
     * @param string|null $providerName
     * @return array
     */
    public function generateFromContent(
        string $content,
        string $questionType,
        int $count = 5,
        ?string $providerName = null
    ): array {
        $prompt = $this->promptService->getQuestionGenerationPrompt(
            'من المحتوى المقدم',
            $content,
            $count,
            $questionType,
            'medium'
        );

        $provider = $providerName ? $this->aiManager->provider($providerName) : $this->aiManager->getDefaultProvider();
        $response = $provider->generateText($prompt);

        return $this->parseQuestionsResponse($response['content'], $questionType);
    }

    /**
     * Generate complete quiz
     *
     * @param int $courseId
     * @param array $requirements
     * @param string|null $providerName
     * @return array
     */
    public function generateQuiz(int $courseId, array $requirements, ?string $providerName = null): array
    {
        // This will be handled by QuizGenerationService
        // But we can use this for question generation part
        $context = $this->getContext($courseId);
        
        return $this->generateQuestions(
            $courseId,
            null,
            $requirements['count'] ?? 10,
            $requirements['difficulty'] ?? 'medium',
            $requirements['question_types'] ?? [],
            $providerName
        );
    }

    /**
     * Enhance existing question
     *
     * @param int $questionId
     * @param string|null $providerName
     * @return array
     */
    public function enhanceQuestion(int $questionId, ?string $providerName = null): array
    {
        $question = QuestionBank::findOrFail($questionId);
        
        $prompt = "قم بتحسين السؤال التالي وجعله أكثر وضوحاً وفعالية:\n\n";
        $prompt .= "السؤال الحالي: {$question->question_text}\n\n";
        $prompt .= "يرجى تقديم:\n";
        $prompt .= "1. نسخة محسنة من السؤال\n";
        $prompt .= "2. اقتراحات للتحسين\n";
        $prompt .= "3. خيارات إضافية إذا كان مناسباً\n";

        $provider = $providerName ? $this->aiManager->provider($providerName) : $this->aiManager->getDefaultProvider();
        $response = $provider->generateText($prompt);

        return [
            'enhanced_question' => $response['content'],
            'suggestions' => $this->extractSuggestions($response['content']),
        ];
    }

    /**
     * Save generated questions to database
     *
     * @param array $questions
     * @param int|null $courseId
     * @param int|null $aiRequestId
     * @return array Created question IDs
     */
    public function saveQuestions(array $questions, ?int $courseId = null, ?int $aiRequestId = null): array
    {
        $createdIds = [];

        DB::transaction(function () use ($questions, $courseId, $aiRequestId, &$createdIds) {
            foreach ($questions as $questionData) {
                $questionType = QuestionType::where('name', $questionData['question_type'])->first();
                
                if (!$questionType) {
                    continue;
                }

                $question = QuestionBank::create([
                    'course_id' => $courseId,
                    'question_type_id' => $questionType->id,
                    'question_text' => $questionData['question_text'],
                    'explanation' => $questionData['explanation'] ?? null,
                    'difficulty_level' => $questionData['difficulty'] ?? 'medium',
                    'default_grade' => 1.00,
                    'metadata' => [
                        'options' => $questionData['options'] ?? [],
                        'tags' => $questionData['tags'] ?? [],
                    ],
                    'is_active' => true,
                    'ai_generated' => true,
                    'ai_request_id' => $aiRequestId,
                    'ai_metadata' => $questionData,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);

                // Create options if multiple choice
                if (in_array($questionData['question_type'], ['multiple_choice_single', 'multiple_choice_multiple'])) {
                    $this->createQuestionOptions($question, $questionData['options'] ?? []);
                }

                $createdIds[] = $question->id;
            }
        });

        return $createdIds;
    }

    /**
     * Get context for question generation
     */
    protected function getContext(?int $courseId = null, ?int $lessonId = null): array
    {
        $context = [
            'topic' => 'عام',
            'content' => '',
        ];

        if ($lessonId) {
            $lesson = Lesson::with('course')->find($lessonId);
            if ($lesson) {
                $context['topic'] = $lesson->title;
                $context['content'] = $lesson->content ?? '';
                $context['course'] = $lesson->course->title ?? '';
            }
        } elseif ($courseId) {
            $course = Course::find($courseId);
            if ($course) {
                $context['topic'] = $course->title;
                $context['content'] = $course->description ?? '';
            }
        }

        return $context;
    }

    /**
     * Parse AI response to extract questions
     */
    protected function parseQuestionsResponse(string $response, string $questionType): array
    {
        // Try to parse as JSON first
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');
        
        if ($jsonStart !== false && $jsonEnd !== false) {
            $json = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
            $data = json_decode($json, true);
            
            if (isset($data['questions']) && is_array($data['questions'])) {
                return $data['questions'];
            }
        }

        // Fallback: try to extract questions from text
        return $this->extractQuestionsFromText($response, $questionType);
    }

    /**
     * Extract questions from text (fallback)
     */
    protected function extractQuestionsFromText(string $text, string $questionType): array
    {
        // Simple extraction - can be improved
        $questions = [];
        $lines = explode("\n", $text);
        
        $currentQuestion = null;
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Check if line looks like a question
            if (preg_match('/^[0-9]+[\.\)]\s*(.+)/', $line, $matches) || 
                preg_match('/^سؤال[:\s]+(.+)/', $line, $matches)) {
                if ($currentQuestion) {
                    $questions[] = $currentQuestion;
                }
                $currentQuestion = [
                    'question_text' => $matches[1],
                    'question_type' => $questionType,
                    'options' => [],
                ];
            } elseif ($currentQuestion && preg_match('/^[أ-ي]?[\.\)]\s*(.+)/', $line, $matches)) {
                $currentQuestion['options'][] = [
                    'text' => $matches[1],
                    'is_correct' => false,
                ];
            }
        }

        if ($currentQuestion) {
            $questions[] = $currentQuestion;
        }

        return $questions;
    }

    /**
     * Create question options
     */
    protected function createQuestionOptions(QuestionBank $question, array $options): void
    {
        foreach ($options as $index => $option) {
            $question->options()->create([
                'option_text' => $option['text'],
                'is_correct' => $option['is_correct'] ?? false,
                'order' => $index + 1,
            ]);
        }
    }

    /**
     * Extract suggestions from enhancement response
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

