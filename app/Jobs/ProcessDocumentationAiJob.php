<?php

namespace App\Jobs;

use App\Models\DocumentationAiGeneration;
use App\Models\DocumentationAiSection;
use App\Services\AiNew\DocumentationAiPipelineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessDocumentationAiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Safe to retry: the staged pipeline resumes from persisted sections instead
     * of regenerating the whole page.
     */
    public int $tries = 3;

    /** Must stay below the database queue retry_after (3700s) to avoid double-running. */
    public int $timeout = 3600;

    /** @var list<int> */
    public array $backoff = [30, 120];

    public function __construct(public int $generationId) {}

    public function handle(DocumentationAiPipelineService $pipeline): void
    {
        $generation = DocumentationAiGeneration::query()->find($this->generationId);
        if (! $generation) {
            return;
        }

        $pipeline->run($generation);
    }

    public function failed(?Throwable $exception): void
    {
        $generation = DocumentationAiGeneration::query()->find($this->generationId);
        if (! $generation || in_array($generation->status, [
            DocumentationAiGeneration::STATUS_COMPLETED,
            DocumentationAiGeneration::STATUS_PAUSED,
        ], true)) {
            return;
        }

        $message = $exception?->getMessage() ?: 'فشل غير معروف في طابور التوليد.';
        Log::error('ProcessDocumentationAiJob failed', [
            'generation_id' => $this->generationId,
            'message' => $message,
        ]);

        // Worker died mid-run (timeout / restart): keep finished sections resumable.
        $hasWork = $generation->sections()
            ->where('status', DocumentationAiSection::STATUS_DONE)
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
