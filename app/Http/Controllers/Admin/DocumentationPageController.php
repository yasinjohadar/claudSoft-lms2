<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveDocumentationPageRequest;
use App\Models\Course;
use App\Models\DocumentationCategory;
use App\Models\DocumentationPage;
use App\Services\Documentation\DocumentationPdfExportService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DocumentationPageController extends Controller
{
    public function index(Request $request)
    {
        $pages = $this->paginatedDocumentationPages($request);
        $categories = DocumentationCategory::ordered()->get();
        $allCourses = Course::query()->select('id', 'title')->orderBy('title')->get();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'tbody_html' => view('admin.docs.pages.partials.table-rows', compact('pages'))->render(),
                'pagination_html' => view('admin.docs.pages.partials.pagination', compact('pages'))->render(),
                'total' => $pages->total(),
                'current_page' => $pages->currentPage(),
            ]);
        }

        $stats = [
            'total' => DocumentationPage::count(),
            'published' => DocumentationPage::where('status', 'published')->count(),
            'draft' => DocumentationPage::where('status', 'draft')->count(),
            'categories' => DocumentationCategory::count(),
        ];

        return view('admin.docs.pages.index', compact('pages', 'categories', 'allCourses', 'stats'));
    }

    private function paginatedDocumentationPages(Request $request): LengthAwarePaginator
    {
        $query = DocumentationPage::with(['category', 'parent'])->orderByDesc('updated_at');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                    ->orWhere('slug', 'like', "%{$s}%");
            });
        }

        if ($request->filled('documentation_category_id')) {
            $query->where('documentation_category_id', $request->documentation_category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->paginate(25)->withQueryString();
    }

    public function create(Request $request)
    {
        $categories = DocumentationCategory::active()->ordered()->get();
        $categoryId = $request->get('documentation_category_id');
        $parentOptions = $this->flatParentOptionsForCreate();

        return view('admin.docs.pages.create', compact('categories', 'parentOptions', 'categoryId'));
    }

    public function store(SaveDocumentationPageRequest $request)
    {
        $validated = $request->validated();

        $validated['updated_by'] = $request->user()->id;
        $validated['is_indexable'] = $request->boolean('is_indexable', true);

        if (($validated['status'] ?? '') === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $page = DocumentationPage::create($validated);

        return redirect()->route('admin.docs.pages.edit', $page)
            ->with('success', 'تم إنشاء صفحة التوثيق');
    }

    public function edit(DocumentationPage $documentation_page)
    {
        $documentation_page->load(['category']);
        $categories = DocumentationCategory::active()->ordered()->get();
        $parentOptions = $this->parentPageOptions(
            $documentation_page->documentation_category_id,
            $documentation_page->id
        );

        return view('admin.docs.pages.edit', compact('documentation_page', 'categories', 'parentOptions'));
    }

    public function update(SaveDocumentationPageRequest $request, DocumentationPage $documentation_page)
    {
        $validated = $request->validated();

        $validated['updated_by'] = $request->user()->id;
        $validated['is_indexable'] = $request->boolean('is_indexable', true);

        if (($validated['status'] ?? '') === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = $documentation_page->published_at ?? now();
        }

        $documentation_page->update($validated);

        return redirect()->route('admin.docs.pages.edit', $documentation_page)
            ->with('success', 'تم حفظ التعديلات');
    }

    public function destroy(DocumentationPage $documentation_page)
    {
        if ($documentation_page->children()->exists()) {
            return back()->with('error', 'لا يمكن الحذف: توجد صفحات فرعية. انقلها أو احذفها أولاً.');
        }

        $documentation_page->delete();

        return redirect()->route('admin.docs.pages.index')
            ->with('success', 'تم حذف الصفحة');
    }

    public function togglePublish(DocumentationPage $documentation_page)
    {
        if ($documentation_page->status === 'published') {
            $documentation_page->update(['status' => 'draft']);
            $message = 'تم إلغاء نشر الصفحة';
        } else {
            $documentation_page->update([
                'status' => 'published',
                'published_at' => $documentation_page->published_at ?? now(),
            ]);
            $message = 'تم نشر الصفحة';
        }

        return back()->with('success', $message);
    }

    private function flatParentOptionsForCreate(): array
    {
        $pages = DocumentationPage::with('category')->orderBy('documentation_category_id')->orderBy('sort_order')->orderBy('title')->get();
        $options = [];
        foreach ($pages as $p) {
            $cat = $p->category->name ?? '—';
            $options[$p->id] = $cat.' — '.$p->title;
        }

        return $options;
    }

    private function parentPageOptions(?int $categoryId, ?int $excludeId = null): array
    {
        if (! $categoryId) {
            return [];
        }

        $pages = DocumentationPage::where('documentation_category_id', $categoryId)
            ->ordered()
            ->get(['id', 'title', 'parent_id']);

        $options = [];
        foreach ($pages as $p) {
            if ($excludeId !== null && (int) $p->id === $excludeId) {
                continue;
            }
            if ($excludeId !== null && $this->isUnderPage($pages, $excludeId, (int) $p->id)) {
                continue;
            }
            $depth = $this->pageDepth($pages, $p);
            $prefix = str_repeat('— ', $depth);
            $options[$p->id] = $prefix.$p->title;
        }

        return $options;
    }

    private function pageDepth($collection, DocumentationPage $page): int
    {
        $d = 0;
        $current = $page;
        while ($current->parent_id) {
            $d++;
            $current = $collection->firstWhere('id', $current->parent_id);
            if (! $current || $d > 50) {
                break;
            }
        }

        return $d;
    }

    /** هل pageId تحت شجرة ancestorId */
    private function isUnderPage($collection, int $ancestorId, int $candidateId): bool
    {
        $current = $collection->firstWhere('id', $candidateId);
        while ($current) {
            if ((int) $current->id === $ancestorId) {
                return true;
            }
            if (! $current->parent_id) {
                return false;
            }
            $current = $collection->firstWhere('id', $current->parent_id);
        }

        return false;
    }

    public function exportPdf(
        DocumentationPage $documentation_page,
        DocumentationPdfExportService $pdfExportService
    ): Response|RedirectResponse {
        set_time_limit((int) config('browsershot.timeout', 120) + 30);

        $documentation_page->loadMissing('category');

        if (! $documentation_page->category) {
            return redirect()
                ->back()
                ->with('error', 'لا يمكن تصدير صفحة بدون قسم.');
        }

        try {
            $pdf = $pdfExportService->export($documentation_page, allowDraft: true);

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$pdfExportService->filename($documentation_page).'"',
                'Content-Length' => (string) strlen($pdf),
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ]);
        } catch (\Throwable $e) {
            report($e);

            $message = $e->getMessage() ?: 'تعذّر تصدير PDF.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 500);
            }

            abort(500, $message);
        }
    }

    public function aiSourceJson(DocumentationPage $documentation_page)
    {
        $documentation_page->loadMissing('category:id,name');

        return response()->json([
            'id' => $documentation_page->id,
            'title' => $documentation_page->title,
            'slug' => $documentation_page->slug,
            'status' => $documentation_page->status,
            'category_name' => $documentation_page->category->name ?? '—',
            'category_id' => $documentation_page->documentation_category_id,
            'parent_id' => $documentation_page->parent_id,
            'sort_order' => $documentation_page->sort_order,
            'published_at' => $documentation_page->published_at?->format('Y-m-d\TH:i'),
            'meta_title' => $documentation_page->meta_title,
            'meta_description' => $documentation_page->meta_description,
            'is_indexable' => (bool) $documentation_page->is_indexable,
            'excerpt' => $documentation_page->excerpt,
            'content' => $documentation_page->content,
            'update_url' => route('admin.docs.pages.update', $documentation_page),
        ]);
    }
}
