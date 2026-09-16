<?php

namespace App\Services\AiNew;

use App\Jobs\ProcessBlogAiBatchJob;
use App\Models\BlogAiBatch;
use App\Models\BlogAiBatchItem;
use App\Models\BlogAiGeneration;
use App\Models\BlogAiSection;
use App\Models\BlogPost;
use App\Models\User;
use App\Services\Ai\BlogHtmlRepairer;
use App\Services\Blog\BlogPostWriter;
use Illuminate\Support\Carbon;

/**
 * Runs a batch of blog topics one at a time: each topic goes through the same
 * staged AI pipeline as the single-article wizard, but the resulting article is
 * saved immediately as a draft instead of waiting for manual review, and the
 * next topic only starts once the current one has fully finished (success or
 * failure) — so N topics never hit the AI provider concurrently.
 *
 * Continuing a stopped topic rides the same driver: the item is parked in
 * STATUS_RESUME_QUEUED and picked up in turn, which is what keeps "resume all"
 * sequential without a second, competing job type.
 */
class BlogAiBatchRunner
{
    public function __construct(
        private BlogAiPipelineService $pipeline,
        private BlogPostWriter $postWriter,
        private BlogHtmlRepairer $repairer = new BlogHtmlRepairer,
    ) {}

    /**
     * @param  array<string, mixed>  $settings
     * @param  list<string>  $topics
     */
    public function start(User $user, array $settings, array $topics): BlogAiBatch
    {
        $batch = BlogAiBatch::query()->create([
            'user_id' => $user->id,
            'status' => BlogAiBatch::STATUS_QUEUED,
            'total' => count($topics),
            'settings' => $settings,
        ]);

        foreach (array_values($topics) as $index => $topic) {
            BlogAiBatchItem::query()->create([
                'batch_id' => $batch->id,
                'position' => $index,
                'topic' => $topic,
                'status' => BlogAiBatchItem::STATUS_PENDING,
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

        ProcessBlogAiBatchJob::dispatch($batchId);
    }

    public function cancel(BlogAiBatch $batch): void
    {
        if ($batch->isFinished()) {
            return;
        }

        $batch->update([
            'status' => BlogAiBatch::STATUS_CANCELLED,
            'finished_at' => now(),
        ]);
    }

    public function processNext(int $batchId): void
    {
        $batch = BlogAiBatch::query()->find($batchId);
        if (! $batch || $batch->status === BlogAiBatch::STATUS_CANCELLED) {
            return;
        }

        // One topic in flight per batch, always: a second driver must back off
        // rather than start a parallel generation against the same provider.
        if ($batch->items()->where('status', BlogAiBatchItem::STATUS_RUNNING)->exists()) {
            return;
        }

        // items() is ordered by position, so pending and resume-queued topics
        // are taken in the order the admin sees them.
        $item = $batch->items()
            ->whereIn('status', BlogAiBatchItem::RUNNABLE_STATUSES)
            ->first();

        if (! $item) {
            $this->finalizeBatchIfDone($batch);

            return;
        }

        // Atomic claim: two workers racing on the same row — or a reaper closing
        // it underneath us — and only one of us proceeds.
        $claimed = BlogAiBatchItem::query()
            ->whereKey($item->id)
            ->whereIn('status', BlogAiBatchItem::RUNNABLE_STATUSES)
            ->update([
                'status' => BlogAiBatchItem::STATUS_RUNNING,
                'updated_at' => now(),
            ]);

        if ($claimed === 0) {
            return;
        }

        $wasResume = $item->status === BlogAiBatchItem::STATUS_RESUME_QUEUED;

        // The claim above wrote the row directly, so this instance still holds
        // the stale status — refresh, or later update() calls see nothing dirty.
        $item->refresh();

        if ($batch->status !== BlogAiBatch::STATUS_RUNNING) {
            $batch->update([
                'status' => BlogAiBatch::STATUS_RUNNING,
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
                'status' => BlogAiGeneration::STATUS_QUEUED,
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

        if ($generation->status === BlogAiGeneration::STATUS_COMPLETED) {
            try {
                $post = $this->savePost($batch, $item->topic, $generation->result ?? [], $settings);
                $this->closeItem($item, BlogAiBatchItem::STATUS_COMPLETED, postId: $post->id);
            } catch (\Throwable $e) {
                $this->closeItem(
                    $item,
                    BlogAiBatchItem::STATUS_FAILED,
                    'تعذر حفظ المقال: '.$e->getMessage()
                );
            }
        } else {
            $this->closeItem(
                $item,
                BlogAiBatchItem::STATUS_FAILED,
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
    public function queueResume(BlogAiBatchItem $item): bool
    {
        $item->loadMissing('batch', 'generation.sections');

        if (! $item->batch || ! $item->generation?->isResumable()) {
            return false;
        }

        $claimed = BlogAiBatchItem::query()
            ->whereKey($item->id)
            ->where('status', BlogAiBatchItem::STATUS_FAILED)
            ->update([
                'status' => BlogAiBatchItem::STATUS_RESUME_QUEUED,
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
            'status' => BlogAiBatch::STATUS_RUNNING,
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
    public function reapStalled(BlogAiBatch $batch): bool
    {
        if ($batch->status === BlogAiBatch::STATUS_CANCELLED) {
            return false;
        }

        $running = $batch->items()
            ->where('status', BlogAiBatchItem::STATUS_RUNNING)
            ->with('generation')
            ->get();

        if ($running->isEmpty()) {
            return false;
        }

        $cutoff = now()->subMinutes(BlogAiGeneration::STALE_HEARTBEAT_MINUTES);
        $changed = false;

        foreach ($running as $item) {
            $reason = $this->stalledReason($item, $cutoff);
            if ($reason === null) {
                continue;
            }

            $this->stopGeneration($item->generation, $reason);

            $closed = BlogAiBatchItem::query()
                ->whereKey($item->id)
                ->where('status', BlogAiBatchItem::STATUS_RUNNING)
                ->update([
                    'status' => BlogAiBatchItem::STATUS_FAILED,
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
    public function releaseStuckItem(BlogAiBatchItem $item): bool
    {
        $item->loadMissing('batch', 'generation');

        if (! $item->batch || ! $item->canRelease()) {
            return false;
        }

        $reason = 'تم تحرير الموضوع يدوياً بعد توقّف المعالجة. الأقسام المكتملة محفوظة — اضغط «متابعة التوليد».';

        $this->stopGeneration($item->generation, $reason);

        $closed = BlogAiBatchItem::query()
            ->whereKey($item->id)
            ->where('status', BlogAiBatchItem::STATUS_RUNNING)
            ->update([
                'status' => BlogAiBatchItem::STATUS_FAILED,
                'error_message' => $reason,
                'updated_at' => now(),
            ]);

        if ($closed === 0) {
            return false;
        }

        $this->settleAfterReap($item->batch);

        return true;
    }

    public function finalizeBatchIfDone(BlogAiBatch $batch): void
    {
        // A cancelled batch stays cancelled — don't relabel it as completed.
        if ($batch->status === BlogAiBatch::STATUS_CANCELLED) {
            return;
        }

        $batch->load('items');
        $batch->recountFromItems();

        $batch->update([
            'status' => $batch->itemStatusCounts()['failed'] > 0
                ? BlogAiBatch::STATUS_COMPLETED_WITH_ERRORS
                : BlogAiBatch::STATUS_COMPLETED,
            'finished_at' => now(),
        ]);
    }

    /** Re-sync counters and close the batch when nothing is left in flight. */
    private function settleAfterReap(BlogAiBatch $batch): void
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
    private function stopGeneration(?BlogAiGeneration $generation, string $reason): void
    {
        if (! $generation) {
            return;
        }

        if (in_array($generation->status, [
            BlogAiGeneration::STATUS_PAUSED,
            BlogAiGeneration::STATUS_FAILED,
            BlogAiGeneration::STATUS_CANCELLED,
        ], true)) {
            return;
        }

        $generation->sections()->where('status', BlogAiSection::STATUS_DONE)->exists()
            ? $generation->markPaused($reason)
            : $generation->markFailed($reason);
    }

    /** Why this running item looks abandoned, or null if it still looks alive. */
    private function stalledReason(BlogAiBatchItem $item, Carbon $cutoff): ?string
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

        if ($generation->status === BlogAiGeneration::STATUS_QUEUED
            && $generation->updated_at?->lt($cutoff)
        ) {
            return 'لم يلتقط أي عامل هذه المهمة. تأكد من تشغيل معالج الطابور ثم اضغط «متابعة التوليد».';
        }

        if (in_array($generation->status, [
            BlogAiGeneration::STATUS_PAUSED,
            BlogAiGeneration::STATUS_FAILED,
            BlogAiGeneration::STATUS_CANCELLED,
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
        BlogAiBatchItem $item,
        string $status,
        ?string $errorMessage = null,
        ?int $postId = null,
    ): void {
        $updates = [
            'status' => $status,
            'error_message' => $errorMessage,
            'updated_at' => now(),
        ];

        if ($postId !== null) {
            $updates['blog_post_id'] = $postId;
        }

        BlogAiBatchItem::query()
            ->whereKey($item->id)
            ->where('status', BlogAiBatchItem::STATUS_RUNNING)
            ->update($updates);

        $item->refresh();
    }

    /** @param  array<string, mixed>  $settings */
    private function createGeneration(
        BlogAiBatch $batch,
        BlogAiBatchItem $item,
        array $settings,
    ): BlogAiGeneration {
        $generation = BlogAiGeneration::query()->create([
            'user_id' => $batch->user_id,
            'operation' => BlogAiGeneration::OPERATION_GENERATE,
            'status' => BlogAiGeneration::STATUS_QUEUED,
            'progress' => 0,
            'stage' => 'queued',
            'stage_label' => 'في الطابور…',
            'payload' => [
                'topic' => $item->topic,
                'blog_engine' => $settings['blog_engine'] ?? 'legacy',
                'ai_model_id' => $settings['ai_model_id'] ?? null,
                'laravel_ai_model_id' => $settings['laravel_ai_model_id'] ?? null,
                'content_length' => $settings['content_length'] ?? 'medium',
                'tone' => $settings['tone'] ?? 'professional',
                'language' => $settings['language'] ?? 'ar',
                'category_id' => $settings['category_id'] ?? null,
                'generate_seo' => $settings['generate_seo'] ?? true,
                'generate_og' => $settings['generate_og'] ?? true,
                'generate_twitter' => $settings['generate_twitter'] ?? true,
                'generate_schema' => $settings['generate_schema'] ?? true,
                'generate_keyword_synonyms' => $settings['generate_keyword_synonyms'] ?? true,
            ],
        ]);

        $item->update(['blog_ai_generation_id' => $generation->id]);

        return $generation;
    }

    /**
     * Save the finished article immediately — always as a draft, whatever the
     * batch settings say, so a run of 30 topics can never publish itself.
     *
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $settings
     */
    private function savePost(BlogAiBatch $batch, string $topic, array $result, array $settings): BlogPost
    {
        $title = trim((string) ($result['title'] ?? '')) ?: $topic;

        $content = (string) ($result['content'] ?? '');
        if ($content !== '') {
            $repaired = $this->repairer->repairDocument($content);
            if ($repaired !== '') {
                $content = $repaired;
            }
        }

        $data = $result;
        unset($data['tags']);

        $data['title'] = $title;
        $data['content'] = $content;
        $data['blog_category_id'] = ! empty($settings['category_id']) ? (int) $settings['category_id'] : null;
        $data['language'] = $settings['language'] ?? 'ar';
        $data['status'] = 'draft';
        $data['published_at'] = null;
        $data['featured_image_alt'] ??= $title;
        $data['tag_names'] = ($settings['generate_tags'] ?? true) ? $this->tagNamesFrom($result) : [];

        // Auth::id() is null on a queue worker, so the author comes from the batch.
        return $this->postWriter->create($data, authorId: $batch->user_id);
    }

    /**
     * Tag names the model suggested, falling back to the SEO keywords it did
     * produce. Never invents tags when there is nothing to work with.
     *
     * @param  array<string, mixed>  $result
     * @return list<string>
     */
    private function tagNamesFrom(array $result): array
    {
        $tags = $result['tags'] ?? null;
        if (is_array($tags) && $tags !== []) {
            return array_values(array_filter(array_map(
                fn ($tag) => is_scalar($tag) ? trim((string) $tag) : '',
                $tags
            )));
        }

        $keywords = trim((string) ($result['meta_keywords'] ?? ''));
        if ($keywords !== '') {
            return array_values(array_filter(array_map(
                'trim',
                preg_split('/[,\x{060C}]/u', $keywords) ?: []
            )));
        }

        $focus = trim((string) ($result['focus_keyword'] ?? ''));

        return $focus !== '' ? [$focus] : [];
    }
}
