<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentationCategory;
use App\Models\DocumentationPage;
use App\Models\DocumentationResearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DocumentationResearchController extends Controller
{
    public function index(Request $request)
    {
        $query = DocumentationResearch::withCount('pages')->with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('documentation_category_id')) {
            $query->where('documentation_category_id', $request->documentation_category_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        $researches = $query->ordered()->paginate(25);
        $categories = DocumentationCategory::active()->ordered()->get();

        $stats = [
            'total' => DocumentationResearch::count(),
            'active' => DocumentationResearch::where('is_active', true)->count(),
            'assigned_pages' => DocumentationPage::whereNotNull('documentation_research_id')->count(),
            'unassigned_pages' => DocumentationPage::whereNull('documentation_research_id')->count(),
        ];

        return view('admin.docs.researches.index', compact('researches', 'categories', 'stats'));
    }

    public function create(Request $request)
    {
        $categories = DocumentationCategory::active()->ordered()->get();
        $categoryId = $request->get('documentation_category_id');

        return view('admin.docs.researches.create', compact('categories', 'categoryId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());
        $validated['is_active'] = $request->boolean('is_active');

        $validated['slug'] = $this->uniqueSlug(
            (int) $validated['documentation_category_id'],
            $validated['slug'] ?? null,
            $validated['name']
        );

        if (! isset($validated['sort_order']) || $validated['sort_order'] === null) {
            $validated['sort_order'] = (int) (DocumentationResearch::query()
                ->where('documentation_category_id', $validated['documentation_category_id'])
                ->max('sort_order') ?? 0) + 1;
        }

        $research = DocumentationResearch::create($validated);

        return redirect()->route('admin.docs.researches.show', $research)
            ->with('success', 'تم إنشاء البحث بنجاح');
    }

    /** The page the admin asked for: every documentation page inside one research. */
    public function show(Request $request, DocumentationResearch $documentation_research)
    {
        $research = $documentation_research->loadMissing('category');

        $baseQuery = DocumentationPage::query()
            ->where('documentation_research_id', $research->id);

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'published' => (clone $baseQuery)->where('status', 'published')->count(),
            'draft' => (clone $baseQuery)->where('status', 'draft')->count(),
            'last_updated' => (clone $baseQuery)->max('updated_at'),
        ];

        $pagesQuery = (clone $baseQuery)->with('parent');

        if ($request->filled('search')) {
            $search = $request->search;
            $pagesQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $pagesQuery->where('status', $request->status);
        }

        // No pagination: drag-and-drop needs the whole ordered list in one DOM.
        $pages = $pagesQuery->orderBy('research_sort_order')->orderBy('title')->get();

        // Filtering hides rows, so a drag would persist a misleading order —
        // the view disables dragging while a filter is active.
        $isFiltered = $request->filled('search') || $request->filled('status');

        $unassignedCount = DocumentationPage::query()
            ->where('documentation_category_id', $research->documentation_category_id)
            ->whereNull('documentation_research_id')
            ->count();

        return view('admin.docs.researches.show', compact(
            'research', 'pages', 'stats', 'isFiltered', 'unassignedCount'
        ));
    }

    public function edit(DocumentationResearch $documentation_research)
    {
        $research = $documentation_research;
        $categories = DocumentationCategory::active()->ordered()->get();
        $hasPages = $research->pages()->exists();

        return view('admin.docs.researches.edit', compact('research', 'categories', 'hasPages'));
    }

    public function update(Request $request, DocumentationResearch $documentation_research)
    {
        $validated = $request->validate($this->rules());
        $validated['is_active'] = $request->boolean('is_active');

        $categoryChanged = (int) $validated['documentation_category_id']
            !== (int) $documentation_research->documentation_category_id;

        // Moving a research to another category would break the "page's research
        // must be in the page's category" rule for every page it holds.
        if ($categoryChanged && $documentation_research->pages()->exists()) {
            return back()->withInput()->with(
                'error',
                'لا يمكن نقل بحث يحتوي على صفحات إلى قسم آخر. أزل الصفحات من البحث أولاً.'
            );
        }

        $validated['slug'] = $this->uniqueSlug(
            (int) $validated['documentation_category_id'],
            $validated['slug'] ?? null,
            $validated['name'],
            $documentation_research->id
        );

        $validated['sort_order'] = $validated['sort_order'] ?? $documentation_research->sort_order;

        $documentation_research->update($validated);

        return redirect()->route('admin.docs.researches.show', $documentation_research)
            ->with('success', 'تم حفظ التعديلات');
    }

    public function destroy(DocumentationResearch $documentation_research)
    {
        // This model soft-deletes, so the nullOnDelete foreign key never fires —
        // detach the pages explicitly or they keep pointing at an invisible row.
        DB::transaction(function () use ($documentation_research) {
            // toBase(): detaching is not an edit to the page, so its real
            // updated_at must survive.
            DocumentationPage::query()
                ->where('documentation_research_id', $documentation_research->id)
                ->toBase()
                ->update(['documentation_research_id' => null, 'research_sort_order' => 0]);

            $documentation_research->delete();
        });

        return redirect()->route('admin.docs.researches.index')
            ->with('success', 'تم حذف البحث. الصفحات التي كانت بداخله لم تُحذف — أصبحت بلا بحث.');
    }

    public function toggleActive(DocumentationResearch $documentation_research)
    {
        $documentation_research->update(['is_active' => ! $documentation_research->is_active]);

        return back()->with('success', $documentation_research->is_active ? 'تم تفعيل البحث' : 'تم تعطيل البحث');
    }

    /** Persist the drag-and-drop order into research_sort_order. */
    public function reorder(Request $request, DocumentationResearch $documentation_research): JsonResponse
    {
        $validated = $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'required|integer|distinct|exists:documentation_pages,id',
        ]);

        $ids = array_map('intval', $validated['orders']);

        // A crafted payload must not renumber pages belonging to another research.
        $owned = DocumentationPage::query()
            ->where('documentation_research_id', $documentation_research->id)
            ->whereIn('id', $ids)
            ->count();

        if ($owned !== count($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'بعض الصفحات لا تنتمي لهذا البحث',
            ], 422, [], JSON_UNESCAPED_UNICODE);
        }

        DB::transaction(function () use ($ids) {
            foreach ($ids as $index => $id) {
                // Query builder on purpose: an Eloquent update() would bump
                // updated_at, and the main pages list is sorted by it — one drag
                // would jump every reordered page to the top of that list.
                DB::table('documentation_pages')
                    ->where('id', $id)
                    ->update(['research_sort_order' => $index + 1]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الترتيب بنجاح',
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /** @return array<string, mixed> */
    private function rules(): array
    {
        return [
            'documentation_category_id' => 'required|exists:documentation_categories,id',
            'name' => 'required|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[\p{Arabic}a-zA-Z0-9\s-]+$/u'],
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Slug unique within its category. withTrashed() matters here: a soft-deleted
     * research still holds its slug, and handing it to a new row would make the
     * old one impossible to restore cleanly.
     */
    private function uniqueSlug(int $categoryId, ?string $slug, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug !== null && $slug !== '' ? $slug : $name);
        if ($base === '') {
            $base = 'research-'.time();
        }

        $candidate = $base;
        $suffix = 1;

        while (DocumentationResearch::withTrashed()
            ->where('documentation_category_id', $categoryId)
            ->where('slug', $candidate)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $candidate = $base.'-'.$suffix++;
        }

        return $candidate;
    }
}
