<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\CleansUtf8AiResponse;
use App\Http\Controllers\Admin\Concerns\UsesLaravelAiSdkForWizards;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveDocumentationPageRequest;
use App\Models\AIModel;
use App\Models\DocumentationCategory;
use App\Models\DocumentationPage;
use App\Models\LaravelAiModel;
use App\Services\Ai\AIDocumentationPageService;
use App\Services\Ai\AIModelService;
use App\Services\AiNew\LaravelAiDocumentationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AIDocumentationPageController extends Controller
{
    use CleansUtf8AiResponse;
    use UsesLaravelAiSdkForWizards;

    public function __construct(
        private AIDocumentationPageService $docService,
        private AIModelService $modelService
    ) {}

    public function create(Request $request)
    {
        $categories = DocumentationCategory::active()->ordered()->get();
        $models = $this->modelService->getAvailableModels('all');
        $categoryId = $request->get('documentation_category_id');

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
        $laravelAiModels = $useLaravelAiEngine
            ? LaravelAiModel::query()->activeOrdered()->get()
            : collect();

        return view('admin.docs.pages.ai-create', compact(
            'categories',
            'models',
            'categoryId',
            'parentPagesJson',
            'useLaravelAiEngine',
            'laravelAiModels',
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
        $laravelAiModels = $useLaravelAiEngine
            ? LaravelAiModel::query()->activeOrdered()->get()
            : collect();

        return view('admin.docs.pages.ai-improve', compact(
            'models',
            'prefillPage',
            'categories',
            'categoryId',
            'parentPagesJson',
            'useLaravelAiEngine',
            'laravelAiModels',
        ));
    }

    public function refine(Request $request)
    {
        $validated = $request->validate([
            'source_html' => 'required|string|max:'.AIDocumentationPageService::MAX_REFINE_SOURCE_CHARS,
            'user_notes' => 'nullable|string|max:5000',
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'laravel_ai_model_id' => 'nullable|exists:laravel_ai_models,id',
            'tone' => 'nullable|in:professional,friendly,technical,casual,formal',
            'language' => 'nullable|in:ar,en',
            'update_excerpt' => 'boolean',
        ]);

        try {
            $refineOptions = [
                'user_notes' => $validated['user_notes'] ?? null,
                'tone' => $validated['tone'] ?? 'professional',
                'language' => $validated['language'] ?? 'ar',
                'update_excerpt' => $validated['update_excerpt'] ?? false,
            ];

            if ($this->wizardUsesLaravelAiSdk('docs_engine')) {
                $laraModel = null;
                if (! empty($validated['laravel_ai_model_id'])) {
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

                $data = app(LaravelAiDocumentationService::class)->refineForLegacy(
                    $validated['source_html'],
                    $refineOptions,
                    Auth::user(),
                    $laraModel,
                );
            } else {
                $model = $validated['ai_model_id']
                    ? AIModel::find($validated['ai_model_id'])
                    : $this->modelService->getDefaultModel();

                if (! $model) {
                    return response()->json([
                        'success' => false,
                        'message' => 'لا يوجد موديل AI متاح',
                    ], 400);
                }

                $data = $this->docService->refineDocumentationContent(
                    $validated['source_html'],
                    $model,
                    $refineOptions
                );
            }

            $data = $this->cleanUtf8Data($data);

            return response()->json([
                'success' => true,
                'data' => $data,
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('AI documentation refine: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'timeout') || str_contains($errorMessage, 'Timeout')) {
                $userMessage = 'انتهت مهلة الاتصال. جرّب تقسيم المحتوى أو تقليل الحجم.';
            } elseif (str_contains($errorMessage, 'API Key') || str_contains(strtolower($errorMessage), 'api key')) {
                $userMessage = 'مشكلة في API Key. يرجى التحقق من إعدادات الموديل.';
            } elseif (str_contains($errorMessage, 'quota') || str_contains($errorMessage, 'رصيد')) {
                $userMessage = 'رصيد الموديل غير كافٍ.';
            } else {
                $userMessage = 'حدث خطأ أثناء التحسين: '.$errorMessage;
            }

            return response()->json([
                'success' => false,
                'message' => $userMessage,
                'error_details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'topic' => 'required|string|max:500',
            'ai_model_id' => 'nullable|exists:ai_models,id',
            'laravel_ai_model_id' => 'nullable|exists:laravel_ai_models,id',
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

            $wizardOptions = [
                'content_length' => $validated['content_length'],
                'tone' => $validated['tone'] ?? 'professional',
                'language' => $validated['language'] ?? 'ar',
                'category' => $category,
                'parent' => $parent,
                'generate_meta' => $validated['generate_meta'] ?? true,
            ];

            if ($this->wizardUsesLaravelAiSdk('docs_engine')) {
                $laraModel = null;
                if (! empty($validated['laravel_ai_model_id'])) {
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

                $data = app(LaravelAiDocumentationService::class)->generateForLegacyWizard(
                    $validated['topic'],
                    $wizardOptions,
                    Auth::user(),
                    $laraModel,
                );
            } else {
                $model = $validated['ai_model_id']
                    ? AIModel::find($validated['ai_model_id'])
                    : $this->modelService->getDefaultModel();

                if (! $model) {
                    return response()->json([
                        'success' => false,
                        'message' => 'لا يوجد موديل AI متاح',
                    ], 400);
                }

                $data = $this->docService->generateDocumentationPage(
                    $validated['topic'],
                    $model,
                    $wizardOptions
                );
            }

            $data = $this->cleanUtf8Data($data);

            return response()->json([
                'success' => true,
                'data' => $data,
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
        } catch (\Exception $e) {
            Log::error('AI documentation generate: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'validated' => $validated,
            ]);

            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'timeout') || str_contains($errorMessage, 'Timeout')) {
                $userMessage = 'انتهت مهلة الاتصال. يرجى المحاولة مرة أخرى أو تقليل طول المحتوى المطلوب.';
            } elseif (str_contains($errorMessage, 'API Key') || str_contains(strtolower($errorMessage), 'api key')) {
                $userMessage = 'مشكلة في API Key. يرجى التحقق من إعدادات الموديل.';
            } elseif (str_contains($errorMessage, 'quota') || str_contains($errorMessage, 'رصيد')) {
                $userMessage = 'رصيد الموديل غير كافٍ. يرجى التحقق من رصيدك.';
            } else {
                $userMessage = 'حدث خطأ أثناء التوليد: '.$errorMessage;
            }

            return response()->json([
                'success' => false,
                'message' => $userMessage,
                'error_details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
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
