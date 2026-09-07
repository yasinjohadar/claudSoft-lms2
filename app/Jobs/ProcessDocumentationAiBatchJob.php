<?php

namespace App\Jobs;

use App\Models\DocumentationAiBatch;
use App\Models\DocumentationAiBatchItem;
use App\Services\AiNew\DocumentationAiBatchRunner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Processes exactly one pending topic of a documentation AI batch, saves the
 * resulting page immediately, then dispatches itself again for the next
 * pending topic — this self-redispatch is what keeps the batch strictly
 * sequential (one topic in flight at a time) no matter how many queue
 * workers are running.
 */
class ProcessDocumentationAiBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    /** Must comfortably cover one staged generation (outline + sections + merge). */
    public int $timeout = 3600;

    public function __construct(public int $batchId) {}

    public function handle(DocumentationAiBatchRunner $runner): void
    {
        $runner->processNext($this->batchId);
    }

    public function failed(?Throwable $exception): void
    {
        $message = $exception?->getMessage() ?: 'فشل غير معروف في معالجة الدفعة.';
        Log::error('ProcessDocumentationAiBatchJob failed', [
            'batch_id' => $this->batchId,
            'message' => $message,
        ]);

        $batch = DocumentationAiBatch::query()->find($this->batchId);
        if (! $batch || $batch->isFinished()) {
            return;
        }

        $item = $batch->items()->where('status', DocumentationAiBatchItem::STATUS_RUNNING)->first();
        if ($item) {
            $item->update([
                'status' => DocumentationAiBatchItem::STATUS_FAILED,
                'error_message' => $message,
            ]);
            $batch->increment('failed');
        }

        // Don't let one crashed job stall the rest of the batch.
        app(DocumentationAiBatchRunner::class)->dispatchNext($this->batchId);
    }
}
