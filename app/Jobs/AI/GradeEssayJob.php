<?php

namespace App\Jobs\AI;

use App\Services\AI\EssayGradingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GradeEssayJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $responseId,
        public int $questionId,
        public string $studentAnswer,
        public ?string $providerName = null
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(EssayGradingService $essayGradingService): void
    {
        try {
            Log::info('Starting essay grading job', [
                'response_id' => $this->responseId,
                'question_id' => $this->questionId,
            ]);

            $result = $essayGradingService->gradeEssay(
                $this->responseId,
                $this->questionId,
                $this->studentAnswer,
                $this->providerName
            );

            Log::info('Essay grading completed', [
                'response_id' => $this->responseId,
                'ai_request_id' => $result['ai_request_id'] ?? null,
                'score' => $result['grading']['total_score'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Essay grading job failed', [
                'error' => $e->getMessage(),
                'response_id' => $this->responseId,
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Essay grading job failed permanently', [
            'error' => $exception->getMessage(),
            'response_id' => $this->responseId,
        ]);
    }
}
