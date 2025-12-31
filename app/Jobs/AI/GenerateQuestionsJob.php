<?php

namespace App\Jobs\AI;

use App\Services\AI\QuestionGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateQuestionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?int $courseId,
        public ?int $lessonId,
        public int $count,
        public string $difficulty,
        public array $questionTypes,
        public ?string $providerName = null,
        public ?int $userId = null
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(QuestionGenerationService $questionGenerator): void
    {
        try {
            Log::info('Starting question generation job', [
                'course_id' => $this->courseId,
                'lesson_id' => $this->lessonId,
                'count' => $this->count,
            ]);

            $result = $questionGenerator->generateQuestions(
                $this->courseId,
                $this->lessonId,
                $this->count,
                $this->difficulty,
                $this->questionTypes,
                $this->providerName
            );

            Log::info('Question generation completed', [
                'ai_request_id' => $result['ai_request_id'] ?? null,
                'questions_count' => count($result['questions'] ?? []),
            ]);
        } catch (\Exception $e) {
            Log::error('Question generation job failed', [
                'error' => $e->getMessage(),
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
        Log::error('Question generation job failed permanently', [
            'error' => $exception->getMessage(),
            'course_id' => $this->courseId,
            'lesson_id' => $this->lessonId,
        ]);
    }
}
