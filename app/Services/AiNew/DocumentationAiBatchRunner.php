<?php

namespace App\Services\AiNew;

use App\Jobs\ProcessDocumentationAiBatchJob;
use App\Models\DocumentationAiBatch;
use App\Models\DocumentationAiBatchItem;
use App\Models\DocumentationAiGeneration;
use App\Models\DocumentationAiSection;
use App\Models\DocumentationPage;
use App\Models\User;
use App\Services\Ai\DocumentationHtmlRepairer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Runs a batch of documentation topics one at a time: each topic goes through
 * the same staged AI pipeline as the single-topic wizard, but the resulting
 * page is saved immediately instead of waiting for manual review, and the
 * next topic only starts once the current one has fully finished (success or
 * failure) — so N topics never hit the AI provider concurrently.
 *
 * Continuing a stopped topic rides the same driver: the item is parked in
 * STATUS_RESUME_QUEUED and picked up in turn, which is what keeps "resume all"
 * sequential without a second, competing job type.
 */
class DocumentationAiBatchRunner
{
    public function __construct(
        private DocumentationAiPipelineService $pipeline,
        private DocumentationHtmlRepairer $repairer = new DocumentationHtmlRepairer,
    ) {}

    /**
     * @param  array<string, mixed>  $settings
     * @param  list<string>  $topics
     */
    public function start(User $user, array $settings, array $topics): DocumentationAiBatch
    {
        $batch = DocumentationAiBatch::query()->create([
            'user_id' => $user->id,
            'status' => DocumentationAiBatch::STATUS_QUEUED,
            'total' => count($topics),
            'settings' => $settings,
        ]);

        foreach (array_values($topics) as $index => $topic) {
            DocumentationAiBatchItem::query()->create([
                'batch_id' => $batch->id,
                'position' => $index,
                'topic' => $topic,
                'status' => DocumentationAiBatchItem::STATUS_PENDING,
            ]);
        }

        $this->dispatchNext($batch->id);

        return $batch;
    }

    public function dispatchNext(int $batchId): void
    {
        // Local/dev with sync driver: run after the HTTP response so the request isn't blocked.
        if (config('queue.default') === 'sync') {
            dispatch(function () use ($batchId) {
                app(self::class)->processNext($batchId);
            })->afterResponse();

            return;
        }

        ProcessDocumentationAiBatchJob::dispatch($batchId);
    }

    public function cancel(DocumentationAiBatch $batch): void
    {
        if ($batch->isFinished()) {
            return;
        }

        $batch->update([
            'status' => DocumentationAiBatch::STATUS_CANCELLED,
            'finished_at' => now(),
        ]);
    }

    public function processNext(int $batchId): void
    {
        $batch = DocumentationAiBatch::query()->find($batchId);
        if (! $batch || $batch->status === DocumentationAiBatch::STATUS_CANCELLED) {
            return;
        }

        // One topic in flight per batch, always: a second driver must back off
        // rather than start a parallel generation against the same provider.
        if ($batch->items()->where('status', DocumentationAiBatchItem::STATUS_RUNNING)->exists()) {
            return;
        }

        // items() is ordered by position, so pending and resume-queued topics
        // are taken in the order the admin sees them.
        $item = $batch->items()
            ->whereIn('status', DocumentationAiBatchItem::RUNNABLE_STATUSES)
            ->first();

        if (! $item) {
            $this->finalizeBatchIfDone($batch);

            return;
        }

        // Atomic claim: two workers racing on the same row — or a reaper closing
        // it underneath us — and only one of us proceeds.
        $claimed = DocumentationAiBatchItem::query()
            ->whereKey($item->id)
            ->whereIn('status', DocumentationAiBatchItem::RUNNABLE_STATUSES)
            ->update([
                'status' => DocumentationAiBatchItem::STATUS_RUNNING,
                'updated_at' => now(),
            ]);

        if ($claimed === 0) {
            return;
        }

        $wasResume = $item->status === DocumentationAiBatchItem::STATUS_RESUME_QUEUED;

        // The claim above wrote the row directly, so this instance still holds
        // the stale status — refresh, or later update() calls see nothing dirty.
        $item->refresh();

        if ($batch->status !== DocumentationAiBatch::STATUS_RUNNING) {
            $batch->update([
                'status' => DocumentationAiBatch::STATUS_RUNNING,
                'started_at' => $batch->started_at ?? now(),
                'finished_at' => null,
            ]);
        }

        $settings = $batch->settings;
        $generation = $wasResume ? $item->generation : null;

        if ($generation && $generation->isResumable()) {
            // Reuse the same generation row: the staged generator skips every
            // section already marked done and writes only the missing ones.
            $generation->update([
                'status' => DocumentationAiGeneration::STATUS_QUEUED,
                'stage' => 'resuming',
                'stage_label' => 'استئناف التوليد — إكمال الأقسام الناقصة…',
                'error_message' => null,
                'finished_at' => null,
                'heartbeat_at' => now(),
            ]);
        } else {
            $generation = $this->createGeneration($batch, $item, $settings);
        }

        $this->pipeline->run($generation);
        $generation->refresh();

        if ($generation->status === DocumentationAiGeneration::STATUS_COMPLETED) {
            try {
                $page = $this->savePage($item->topic, $generation->result ?? [], $settings);
                $this->closeItem($item, DocumentationAiBatchItem::STATUS_COMPLETED, pageId: $page->id);
            } catch (\Throwable $e) {
                $this->closeItem(
                    $item,
                    DocumentationAiBatchItem::STATUS_FAILED,
                    'تعذر حفظ الصفحة: '.$e->getMessage()
                );
            }
        } else {
            $this->closeItem(
                $item,
                DocumentationAiBatchItem::STATUS_FAILED,
                $generation->error_message ?? 'فشل التوليد'
            );
        }

        $batch->fresh()?->recountFromItems();

        $this->dispatchNext($batchId);
    }

    /**
     * Queue a stopped topic to be continued. Runs synchronously in the request
     * so the JSON that dispatches the job already reports the topic as queued —
     * otherwise the page sees a still-finished batch and stops polling within a
     * second, which is why "متابعة التوليد" used to look like it did nothing.
     */
    public function queueResume(DocumentationAiBatchItem $item): bool
    {
        $item->loadMissing('batch', 'generation.sections');

        if (! $item->batch || ! $item->generation?->isResumable()) {
            return false;
        }

        $claimed = DocumentationAiBatchItem::query()
            ->whereKey($item->id)
            ->where('status', DocumentationAiBatchItem::STATUS_FAILED)
            ->update([
                'status' => DocumentationAiBatchItem::STATUS_RESUME_QUEUED,
                'error_message' => null,
                'updated_at' => now(),
            ]);

        if ($claimed === 0) {
            return false;
        }

        $item->refresh();

        // Deliberately leave the generation paused/failed: that status is what
        // isResumable() keys off, and processNext() needs it to still be true so
        // it reuses this row instead of starting the topic from scratch. Only
        // the labels change, so the row reads as queued in the UI.
        $item->generation->update([
            'stage' => 'resume_queued',
            'stage_label' => 'في الطابور — بانتظار بدء المتابعة…',
            'heartbeat_at' => now(),
        ]);

        // Reopening the batch is what keeps the frontend polling loop alive.
        $item->batch->update([
            'status' => DocumentationAiBatch::STATUS_RUNNING,
            'finished_at' => null,
        ]);
        $item->batch->fresh()?->recountFromItems();

        $this->dispatchNext($item->batch_id);

        return true;
    }

    /**
     * Close out topics whose worker died. Without this a killed queue process
     * leaves the item "running" and the batch unfinished forever, and every
     * resume attempt is rejected as "still processing". Idempotent: it only
     * transitions rows that are still running, via a compare-and-swap, so
     * running it twice can never double-count anything.
     */
    public function reapStalled(DocumentationAiBatch $batch): bool
    {
        if ($batch->status === DocumentationAiBatch::STATUS_CANCELLED) {
            return false;
        }

        $running = $batch->items()
            ->where('status', DocumentationAiBatchItem::STATUS_RUNNING)
            ->with('generation')
            ->get();

        if ($running->isEmpty()) {
            return false;
        }

        $cutoff = now()->subMinutes(DocumentationAiGeneration::STALE_HEARTBEAT_MINUTES);
        $changed = false;

        foreach ($running as $item) {
            $reason = $this->stalledReason($item, $cutoff);
            if ($reason === null) {
                continue;
            }

            $this->stopGeneration($item->generation, $reason);

            $closed = DocumentationAiBatchItem::query()
                ->whereKey($item->id)
                ->where('status', DocumentationAiBatchItem::STATUS_RUNNING)
                ->update([
                    'status' => DocumentationAiBatchItem::STATUS_FAILED,
                    'error_message' => $item->generation?->fresh()?->error_message ?: $reason,
                    'updated_at' => now(),
                ]);

            $changed = $changed || $closed > 0;
        }

        if (! $changed) {
            return false;
        }

        $this->settleAfterReap($batch);

        return true;
    }

    /**
     * Force one silent topic back to "incomplete" now, without waiting out the
     * full stale window. Guarded by a much shorter silence threshold so it can
     * never start a second pipeline over a generation still being written to.
     */
    public function releaseStuckItem(DocumentationAiBatchItem $item): bool
    {
        $item->loadMissing('batch', 'generation');

        if (! $item->batch || ! $item->canRelease()) {
            return false;
        }

        $reason = 'تم تحرير الموضوع يدوياً بعد توقّف المعالجة. الأقسام المكتملة محفوظة — اضغط «متابعة التوليد».';

        $this->stopGeneration($item->generation, $reason);

        $closed = DocumentationAiBatchItem::query()
            ->whereKey($item->id)
            ->where('status', DocumentationAiBatchItem::STATUS_RUNNING)
            ->update([
                'status' => DocumentationAiBatchItem::STATUS_FAILED,
                'error_message' => $reason,
                'updated_at' => now(),
            ]);

        if ($closed === 0) {
            return false;
        }

        $this->settleAfterReap($item->batch);

        return true;
    }

    public function finalizeBatchIfDone(DocumentationAiBatch $batch): void
    {
        // A cancelled batch stays cancelled — don't relabel it as completed.
        if ($batch->status === DocumentationAiBatch::STATUS_CANCELLED) {
            return;
        }

        $batch->load('items');
        $batch->recountFromItems();

        $batch->update([
            'status' => $batch->itemStatusCounts()['failed'] > 0
                ? DocumentationAiBatch::STATUS_COMPLETED_WITH_ERRORS
                : DocumentationAiBatch::STATUS_COMPLETED,
            'finished_at' => now(),
        ]);
    }

    /** Re-sync counters and close the batch when nothing is left in flight. */
    private function settleAfterReap(DocumentationAiBatch $batch): void
    {
        $batch = $batch->fresh();
        if (! $batch) {
            return;
        }

        $batch->load('items');
        $batch->recountFromItems();

        if (! $batch->hasOpenWork() && ! $batch->isFinished()) {
            $this->finalizeBatchIfDone($batch);
        }
    }

    /**
     * Park a generation whose worker vanished. Sections that finished stay in
     * the database, so the topic remains resumable rather than starting over.
     */
    private function stopGeneration(?DocumentationAiGeneration $generation, string $reason): void
    {
        if (! $generation) {
            return;
        }

        if (in_array($generation->status, [
            DocumentationAiGeneration::STATUS_PAUSED,
            DocumentationAiGeneration::STATUS_FAILED,
            DocumentationAiGeneration::STATUS_CANCELLED,
        ], true)) {
            return;
        }

        $generation->sections()->where('status', DocumentationAiSection::STATUS_DONE)->exists()
            ? $generation->markPaused($reason)
            : $generation->markFailed($reason);
    }

    /** Why this running item looks abandoned, or null if it still looks alive. */
    private function stalledReason(DocumentationAiBatchItem $item, Carbon $cutoff): ?string
    {
        $generation = $item->generation;

        if (! $generation) {
            // Claimed, then the process died before the generation row existed.
            return $item->updated_at?->lt($cutoff)
                ? 'انقطعت المعالجة قبل أن تبدأ. تأكد من تشغيل معالج الطابور ثم أعد المحاولة.'
                : null;
        }

        if ($generation->isStale()) {
            return 'توقّف التوليد دون استجابة من الخادم. الأقسام المكتملة محفوظة — اضغط «متابعة التوليد».';
        }

        if ($generation->status === DocumentationAiGeneration::STATUS_QUEUED
            && $generation->updated_at?->lt($cutoff)
        ) {
            return 'لم يلتقط أي عامل هذه المهمة. تأكد من تشغيل معالج الطابور ثم اضغط «متابعة التوليد».';
        }

        if (in_array($generation->status, [
            DocumentationAiGeneration::STATUS_PAUSED,
            DocumentationAiGeneration::STATUS_FAILED,
            DocumentationAiGeneration::STATUS_CANCELLED,
        ], true)) {
            // The pipeline finished but the runner died before closing the item.
            return $generation->error_message ?: 'توقّف التوليد قبل اكتماله.';
        }

        return null;
    }

    /**
     * Terminal write guarded on "still running", so a reaper and the worker can
     * never both close the same row with different outcomes.
     */
    private function closeItem(
        DocumentationAiBatchItem $item,
        string $status,
        ?string $errorMessage = null,
        ?int $pageId = null,
    ): void {
        $updates = [
            'status' => $status,
            'error_message' => $errorMessage,
            'updated_at' => now(),
        ];

        if ($pageId !== null) {
            $updates['documentation_page_id'] = $pageId;
        }

        DocumentationAiBatchItem::query()
            ->whereKey($item->id)
            ->where('status', DocumentationAiBatchItem::STATUS_RUNNING)
            ->update($updates);

        $item->refresh();
    }

    /** @param  array<string, mixed>  $settings */
    private function createGeneration(
        DocumentationAiBatch $batch,
        DocumentationAiBatchItem $item,
        array $settings,
    ): DocumentationAiGeneration {
        $generation = DocumentationAiGeneration::query()->create([
            'user_id' => $batch->user_id,
            'operation' => DocumentationAiGeneration::OPERATION_GENERATE,
            'status' => DocumentationAiGeneration::STATUS_QUEUED,
            'progress' => 0,
            'stage' => 'queued',
            'stage_label' => 'في الطابور…',
            'payload' => [
                'topic' => $item->topic,
                'docs_engine' => $settings['docs_engine'] ?? 'legacy',
                'ai_model_id' => $settings['ai_model_id'] ?? null,
                'laravel_ai_model_id' => $settings['laravel_ai_model_id'] ?? null,
                'content_length' => $settings['content_length'] ?? 'medium',
                'tone' => $settings['tone'] ?? 'professional',
                'language' => $settings['language'] ?? 'ar',
                'documentation_category_id' => $settings['documentation_category_id'] ?? null,
                'parent_id' => $settings['parent_id'] ?? null,
                'generate_meta' => $settings['generate_meta'] ?? true,
            ],
        ]);

        $item->update(['documentation_ai_generation_id' => $generation->id]);

        return $generation;
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $settings
     */
    private function savePage(string $topic, array $result, array $settings): DocumentationPage
    {
        $title = trim((string) ($result['title'] ?? '')) ?: $topic;
        $content = (string) ($result['content'] ?? '');
        if ($content !== '') {
            $repaired = $this->repairer->repairDocument($content);
            if ($repaired !== '') {
                $content = $repaired;
            }
        }

        $categoryId = (int) ($settings['documentation_category_id'] ?? 0);
        $parentId = ! empty($settings['parent_id']) ? (int) $settings['parent_id'] : null;
        $slug = $this->uniqueSlug($categoryId, $parentId, (string) ($result['slug'] ?? '') ?: $title);

        $status = $settings['status'] ?? 'published';
        $publishedAt = $settings['published_at'] ?? null;
        if ($status === 'published' && empty($publishedAt)) {
            $publishedAt = now();
        }

        return DocumentationPage::create([
            'documentation_category_id' => $categoryId,
            // Batches created before researches existed simply lack the key.
            'documentation_research_id' => ! empty($settings['documentation_research_id'])
                ? (int) $settings['documentation_research_id']
                : null,
            'parent_id' => $parentId,
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $result['excerpt'] ?? null,
            'content' => $content,
            'sort_order' => $settings['sort_order'] ?? 0,
            'status' => $status,
            'published_at' => $publishedAt,
            'meta_title' => $result['meta_title'] ?? null,
            'meta_description' => $result['meta_description'] ?? null,
            'is_indexable' => $settings['is_indexable'] ?? true,
        ]);
    }

    private function uniqueSlug(int $categoryId, ?int $parentId, string $seed): string
    {
        $base = Str::slug($seed) ?: 'page-'.time();
        $slug = $base;
        $suffix = 2;

        while ($this->slugExists($categoryId, $parentId, $slug)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function slugExists(int $categoryId, ?int $parentId, string $slug): bool
    {
        $query = DocumentationPage::query()
            ->where('documentation_category_id', $categoryId)
            ->where('slug', $slug);

        $parentId === null ? $query->whereNull('parent_id') : $query->where('parent_id', $parentId);

        return $query->exists();
    }
}
