<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\UsesLaravelAiSdkForWizards;
use App\Http\Controllers\Controller;
use App\Models\DocumentationAiBatch;
use App\Models\DocumentationCategory;
use App\Models\DocumentationPage;
use App\Models\LaravelAiModel;
use App\Services\Ai\AIModelService;
use App\Services\AiNew\DocumentationAiBatchRunner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AIDocumentationBatchController extends Controller
{
    use UsesLaravelAiSdkForWizards;

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

        return view('admin.docs.pages.ai-batch-create', compact(
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
            return response()->json([
                'success' => false,
                'message' => 'يرجى إدخال موضوع واحد على الأقل.',
            ], 422);
        }

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

            return response()->json([
                'success' => true,
                'batch' => $batch->toStatusPayload(),
            ]);
        } catch (\Exception $e) {
            Log::error('AI documentation batch start: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'تعذر بدء الدفعة: '.$e->getMessage(),
            ], 500);
        }
    }

    public function status(string $uuid)
    {
        $batch = DocumentationAiBatch::query()
            ->with('items.generation.sections')
            ->where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'batch' => $batch->toStatusPayload(),
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
    }

    public function cancel(string $uuid)
    {
        $batch = DocumentationAiBatch::query()
            ->where('uuid', $uuid)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $this->runner->cancel($batch);

        return response()->json([
            'success' => true,
            'batch' => $batch->fresh('items')->toStatusPayload(),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
