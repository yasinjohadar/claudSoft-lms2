<?php

namespace App\Jobs;

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
 * Resumes one failed batch item, completing only its missing sections. Only
 * ever dispatched once the whole batch has already finished, so it never
 * runs concurrently with ProcessDocumentationAiBatchJob working another item.
 */
class ProcessDocumentationAiBatchItemResumeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 3600;

    public function __construct(public int $itemId) {}

    public function handle(DocumentationAiBatchRunner $runner): void
    {
        $runner->resumeItem($this->itemId);
    }

    public function failed(?Throwable $exception): void
    {
        $message = $exception?->getMessage() ?: 'فشل غير معروف أثناء متابعة التوليد.';
        Log::error('ProcessDocumentationAiBatchItemResumeJob failed', [
            'item_id' => $this->itemId,
            'message' => $message,
        ]);

        $item = DocumentationAiBatchItem::query()->with('batch')->find($this->itemId);
        if (! $item || ! $item->batch || $item->status !== DocumentationAiBatchItem::STATUS_RUNNING) {
            return;
        }

        $item->update([
            'status' => DocumentationAiBatchItem::STATUS_FAILED,
            'error_message' => $message,
        ]);

        app(DocumentationAiBatchRunner::class)->finalizeBatchIfDone($item->batch->fresh());
    }
}
