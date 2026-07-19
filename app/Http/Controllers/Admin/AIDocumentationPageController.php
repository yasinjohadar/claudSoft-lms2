<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CleansUtf8AiResponse;
use App\Http\Controllers\Admin\Concerns\UsesLaravelAiSdkForWizards;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveDocumentationPageRequest;
use App\Models\DocumentationCategory;
use App\Models\DocumentationPage;
use App\Models\LaravelAiModel;
use App\Models\DocumentationAiGeneration;
use App\Services\Ai\AIDocumentationPageService;
use App\Services\Ai\AIModelService;
use App\Services\AiNew\DocumentationAiJobStarter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AIDocumentationPageController extends Controller
{
    use CleansUtf8AiResponse;
    use UsesLaravelAiSdkForWizards;

    public function __construct(
        private AIDocumentationPageService $docService,
        private AIModelService $modelService,
        private DocumentationAiJobStarter $jobStarter,
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

        return view('admin.docs.pages.ai-create', compact(
            'categories',
            'models',
            'categoryId',
            'defaultPublishedAt',
            'parentPagesJson',
            'useLaravelAiEngine',
            'laravelAiModels',
            'docsEngineChoiceAvailable',
        ));
    }

    public function improve(Request $request)
    {
        $models = $this->modelService->getAvailableModels('all');
        $prefillPage = null;
        if ($request->filled('documentation_page_id')) {
            $prefillPage = DocumentationPage::query()->find((int) $request->get('documentation_page_id'));
        }

        $categories = DocumentationCategory::active()->ordered()->get();
        $categoryId = $prefillPage?->documentation_category_id ?? $request->get('documentation_category_id');

        $parentPages = DocumentationPage::with('category')
            ->orderBy('documentation_category_id')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title', 'documentation_category_id']);

        $forbiddenParentIds = $this->documentationPageIdsForbiddenAsParent($prefillPage);

        $parentPagesJson = $parentPages
            ->filter(fn (DocumentationPage $p) => ! in_array((int) $p->id, $forbiddenParentIds, true))
            ->map(fn (DocumentationPage $p) => [
                'id' => $p->id,
                'category_id' => $p->documentation_category_id,
                'label' => ($p->category->name ?? '—').' — '.$p->title,
            ])->values()->all();

        $useLaravelAiEngine = $this->wizardUsesLaravelAiSdk('docs_engine');
        $laravelAiModels = LaravelAiModel::query()->activeOrdered()->get();
        $docsEngineChoiceAvailable = $models->isNotEmpty() && $laravelAiModels->isNotEmpty();

        return view('admin.docs.pages.ai-improve', compact(
            'models',
            'prefillPage',
            'categories',
            'categoryId',
            'parentPagesJson',
            'useLaravelAiEngine',
            'laravelAiModels',
            'docsEngineChoiceAvailable',
        ));
    }

    public function enhance(Request $request)
    {
        $models = $this->modelService->getAvailableModels('all');
        $prefillPage = null;

        if ($request->filled('documentation_page_id')) {
            $prefillPage = DocumentationPage::query()
                ->with('category')
                ->find((int) $request->get('documentation_page_id'));
        }

        $allPages = DocumentationPage::query()
            ->with('category:id,name')
            ->orderByDesc('updated_at')
            ->get();

        $pagesJson = $allPages->map(fn (DocumentationPage $p) => [
            'id' => $p->id,
            'title' => $p->title,
            'slug' => $p->slug,
            'status' => $p->status,
            'category_name' => $p->category->name ?? '—',
            'category_id' => $p->documentation_category_id,
            'parent_id' => $p->parent_id,
            'sort_order' => $p->sort_order,
            'published_at' => $p->published_at?->format('Y-m-d\TH:i'),
            'meta_title' => $p->meta_title,
            'meta_description' => $p->meta_description,
            'is_indexable' => (bool) $p->is_indexable,
            'excerpt' => $p->excerpt,
            'update_url' => route('admin.docs.pages.update', $p),
            'edit_url' => route('admin.docs.pages.edit', $p),
            'source_url' => route('admin.docs.pages.ai-source', $p),
        ])->values()->all();

        $useLaravelAiEngine = $this->wizardUsesLaravelAiSdk('docs_engine');
        $laravelAiModels = LaravelAiModel::query()->activeOrdered()->get();
        $docsEngineChoiceAvailable = $models->isNotEmpty() && $laravelAiModels->isNotEmpty();

        if ($laravelAiModels->isNotEmpty() && $models->isEmpty()) {
            $useLaravelAiEngine = true;
        } elseif ($models->isNotEmpty() && $laravelAiModels->isEmpty()) {
            $useLaravelAiEngine = false;
        }

        return view('admin.docs.pages.ai-enhance', compact(
            'models',
            'prefillPage',
            'pagesJson',
            'useLaravelAiEngine',
            'laravelAiModels',
            'docsEngineChoiceAvailable',
        ));
    }

    public function refine(Request $request)
    {
        $mode = $request->input('mode', 'refine');

        $validated = $request->validate([
            'source_html' => 'required|string|max:'.AIDocumentationPageService::MAX_REFINE_SOURCE_CHARS,
            'user_notes' => $mode === 'enhance'
                ? 'required|string|min:10|max:5000'
                : 'nullable|string|max:5000',
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'laravel_ai_model_id' => 'nullable|exists:laravel_ai_models,id',
            'docs_engine' => 'nullable|in:laravel_ai,legacy',
            'tone' => 'nullable|in:professional,friendly,technical,casual,formal',
            'language' => 'nullable|in:ar,en',
            'update_excerpt' => 'boolean',
            'mode' => 'nullable|in:refine,enhance',
        ]);

        $mode = $validated['mode'] ?? 'refine';
        $operation = $mode === 'enhance'
            ? DocumentationAiGeneration::OPERATION_ENHANCE
            : DocumentationAiGeneration::OPERATION_REFINE;

        try {
            $requestedEngine = $validated['docs_engine'] ?? null;
            if ($requestedEngine === 'laravel_ai' && ! LaravelAiModel::query()->where('is_active', true)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد موديل Laravel AI نشط. أضف موديلاً من لوحة «موديلات Laravel AI SDK» أو اختر المحرك القديم.',
                ], 400);
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
                    return response()->json([
                        'success' => false,
                        'message' => 'موديل Laravel AI المحدد غير متاح أو غير نشط.',
                    ], 400);
                }
            }

            $generation = $this->jobStarter->start(Auth::user(), $operation, [
                'source_html' => $validated['source_html'],
                'user_notes' => $validated['user_notes'] ?? null,
                'docs_engine' => $engine,
                'ai_model_id' => $validated['ai_model_id'] ?? null,
                'laravel_ai_model_id' => $validated['laravel_ai_model_id'] ?? null,
                'tone' => $validated['tone'] ?? 'professional',
                'language' => $validated['language'] ?? 'ar',
                'update_excerpt' => $validated['update_excerpt'] ?? false,
            ]);

            return response()->json([
                'success' => true,
                'async' => true,
                'job' => $generation->toStatusPayload(),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('AI documentation refine start: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'تعذر بدء المهمة: '.$e->getMessage(),
            ], 500);
        }
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:500',
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'laravel_ai_model_id' => 'nullable|exists:laravel_ai_models,id',
            'docs_engine' => 'nullable|in:laravel_ai,legacy',
            'content_length' => 'required|in:short,medium,long',
            'tone' => 'nullable|in:professional,friendly,technical,casual,formal',
            'language' => 'nullable|in:ar,en',
            'documentation_category_id' => 'required|exists:documentation_categories,id',
            'parent_id' => 'nullable|exists:documentation_pages,id',
            'generate_meta' => 'boolean',
        ]);

        try {
            $category = DocumentationCategory::find($validated['documentation_category_id']);
            $parent = ! empty($validated['parent_id'])
                ? DocumentationPage::find($validated['parent_id'])
                : null;

            if ($parent && (int) $parent->documentation_category_id !== (int) $category->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'صفحة الأب يجب أن تنتمي لنفس القسم المختار',
                ], 422);
            }

            $requestedEngine = $validated['docs_engine'] ?? null;
            if ($requestedEngine === 'laravel_ai' && ! LaravelAiModel::query()->where('is_active', true)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد موديل Laravel AI نشط. أضف موديلاً من لوحة «موديلات Laravel AI SDK» أو اختر المحرك القديم.',
                ], 400);
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
                    return response()->json([
                        'success' => false,
                        'message' => 'موديل Laravel AI المحدد غير متاح أو غير نشط.',
                    ], 400);
                }
            }

            $generation = $this->jobStarter->start(
                Auth::user(),
                DocumentationAiGeneration::OPERATION_GENERATE,
                [
                    'topic' => $validated['topic'],
                    'docs_engine' => $engine,
                    'ai_model_id' => $validated['ai_model_id'] ?? null,
                    'laravel_ai_model_id' => $validated['laravel_ai_model_id'] ?? null,
                    'content_length' => $validated['content_length'],
                    'tone' => $validated['tone'] ?? 'professional',
                    'language' => $validated['language'] ?? 'ar',
                    'documentation_category_id' => $validated['documentation_category_id'],
                    'parent_id' => $validated['parent_id'] ?? null,
                    'generate_meta' => $validated['generate_meta'] ?? true,
                ]
            );

            return response()->json([
                'success' => true,
                'async' => true,
                'job' => $generation->toStatusPayload(),
            ]);
        } catch (\Exception $e) {
            Log::error('AI documentation generate start: '.$e->getMessage(), [
                'validated' => $validated,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'تعذر بدء التوليد: '.$e->getMessage(),
            ], 500);
        }
    }

    public function jobStatus(string $uuid)
    {
        $generation = DocumentationAiGeneration::query()
            ->where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'job' => $generation->toStatusPayload(),
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
    }

    public function store(SaveDocumentationPageRequest $request)
    {
        $validated = $request->validated();
        $validated['updated_by'] = Auth::id();
        $validated['is_indexable'] = $request->boolean('is_indexable', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if (($validated['status'] ?? '') === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $page = DocumentationPage::create($validated);

        return redirect()->route('admin.docs.pages.edit', $page)
            ->with('success', 'تم إنشاء صفحة التوثيق بالذكاء الاصطناعي. يمكنك المراجعة والتعديل.');
    }

    /**
     * الصفحة نفسها وكل الفروع لا يمكن اختيارها كأب عند التحديث.
     *
     * @return array<int>
     */
    private function documentationPageIdsForbiddenAsParent(?DocumentationPage $page): array
    {
        if (! $page) {
            return [];
        }

        $all = DocumentationPage::query()->get(['id', 'parent_id']);
        $childrenByParent = [];
        foreach ($all as $p) {
            if ($p->parent_id) {
                $pid = (int) $p->parent_id;
                $childrenByParent[$pid][] = (int) $p->id;
            }
        }

        $forbidden = [(int) $page->id];
        $queue = [(int) $page->id];
        while ($queue !== []) {
            $id = array_shift($queue);
            foreach ($childrenByParent[$id] ?? [] as $childId) {
                $forbidden[] = $childId;
                $queue[] = $childId;
            }
        }

        return array_values(array_unique($forbidden));
    }
}
