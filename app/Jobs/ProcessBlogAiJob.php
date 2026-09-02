<?php

namespace App\Jobs;

use App\Models\BlogAiGeneration;
use App\Models\BlogAiSection;
use App\Services\AiNew\BlogAiPipelineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessBlogAiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Safe to retry: the staged pipeline resumes from persisted sections instead
     * of regenerating the whole article.
     */
    public int $tries = 3;

    /** Must stay below the database queue retry_after (3700s) to avoid double-running. */
    public int $timeout = 3600;

    /** @var list<int> */
    public array $backoff = [30, 120];

    public function __construct(public int $generationId) {}

    public function handle(BlogAiPipelineService $pipeline): void
    {
        $generation = BlogAiGeneration::query()->find($this->generationId);
        if (! $generation) {
            return;
        }

        $pipeline->run($generation);
    }

    public function failed(?Throwable $exception): void
    {
        $generation = BlogAiGeneration::query()->find($this->generationId);
        if (! $generation || in_array($generation->status, [
            BlogAiGeneration::STATUS_COMPLETED,
            BlogAiGeneration::STATUS_PAUSED,
        ], true)) {
            return;
        }

        $message = $exception?->getMessage() ?: 'فشل غير معروف في طابور التوليد.';
        Log::error('ProcessBlogAiJob failed', [
            'generation_id' => $this->generationId,
            'message' => $message,
        ]);

        // Worker died mid-run (timeout / restart): keep finished sections resumable.
        $hasWork = $generation->sections()
            ->where('status', BlogAiSection::STATUS_DONE)
            ->exists();

        if ($hasWork) {
            $generation->markPaused(
                'توقّفت المهمة في الطابور ('.$message.'). الأقسام المكتملة محفوظة — اضغط «متابعة التوليد».'
            );

            return;
        }

        $generation->markFailed($message);
    }
}
