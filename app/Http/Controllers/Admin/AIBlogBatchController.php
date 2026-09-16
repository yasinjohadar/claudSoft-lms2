<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\UsesLaravelAiSdkForWizards;
use App\Http\Controllers\Controller;
use App\Models\BlogAiBatch;
use App\Models\BlogAiBatchItem;
use App\Models\BlogAiGeneration;
use App\Models\BlogAiSection;
use App\Models\BlogCategory;
use App\Models\LaravelAiModel;
use App\Services\Ai\AIModelService;
use App\Services\AiNew\BlogAiBatchRunner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Batch article generation — the blog twin of AIDocumentationBatchController.
 *
 * The only behavioural difference is the save: every finished article is stored
 * as a draft, so there is no status/published_at choice to make here.
 */
class AIBlogBatchController extends Controller
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
        private BlogAiBatchRunner $runner,
    ) {}

    public function create(Request $request)
    {
        $categories = BlogCategory::orderBy('name')->get();
        $models = $this->modelService->getAvailableModels('all');
        $categoryId = $request->get('category_id') ?: $categories->first()?->id;

        $useLaravelAiEngine = $this->wizardUsesLaravelAiSdk('blog_engine');
        $laravelAiModels = LaravelAiModel::query()->activeOrdered()->get();
        $blogEngineChoiceAvailable = $models->isNotEmpty() && $laravelAiModels->isNotEmpty();

        // Everything about a batch lives in the database; the page just had no
        // way to find it again after a reload. Hand it back the live batch.
        $activeBatch = $this->resolveRestorableBatch($request->query('batch'));
        $initialBatch = $activeBatch?->toStatusPayload();
        $initialSettings = $activeBatch?->settings;
        $incompleteCount = $this->incompleteItemsCountForUser();

        return view('admin.blog.ai-posts.batch-create', compact(
            'categories',
            'models',
            'categoryId',
            'useLaravelAiEngine',
            'laravelAiModels',
            'blogEngineChoiceAvailable',
            'initialBatch',
            'initialSettings',
            'incompleteCount',
        ));
    }

    /** Batch history: every batch this admin has run, newest first. */
    public function index(Request $request)
    {
        // Heal anything the queue abandoned so the counts below are honest.
        BlogAiBatch::query()
            ->where('user_id', Auth::id())
            ->whereNotIn('status', [
                BlogAiBatch::STATUS_COMPLETED,
                BlogAiBatch::STATUS_CANCELLED,
            ])
            ->get()
            ->each(fn (BlogAiBatch $batch) => $this->runner->reapStalled($batch));

        $onlyIncomplete = $request->boolean('incomplete');

        $batches = BlogAiBatch::query()
            ->where('user_id', Auth::id())
            ->withCount([
                'items as completed_count' => fn ($q) => $q->where('status', BlogAiBatchItem::STATUS_COMPLETED),
                'items as failed_count' => fn ($q) => $q->where('status', BlogAiBatchItem::STATUS_FAILED),
                'items as open_count' => fn ($q) => $q->whereIn('status', BlogAiBatchItem::OPEN_STATUSES),
            ])
            ->when($onlyIncomplete, fn ($q) => $q->having('failed_count', '>', 0))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totals = [
            'batches' => BlogAiBatch::query()->where('user_id', Auth::id())->count(),
            'incomplete_items' => $this->incompleteItemsCountForUser(),
        ];

        return view('admin.blog.ai-posts.batch-index', compact('batches', 'totals', 'onlyIncomplete'));
    }

    /** Live detail page for one batch — the table is filled by the same poller. */
    public function show(string $uuid)
    {
        $batch = $this->loadBatch($uuid);
        $this->runner->reapStalled($batch);
        $batch = $this->loadBatch($uuid);

        $initialBatch = $batch->toStatusPayload();
        $category = BlogCategory::find($batch->settings['category_id'] ?? null);

        return view('admin.blog.ai-posts.batch-show', compact('batch', 'initialBatch', 'category'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'topics' => 'required|array|min:1|max:30',
            'topics.*' => 'required|string|max:3000',
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'laravel_ai_model_id' => 'nullable|exists:laravel_ai_models,id',
            'blog_engine' => 'nullable|in:laravel_ai,legacy',
            'content_length' => 'required|in:short,medium,long,very_long',
            'tone' => 'nullable|in:professional,friendly,technical,casual,formal',
            'language' => 'nullable|in:ar,en',
            'category_id' => 'required|exists:blog_categories,id',
            'generate_seo' => 'boolean',
            'generate_og' => 'boolean',
            'generate_twitter' => 'boolean',
            'generate_schema' => 'boolean',
            'generate_keyword_synonyms' => 'boolean',
            'generate_tags' => 'boolean',
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
            $requestedEngine = $validated['blog_engine'] ?? null;
            if ($requestedEngine === 'laravel_ai' && ! LaravelAiModel::query()->where('is_active', true)->exists()) {
                return $this->fail(
                    'لا يوجد موديل Laravel AI نشط. أضف موديلاً من لوحة «موديلات Laravel AI SDK» أو اختر المحرك القديم.',
                    400
                );
            }

            $engine = $this->resolveWizardAiEngineWithModels(
                $requestedEngine,
                'blog_engine',
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
                'blog_engine' => $engine,
                'ai_model_id' => $validated['ai_model_id'] ?? null,
                'laravel_ai_model_id' => $validated['laravel_ai_model_id'] ?? null,
                'content_length' => $validated['content_length'],
                'tone' => $validated['tone'] ?? 'professional',
                'language' => $validated['language'] ?? 'ar',
                'category_id' => (int) $validated['category_id'],
                'generate_seo' => $request->boolean('generate_seo', true),
                'generate_og' => $request->boolean('generate_og', true),
                'generate_twitter' => $request->boolean('generate_twitter', true),
                'generate_schema' => $request->boolean('generate_schema', true),
                'generate_keyword_synonyms' => $request->boolean('generate_keyword_synonyms', true),
                'generate_tags' => $request->boolean('generate_tags', true),
            ];

            $batch = $this->runner->start(Auth::user(), $settings, $topics);

            return $this->ok($this->loadBatch($batch->uuid));
        } catch (\Exception $e) {
            Log::error('AI blog batch start: '.$e->getMessage());

            return $this->fail('تعذر بدء الدفعة: '.$e->getMessage(), 500);
        }
    }

    /** JSON poll endpoint. */
    public function status(string $uuid)
    {
        $batch = $this->loadBatch($uuid);

        // A worker that died must not leave the page polling a frozen row
        // forever — close it out here, the way the single-article wizard does.
        if ($this->runner->reapStalled($batch)) {
            $batch = $this->loadBatch($uuid);
        }

        return $this->ok($batch);
    }

    public function cancel(string $uuid)
    {
        $batch = $this->loadBatch($uuid);

        $this->runner->cancel($batch);

        return $this->ok($this->loadBatch($uuid), 'أُلغيت الدفعة. المقالات التي لم تبدأ بعد لن تُعالَج.');
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

    public function resumeItem(string $uuid, BlogAiBatchItem $item)
    {
        $batch = $this->loadBatch($uuid);

        if ((int) $item->batch_id !== (int) $batch->id) {
            abort(404);
        }

        // This endpoint used to answer "الدفعة لا تزال قيد المعالجة" forever when
        // a worker had died mid-topic. Heal first, then decide.
        $this->runner->reapStalled($batch);
        $item->refresh();

        if ($item->status === BlogAiBatchItem::STATUS_COMPLETED) {
            return $this->fail('اكتمل توليد هذا المقال بالفعل.', 422);
        }

        if (in_array($item->status, BlogAiBatchItem::OPEN_STATUSES, true)) {
            return $this->fail('هذا المقال في الطابور بالفعل — انتظر حتى يبدأ التوليد.', 422);
        }

        if (! $this->runner->queueResume($item)) {
            return $this->fail(
                'لا يوجد تقدّم محفوظ لهذا المقال لمتابعته. أعد توليده من دفعة جديدة.',
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
            if ($item->status !== BlogAiBatchItem::STATUS_FAILED) {
                continue;
            }

            if ($this->runner->queueResume($item)) {
                $resumed[] = $item->id;
            }
        }

        if ($resumed === []) {
            return $this->fail('لا توجد مقالات غير مكتملة قابلة للمتابعة في هذه الدفعة.', 422);
        }

        return $this->ok(
            $this->loadBatch($uuid),
            'أُضيف '.count($resumed).' مقالاً إلى الطابور — ستُعالَج تباعاً، مقال واحد في كل مرة.',
            ['resumed_item_ids' => $resumed]
        );
    }

    /** Manually free a topic whose worker has gone silent. */
    public function releaseItem(string $uuid, BlogAiBatchItem $item)
    {
        $batch = $this->loadBatch($uuid);

        if ((int) $item->batch_id !== (int) $batch->id) {
            abort(404);
        }

        $item->refresh();

        if (! $this->runner->releaseStuckItem($item)) {
            return $this->fail(
                'لا يمكن تحرير هذا المقال الآن — المعالجة ما زالت نشطة. انتظر دقيقتين ثم أعد المحاولة.',
                422
            );
        }

        return $this->ok(
            $this->loadBatch($uuid),
            'تم تحرير المقال. الأقسام المكتملة محفوظة — اضغط «متابعة التوليد» لإكمال الناقص.'
        );
    }

    /**
     * One place that loads a batch with everything the status payload needs.
     *
     * The sections columns are pinned deliberately: the html column is LONGTEXT
     * and this relation is re-read every few seconds by the poller, so pulling
     * whole section bodies would push megabytes per request for no benefit.
     */
    private function loadBatch(string $uuid): BlogAiBatch
    {
        return BlogAiBatch::query()
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
    private function resolveRestorableBatch(?string $uuid): ?BlogAiBatch
    {
        $base = fn () => BlogAiBatch::query()
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
            ->whereIn('status', [BlogAiBatch::STATUS_QUEUED, BlogAiBatch::STATUS_RUNNING])
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
        return BlogAiBatchItem::query()
            ->whereHas('batch', fn ($q) => $q->where('user_id', Auth::id()))
            ->where('status', BlogAiBatchItem::STATUS_FAILED)
            ->whereHas('generation', fn ($q) => $q
                ->whereIn('status', [
                    BlogAiGeneration::STATUS_PAUSED,
                    BlogAiGeneration::STATUS_FAILED,
                ])
                ->whereHas('sections', fn ($s) => $s->where('status', '!=', BlogAiSection::STATUS_DONE))
            )
            ->count();
    }

    /** @param  array<string, mixed>  $extra */
    private function ok(BlogAiBatch $batch, ?string $message = null, array $extra = []): JsonResponse
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
