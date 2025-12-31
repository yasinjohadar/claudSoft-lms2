<?php

namespace App\Services\AI;

use App\Models\QuizResponse;
use App\Models\QuestionBank;
use App\Models\EssayGradingRubric;
use App\Models\AIRequest;
use App\Services\AI\AIManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EssayGradingService
{
    protected AIManager $aiManager;
    protected PromptService $promptService;

    public function __construct(AIManager $aiManager, PromptService $promptService)
    {
        $this->aiManager = $aiManager;
        $this->promptService = $promptService;
    }

    /**
     * Grade essay response
     *
     * @param int $responseId
     * @param int $questionId
     * @param string $studentAnswer
     * @param string|null $providerName
     * @return array
     */
    public function gradeEssay(
        int $responseId,
        int $questionId,
        string $studentAnswer,
        ?string $providerName = null
    ): array {
        try {
            $question = QuestionBank::findOrFail($questionId);
            $rubric = EssayGradingRubric::where('question_id', $questionId)->first();

            // Use default criteria if no rubric exists
            $criteria = $rubric ? $rubric->criteria : EssayGradingRubric::getDefaultCriteria();
            $maxScore = $rubric ? $rubric->max_score : 10.00;

            // Create AI request record
            $aiRequest = AIRequest::create([
                'provider_id' => $this->getProviderId($providerName),
                'user_id' => auth()->id(),
                'request_type' => 'essay_grading',
                'input_data' => [
                    'response_id' => $responseId,
                    'question_id' => $questionId,
                    'question_text' => $question->question_text,
                    'student_answer' => $studentAnswer,
                    'criteria' => $criteria,
                    'max_score' => $maxScore,
                ],
                'status' => 'processing',
            ]);

            $startTime = microtime(true);

            // Get custom prompt if exists
            $customPrompt = $rubric?->ai_prompt;
            $instructions = $rubric?->instructions;

            $prompt = $this->promptService->getEssayGradingPrompt(
                $question->question_text,
                $criteria,
                $studentAnswer,
                $maxScore
            );

            // Add custom instructions if available
            if ($instructions) {
                $prompt .= "\n\nتعليمات إضافية:\n{$instructions}";
            }

            $provider = $providerName ? $this->aiManager->provider($providerName) : $this->aiManager->getDefaultProvider();
            $response = $provider->generateText($prompt, [
                'temperature' => 0.3, // Lower temperature for more consistent grading
                'max_tokens' => 2000,
            ]);

            $gradingResult = $this->parseGradingResponse($response['content'], $maxScore);

            $responseTime = (microtime(true) - $startTime) * 1000;

            // Update AI request
            $aiRequest->markAsCompleted(
                ['grading' => $gradingResult],
                $response['tokens_used']['total'] ?? 0,
                $provider->calculateCost(
                    $response['tokens_used']['input'] ?? 0,
                    $response['tokens_used']['output'] ?? 0
                ),
                $response['model_used'] ?? null
            );
            $aiRequest->update(['response_time_ms' => (int)$responseTime]);

            // Update quiz response
            $quizResponse = QuizResponse::findOrFail($responseId);
            $quizResponse->update([
                'score_obtained' => $gradingResult['total_score'],
                'ai_graded' => true,
                'ai_request_id' => $aiRequest->id,
                'ai_feedback' => [
                    'overall_feedback' => $gradingResult['overall_feedback'] ?? '',
                    'strengths' => $gradingResult['strengths'] ?? [],
                    'improvements' => $gradingResult['improvements'] ?? [],
                ],
                'ai_grading_details' => $gradingResult,
                'graded_at' => now(),
            ]);

            return [
                'success' => true,
                'grading' => $gradingResult,
                'ai_request_id' => $aiRequest->id,
            ];
        } catch (\Exception $e) {
            Log::error('Essay grading failed', [
                'error' => $e->getMessage(),
                'response_id' => $responseId,
                'trace' => $e->getTraceAsString(),
            ]);

            if (isset($aiRequest)) {
                $aiRequest->markAsFailed($e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * Provide feedback for graded essay
     *
     * @param int $responseId
     * @param array $gradedResponse
     * @return array
     */
    public function provideFeedback(int $responseId, array $gradedResponse): array
    {
        $quizResponse = QuizResponse::findOrFail($responseId);
        
        return [
            'score' => $gradedResponse['total_score'] ?? 0,
            'max_score' => $gradedResponse['max_score'] ?? 10,
            'percentage' => $gradedResponse['percentage'] ?? 0,
            'feedback' => $gradedResponse['overall_feedback'] ?? '',
            'strengths' => $gradedResponse['strengths'] ?? [],
            'improvements' => $gradedResponse['improvements'] ?? [],
            'criteria_scores' => $gradedResponse['criteria_scores'] ?? [],
        ];
    }

    /**
     * Compare answer with rubric
     *
     * @param string $answer
     * @param array $rubric
     * @return array
     */
    public function compareWithRubric(string $answer, array $rubric): array
    {
        // This can be enhanced to do more detailed comparison
        $prompt = "قم بمقارنة الإجابة التالية مع معايير التصحيح:\n\n";
        $prompt .= "الإجابة: {$answer}\n\n";
        $prompt .= "المعايير:\n";
        
        foreach ($rubric as $key => $criterion) {
            $prompt .= "- {$key}: {$criterion['description']}\n";
        }

        $provider = $this->aiManager->getDefaultProvider();
        $response = $provider->generateText($prompt);

        return [
            'comparison' => $response['content'],
            'matches' => $this->extractMatches($response['content']),
        ];
    }

    /**
     * Parse grading response from AI
     */
    protected function parseGradingResponse(string $response, float $maxScore): array
    {
        // Try to parse as JSON first
        $jsonStart = strpos($response, '{');
        $jsonEnd = strrpos($response, '}');
        
        if ($jsonStart !== false && $jsonEnd !== false) {
            $json = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
            $data = json_decode($json, true);
            
            if ($data && isset($data['total_score'])) {
                // Ensure max_score is set
                $data['max_score'] = $maxScore;
                $data['percentage'] = ($data['total_score'] / $maxScore) * 100;
                return $data;
            }
        }

        // Fallback: extract scores from text
        return $this->extractGradingFromText($response, $maxScore);
    }

    /**
     * Extract grading from text (fallback)
     */
    protected function extractGradingFromText(string $response, float $maxScore): array
    {
        $totalScore = $maxScore * 0.8; // Default to 80% if can't parse
        
        // Try to extract score
        if (preg_match('/(?:النتيجة|الدرجة|Score)[:\s]+([0-9.]+)/i', $response, $matches)) {
            $totalScore = min((float)$matches[1], $maxScore);
        }

        return [
            'total_score' => $totalScore,
            'max_score' => $maxScore,
            'percentage' => ($totalScore / $maxScore) * 100,
            'overall_feedback' => $this->extractFeedback($response),
            'strengths' => [],
            'improvements' => [],
            'criteria_scores' => [],
            'is_passing' => $totalScore >= ($maxScore * 0.6),
        ];
    }

    /**
     * Extract feedback from response
     */
    protected function extractFeedback(string $response): string
    {
        // Try to find feedback section
        if (preg_match('/(?:ملاحظات|تغذية راجعة|Feedback)[:\s]+(.+?)(?:\n\n|\n[أ-ي]+[:\s]|$)/is', $response, $matches)) {
            return trim($matches[1]);
        }

        // Return first paragraph as feedback
        $lines = explode("\n", $response);
        foreach ($lines as $line) {
            $line = trim($line);
            if (strlen($line) > 50) {
                return $line;
            }
        }

        return 'تم التصحيح بنجاح';
    }

    /**
     * Extract matches from comparison
     */
    protected function extractMatches(string $response): array
    {
        $matches = [];
        $lines = explode("\n", $response);
        
        foreach ($lines as $line) {
            if (preg_match('/^[✓✔✅]\s*(.+)/', trim($line), $m)) {
                $matches[] = $m[1];
            }
        }

        return $matches;
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

