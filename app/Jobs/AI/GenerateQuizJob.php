<?php

namespace App\Jobs\AI;

use App\Services\AI\QuizGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateQuizJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // 10 minutes
    public int $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $courseId,
        public array $specifications,
        public ?string $providerName = null,
        public ?int $userId = null
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(QuizGenerationService $quizGenerator): void
    {
        try {
            Log::info('Starting quiz generation job', [
                'course_id' => $this->courseId,
                'specifications' => $this->specifications,
            ]);

            $result = $quizGenerator->generateCompleteQuiz(
                $this->courseId,
                $this->specifications,
                $this->providerName
            );

            Log::info('Quiz generation completed', [
                'ai_request_id' => $result['ai_request_id'] ?? null,
                'quiz_title' => $result['quiz']['quiz_title'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Quiz generation job failed', [
                'error' => $e->getMessage(),
                'course_id' => $this->courseId,
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
        Log::error('Quiz generation job failed permanently', [
            'error' => $exception->getMessage(),
            'course_id' => $this->courseId,
        ]);
    }
}
