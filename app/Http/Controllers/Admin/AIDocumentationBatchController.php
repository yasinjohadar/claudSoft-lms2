<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\UsesLaravelAiSdkForWizards;
use App\Http\Controllers\Controller;
use App\Models\DocumentationAiBatch;
use App\Models\DocumentationAiBatchItem;
use App\Models\DocumentationAiGeneration;
use App\Models\DocumentationAiSection;
use App\Models\DocumentationCategory;
use App\Models\DocumentationPage;
use App\Models\LaravelAiModel;
use App\Services\Ai\AIModelService;
use App\Services\AiNew\DocumentationAiBatchRunner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AIDocumentationBatchController extends Controller
{
    use UsesLaravelAiSdkForWizards;

    /**
     * A batch this recent is still worth restoring onto the create page after a
     * refresh — long enough to survive going home and coming back the next
     * morning, short enough not to resurrect ancient work.
     */
    private const RESTORE_WINDOW_DAYS = 7;

    public function __construct(
        private AIModelService $modelService,
        private DocumentationAiBatchRunner $runner,
    ) {}

    public function create(Request $request)
    {
        $categories = DocumentationCategory::active()->ordered()->get();
        $models = $this->modelService->getAvailableModels('all');
        $categoryId = $request->get('documentation_category_id');

        if (! $categoryId) {
            $categoryId = $categories->first(function (DocumentationCategory $cat) {
                return strcasecmp((string) $cat->slug, 'html') === 0
                    || strcasecmp((string) $cat->name, 'html') === 0;
            })?->id;
        }

        $defaultPublishedAt = now()->format('Y-m-d\TH:i');
        $parentPages = DocumentationPage::with('category')
            ->orderBy('documentation_category_id')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title', 'documentation_category_id']);

        $parentPagesJson = $parentPages->map(fn (DocumentationPage $p) => [
            'id' => $p->id,
            'category_id' => $p->documentation_category_id,
            'label' => ($p->category->name ?? '—').' — '.$p->title,
        ])->values()->all();

        $useLaravelAiEngine = $this->wizardUsesLaravelAiSdk('docs_engine');
        $laravelAiModels = LaravelAiModel::query()->activeOrdered()->get();
        $docsEngineChoiceAvailable = $models->isNotEmpty() && $laravelAiModels->isNotEmpty();

        // Everything about a batch lives in the database; the page just had no
        // way to find it again after a reload. Hand it back the live batch.
        $activeBatch = $this->resolveRestorableBatch($request->query('batch'));
        $initialBatch = $activeBatch?->toStatusPayload();
        $initialSettings = $activeBatch?->settings;
        $incompleteCount = $this->incompleteItemsCountForUser();

        return view('admin.docs.pages.ai-batch-create', compact(
            'categories',
            'models',
            'categoryId',
            'defaultPublishedAt',
            'parentPagesJson',
            'useLaravelAiEngine',
            'laravelAiModels',
            'docsEngineChoiceAvailable',
            'initialBatch',
            'initialSettings',
            'incompleteCount',
        ));
    }

    /** Batch history: every batch this admin has run, newest first. */
    public function index(Request $request)
    {
        // Heal anything the queue abandoned so the counts below are honest.
        DocumentationAiBatch::query()
            ->where('user_id', Auth::id())
            ->whereNotIn('status', [
                DocumentationAiBatch::STATUS_COMPLETED,
                DocumentationAiBatch::STATUS_CANCELLED,
            ])
            ->get()
            ->each(fn (DocumentationAiBatch $batch) => $this->runner->reapStalled($batch));

        $onlyIncomplete = $request->boolean('incomplete');

        $batches = DocumentationAiBatch::query()
            ->where('user_id', Auth::id())
            ->withCount([
                'items as completed_count' => fn ($q) => $q->where('status', DocumentationAiBatchItem::STATUS_COMPLETED),
                'items as failed_count' => fn ($q) => $q->where('status', DocumentationAiBatchItem::STATUS_FAILED),
                'items as open_count' => fn ($q) => $q->whereIn('status', DocumentationAiBatchItem::OPEN_STATUSES),
            ])
            ->when($onlyIncomplete, fn ($q) => $q->having('failed_count', '>', 0))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totals = [
            'batches' => DocumentationAiBatch::query()->where('user_id', Auth::id())->count(),
            'incomplete_items' => $this->incompleteItemsCountForUser(),
        ];

        return view('admin.docs.pages.ai-batch-index', compact('batches', 'totals', 'onlyIncomplete'));
    }

    /** Live detail page for one batch — the table is filled by the same poller. */
    public function show(string $uuid)
    {
        $batch = $this->loadBatch($uuid);
        $this->runner->reapStalled($batch);
        $batch = $this->loadBatch($uuid);

        $initialBatch = $batch->toStatusPayload();
        $category = DocumentationCategory::find($batch->settings['documentation_category_id'] ?? null);

        return view('admin.docs.pages.ai-batch-show', compact('batch', 'initialBatch', 'category'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'topics' => 'required|array|min:1|max:30',
            'topics.*' => 'required|string|max:3000',
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'laravel_ai_model_id' => 'nullable|exists:laravel_ai_models,id',
            'docs_engine' => 'nullable|in:laravel_ai,legacy',
            'content_length' => 'required|in:short,medium,long',
            'tone' => 'nullable|in:professional,friendly,technical,casual,formal',
            'language' => 'nullable|in:ar,en',
            'documentation_category_id' => 'required|exists:documentation_categories,id',
            'parent_id' => 'nullable|exists:documentation_pages,id',
            'generate_meta' => 'boolean',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date',
            'is_indexable' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $topics = collect($validated['topics'])
            ->map(fn ($t) => trim((string) $t))
            ->filter(fn ($t) => $t !== '')
            ->values()
            ->all();

        if ($topics === []) {
            return $this->fail('يرجى إدخال موضوع واحد على الأقل.', 422);
        }

        try {
            $category = DocumentationCategory::find($validated['documentation_category_id']);
            $parent = ! empty($validated['parent_id'])
                ? DocumentationPage::find($validated['parent_id'])
                : null;

            if ($parent && (int) $parent->documentation_category_id !== (int) $category->id) {
                return $this->fail('صفحة الأب يجب أن تنتمي لنفس القسم المختار', 422);
            }

            $requestedEngine = $validated['docs_engine'] ?? null;
            if ($requestedEngine === 'laravel_ai' && ! LaravelAiModel::query()->where('is_active', true)->exists()) {
                return $this->fail(
                    'لا يوجد موديل Laravel AI نشط. أضف موديلاً من لوحة «موديلات Laravel AI SDK» أو اختر المحرك القديم.',
                    400
                );
            }

            $engine = $this->resolveDocumentationAiEngine(
                $requestedEngine,
                ! empty($validated['laravel_ai_model_id']) ? (int) $validated['laravel_ai_model_id'] : null,
                ! empty($validated['ai_model_id']) ? (int) $validated['ai_model_id'] : null,
            ) ? 'laravel_ai' : 'legacy';

            if ($engine === 'laravel_ai' && ! empty($validated['laravel_ai_model_id'])) {
                $laraModel = LaravelAiModel::query()
                    ->where('id', $validated['laravel_ai_model_id'])
                    ->where('is_active', true)
                    ->first();
                if (! $laraModel) {
                    return $this->fail('موديل Laravel AI المحدد غير متاح أو غير نشط.', 400);
                }
            }

            $settings = [
                'docs_engine' => $engine,
                'ai_model_id' => $validated['ai_model_id'] ?? null,
                'laravel_ai_model_id' => $validated['laravel_ai_model_id'] ?? null,
                'content_length' => $validated['content_length'],
                'tone' => $validated['tone'] ?? 'professional',
                'language' => $validated['language'] ?? 'ar',
                'documentation_category_id' => (int) $validated['documentation_category_id'],
                'parent_id' => $validated['parent_id'] ?? null,
                'generate_meta' => $validated['generate_meta'] ?? true,
                'status' => $validated['status'],
                'published_at' => $validated['published_at'] ?? null,
                'is_indexable' => $request->boolean('is_indexable', true),
                'sort_order' => $validated['sort_order'] ?? 0,
            ];

            $batch = $this->runner->start(Auth::user(), $settings, $topics);

            return $this->ok($this->loadBatch($batch->uuid));
        } catch (\Exception $e) {
            Log::error('AI documentation batch start: '.$e->getMessage());

            return $this->fail('تعذر بدء الدفعة: '.$e->getMessage(), 500);
        }
    }

    /** JSON poll endpoint. */
    public function status(string $uuid)
    {
        $batch = $this->loadBatch($uuid);

        // A worker that died must not leave the page polling a frozen row
        // forever — close it out here, the way the single-page wizard does.
        if ($this->runner->reapStalled($batch)) {
            $batch = $this->loadBatch($uuid);
        }

        return $this->ok($batch);
    }

    public function cancel(string $uuid)
    {
        $batch = $this->loadBatch($uuid);

        $this->runner->cancel($batch);

        return $this->ok($this->loadBatch($uuid), 'أُلغيت الدفعة. المواضيع التي لم تبدأ بعد لن تُعالَج.');
    }

    /** Re-kick a batch whose queue worker went away while topics were waiting. */
    public function restart(string $uuid)
    {
        $batch = $this->loadBatch($uuid);

        if ($batch->isFinished()) {
            return $this->fail('اكتملت هذه الدفعة — لا يوجد ما يُعاد تشغيله.', 422);
        }

        $this->runner->reapStalled($batch);
        $this->runner->dispatchNext($batch->id);

        return $this->ok($this->loadBatch($uuid), 'أُعيد إرسال الدفعة إلى الطابور.');
    }

    public function resumeItem(string $uuid, DocumentationAiBatchItem $item)
    {
        $batch = $this->loadBatch($uuid);

        if ((int) $item->batch_id !== (int) $batch->id) {
            abort(404);
        }

        // This endpoint used to answer "الدفعة لا تزال قيد المعالجة" forever when
        // a worker had died mid-topic. Heal first, then decide.
        $this->runner->reapStalled($batch);
        $item->refresh();

        if ($item->status === DocumentationAiBatchItem::STATUS_COMPLETED) {
            return $this->fail('اكتمل توليد هذا الموضوع بالفعل.', 422);
        }

        if (in_array($item->status, DocumentationAiBatchItem::OPEN_STATUSES, true)) {
            return $this->fail('هذا الموضوع في الطابور بالفعل — انتظر حتى يبدأ التوليد.', 422);
        }

        if (! $this->runner->queueResume($item)) {
            return $this->fail(
                'لا يوجد تقدّم محفوظ لهذا الموضوع لمتابعته. أعد توليده من دفعة جديدة.',
                422
            );
        }

        return $this->ok(
            $this->loadBatch($uuid),
            'أُضيف «'.Str::limit($item->topic, 60).'» إلى الطابور — سيبدأ إكمال الأقسام الناقصة خلال لحظات.',
            ['resumed_item_ids' => [$item->id]]
        );
    }

    /** Queue every incomplete topic at once; the driver still runs them one by one. */
    public function resumeAll(string $uuid)
    {
        $batch = $this->loadBatch($uuid);
        $this->runner->reapStalled($batch);

        $resumed = [];

        foreach ($this->loadBatch($uuid)->items as $item) {
            if ($item->status !== DocumentationAiBatchItem::STATUS_FAILED) {
                continue;
            }

            if ($this->runner->queueResume($item)) {
                $resumed[] = $item->id;
            }
        }

        if ($resumed === []) {
            return $this->fail('لا توجد مواضيع غير مكتملة قابلة للمتابعة في هذه الدفعة.', 422);
        }

        return $this->ok(
            $this->loadBatch($uuid),
            'أُضيف '.count($resumed).' موضوعاً إلى الطابور — ستُعالَج تباعاً، موضوع واحد في كل مرة.',
            ['resumed_item_ids' => $resumed]
        );
    }

    /** Manually free a topic whose worker has gone silent. */
    public function releaseItem(string $uuid, DocumentationAiBatchItem $item)
    {
        $batch = $this->loadBatch($uuid);

        if ((int) $item->batch_id !== (int) $batch->id) {
            abort(404);
        }

        $item->refresh();

        if (! $this->runner->releaseStuckItem($item)) {
            return $this->fail(
                'لا يمكن تحرير هذا الموضوع الآن — المعالجة ما زالت نشطة. انتظر دقيقتين ثم أعد المحاولة.',
                422
            );
        }

        return $this->ok(
            $this->loadBatch($uuid),
            'تم تحرير الموضوع. الأقسام المكتملة محفوظة — اضغط «متابعة التوليد» لإكمال الناقص.'
        );
    }

    /**
     * One place that loads a batch with everything the status payload needs.
     *
     * The sections columns are pinned deliberately: the html column is LONGTEXT
     * and this relation is re-read every few seconds by the poller, so pulling
     * whole section bodies would push megabytes per request for no benefit.
     */
    private function loadBatch(string $uuid): DocumentationAiBatch
    {
        return DocumentationAiBatch::query()
            ->with([
                'items.generation',
                'items.generation.sections' => fn ($q) => $q->select(
                    'id', 'generation_id', 'position', 'heading', 'status'
                )->addSelect(DB::raw('LENGTH(html) as html_length')),
            ])
            ->where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    /**
     * The batch to put back on screen: an explicit ?batch=uuid when given,
     * otherwise the newest unfinished one, otherwise the newest recent one so a
     * finished-with-errors batch is still there to continue after a reload.
     */
    private function resolveRestorableBatch(?string $uuid): ?DocumentationAiBatch
    {
        $base = fn () => DocumentationAiBatch::query()
            ->with([
                'items.generation',
                'items.generation.sections' => fn ($q) => $q->select(
                    'id', 'generation_id', 'position', 'heading', 'status'
                )->addSelect(DB::raw('LENGTH(html) as html_length')),
            ])
            ->where('user_id', Auth::id());

        $batch = null;

        if ($uuid) {
            // Unknown or someone else's uuid falls through to the default pick
            // rather than erroring the whole page.
            $batch = $base()->where('uuid', $uuid)->first();
        }

        $batch ??= $base()
            ->whereIn('status', [DocumentationAiBatch::STATUS_QUEUED, DocumentationAiBatch::STATUS_RUNNING])
            ->latest()
            ->first();

        $batch ??= $base()
            ->where('created_at', '>=', now()->subDays(self::RESTORE_WINDOW_DAYS))
            ->latest()
            ->first();

        if (! $batch) {
            return null;
        }

        if ($this->runner->reapStalled($batch)) {
            $batch = $base()->where('uuid', $batch->uuid)->first();
        }

        return $batch;
    }

    private function incompleteItemsCountForUser(): int
    {
        return DocumentationAiBatchItem::query()
            ->whereHas('batch', fn ($q) => $q->where('user_id', Auth::id()))
            ->where('status', DocumentationAiBatchItem::STATUS_FAILED)
            ->whereHas('generation', fn ($q) => $q
                ->whereIn('status', [
                    DocumentationAiGeneration::STATUS_PAUSED,
                    DocumentationAiGeneration::STATUS_FAILED,
                ])
                ->whereHas('sections', fn ($s) => $s->where('status', '!=', DocumentationAiSection::STATUS_DONE))
            )
            ->count();
    }

    /** @param  array<string, mixed>  $extra */
    private function ok(DocumentationAiBatch $batch, ?string $message = null, array $extra = []): JsonResponse
    {
        return response()->json(array_merge([
            'success' => true,
            'message' => $message,
            'batch' => $batch->toStatusPayload(),
        ], $extra), 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
    }

    private function fail(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status, [], JSON_UNESCAPED_UNICODE);
    }
}
