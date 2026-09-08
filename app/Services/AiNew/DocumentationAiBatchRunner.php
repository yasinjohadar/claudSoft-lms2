<?php

namespace App\Services\AiNew;

use App\Jobs\ProcessDocumentationAiBatchItemResumeJob;
use App\Jobs\ProcessDocumentationAiBatchJob;
use App\Models\DocumentationAiBatch;
use App\Models\DocumentationAiBatchItem;
use App\Models\DocumentationAiGeneration;
use App\Models\DocumentationPage;
use App\Models\User;
use App\Services\Ai\DocumentationHtmlRepairer;
use Illuminate\Support\Str;

/**
 * Runs a batch of documentation topics one at a time: each topic goes through
 * the same staged AI pipeline as the single-topic wizard, but the resulting
 * page is saved immediately instead of waiting for manual review, and the
 * next topic only starts once the current one has fully finished (success or
 * failure) — so N topics never hit the AI provider concurrently.
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

    public function dispatchResume(int $itemId): void
    {
        if (config('queue.default') === 'sync') {
            dispatch(function () use ($itemId) {
                app(self::class)->resumeItem($itemId);
            })->afterResponse();

            return;
        }

        ProcessDocumentationAiBatchItemResumeJob::dispatch($itemId);
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

        $item = $batch->items()->where('status', DocumentationAiBatchItem::STATUS_PENDING)->first();

        if (! $item) {
            $this->finalizeBatchIfDone($batch);

            return;
        }

        $item->update(['status' => DocumentationAiBatchItem::STATUS_RUNNING]);
        if ($batch->status !== DocumentationAiBatch::STATUS_RUNNING) {
            $batch->update(['status' => DocumentationAiBatch::STATUS_RUNNING, 'started_at' => $batch->started_at ?? now()]);
        }

        $settings = $batch->settings;

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

        $this->pipeline->run($generation);
        $generation->refresh();

        if ($generation->status === DocumentationAiGeneration::STATUS_COMPLETED) {
            try {
                $page = $this->savePage($item->topic, $generation->result ?? [], $settings);
                $item->update([
                    'status' => DocumentationAiBatchItem::STATUS_COMPLETED,
                    'documentation_page_id' => $page->id,
                ]);
                $batch->increment('completed');
            } catch (\Throwable $e) {
                $item->update([
                    'status' => DocumentationAiBatchItem::STATUS_FAILED,
                    'error_message' => 'تعذر حفظ الصفحة: '.$e->getMessage(),
                ]);
                $batch->increment('failed');
            }
        } else {
            $item->update([
                'status' => DocumentationAiBatchItem::STATUS_FAILED,
                'error_message' => $generation->error_message ?? 'فشل التوليد',
            ]);
            $batch->increment('failed');
        }

        $this->dispatchNext($batchId);
    }

    /**
     * Resume a single failed item's generation, completing only its missing
     * sections. Only allowed once the whole batch has finished, so this never
     * runs alongside processNext() working on another item.
     */
    public function resumeItem(int $itemId): void
    {
        $item = DocumentationAiBatchItem::query()->with('batch', 'generation')->find($itemId);
        if (! $item || ! $item->batch || $item->status !== DocumentationAiBatchItem::STATUS_FAILED) {
            return;
        }

        $batch = $item->batch;
        $generation = $item->generation;

        if (! $batch->isFinished() || ! $generation || ! $generation->isResumable()) {
            return;
        }

        // Atomic claim: guards against a double-click resuming the same item twice.
        $claimed = DocumentationAiBatchItem::query()
            ->where('id', $itemId)
            ->where('status', DocumentationAiBatchItem::STATUS_FAILED)
            ->update(['status' => DocumentationAiBatchItem::STATUS_RUNNING]);

        if ($claimed === 0) {
            return;
        }

        // The claim above updated the row directly (not through this instance),
        // so $item's in-memory status is still stale "failed" — refresh it, or
        // the later update() below won't see status as dirty and will skip it.
        $item->refresh();

        // Leave the "finished" status set while this item is being reprocessed,
        // otherwise the frontend polling loop (which stops once finished=true)
        // would freeze the progress bar right after the first tick.
        $batch->update(['status' => DocumentationAiBatch::STATUS_RUNNING]);

        $generation->update([
            'status' => DocumentationAiGeneration::STATUS_QUEUED,
            'stage' => 'resuming',
            'stage_label' => 'استئناف التوليد…',
            'error_message' => null,
            'finished_at' => null,
            'heartbeat_at' => now(),
        ]);

        $this->pipeline->run($generation);
        $generation->refresh();

        if ($generation->status === DocumentationAiGeneration::STATUS_COMPLETED) {
            try {
                $page = $this->savePage($item->topic, $generation->result ?? [], $batch->settings);
                $item->update([
                    'status' => DocumentationAiBatchItem::STATUS_COMPLETED,
                    'documentation_page_id' => $page->id,
                ]);
                // Was already counted as failed on the earlier attempt — move it over.
                $batch->increment('completed');
                $batch->decrement('failed');
            } catch (\Throwable $e) {
                $item->update([
                    'status' => DocumentationAiBatchItem::STATUS_FAILED,
                    'error_message' => 'تعذر حفظ الصفحة: '.$e->getMessage(),
                ]);
            }
        } else {
            $item->update([
                'status' => DocumentationAiBatchItem::STATUS_FAILED,
                'error_message' => $generation->error_message ?? 'فشل التوليد',
            ]);
        }

        $this->finalizeBatchIfDone($batch->fresh());
    }

    public function finalizeBatchIfDone(DocumentationAiBatch $batch): void
    {
        $batch->update([
            'status' => $batch->failed > 0
                ? DocumentationAiBatch::STATUS_COMPLETED_WITH_ERRORS
                : DocumentationAiBatch::STATUS_COMPLETED,
            'finished_at' => now(),
        ]);
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
