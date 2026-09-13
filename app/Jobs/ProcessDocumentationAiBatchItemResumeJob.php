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
 * Deprecated compatibility shim.
 *
 * Resuming a topic no longer has its own driver: the item is parked in
 * DocumentationAiBatchItem::STATUS_RESUME_QUEUED and picked up by the normal
 * batch driver, which is what keeps one topic in flight at a time. This class
 * only stays so instances already sitting in the queue when the change deploys
 * still do the right thing instead of erroring.
 */
class ProcessDocumentationAiBatchItemResumeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 3600;

    public function __construct(public int $itemId) {}

    public function handle(DocumentationAiBatchRunner $runner): void
    {
        $item = DocumentationAiBatchItem::query()->find($this->itemId);
        if (! $item) {
            return;
        }

        $runner->dispatchNext($item->batch_id);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('ProcessDocumentationAiBatchItemResumeJob (shim) failed', [
            'item_id' => $this->itemId,
            'message' => $exception?->getMessage(),
        ]);
    }
}
