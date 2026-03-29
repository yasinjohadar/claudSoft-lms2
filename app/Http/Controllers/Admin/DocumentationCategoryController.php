<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentationCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentationCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = DocumentationCategory::withCount('pages')->with('parent');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('parent')) {
            if ($request->parent === 'root') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent);
            }
        }

        if ($request->filled('kind')) {
            $query->where('kind', $request->kind);
        }

        $categories = $query->ordered()->paginate(25);
        $parentCategories = DocumentationCategory::whereNull('parent_id')->ordered()->get();

        return view('admin.docs.categories.index', compact('categories', 'parentCategories'));
    }

    public function create()
    {
        $parentCategories = DocumentationCategory::whereNull('parent_id')->ordered()->get();
        return view('admin.docs.categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:documentation_categories,slug',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:documentation_categories,id',
            'icon' => 'nullable|string|max:100',
            'kind' => 'required|in:section,technology',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        if (empty($validated['parent_id'])) {
            $validated['parent_id'] = null;
        }


        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        } else {
            $validated['slug'] = Str::slug($validated['slug']);
        }

        $base = $validated['slug'];
        $i = 1;
        while (DocumentationCategory::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $base . '-' . $i++;
        }

        if (! isset($validated['sort_order'])) {
            $validated['sort_order'] = (int) (DocumentationCategory::max('sort_order') ?? 0) + 1;
        }

        DocumentationCategory::create($validated);

        return redirect()->route('admin.docs.categories.index')
            ->with('success', 'تم إنشاء قسم التوثيق بنجاح');
    }

    public function edit(DocumentationCategory $documentation_category)
    {
        $parentCategories = DocumentationCategory::whereNull('parent_id')
            ->where('id', '!=', $documentation_category->id)
            ->ordered()
            ->get();

        return view('admin.docs.categories.edit', [
            'category' => $documentation_category,
            'parentCategories' => $parentCategories,
        ]);
    }

    public function update(Request $request, DocumentationCategory $documentation_category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:documentation_categories,slug,' . $documentation_category->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:documentation_categories,id',
            'icon' => 'nullable|string|max:100',
            'kind' => 'required|in:section,technology',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        if (empty($validated['parent_id'])) {
            $validated['parent_id'] = null;
        }


        if ($request->filled('slug')) {
            $validated['slug'] = Str::slug($validated['slug']);
        } else {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if (! empty($validated['parent_id']) && (int) $validated['parent_id'] === $documentation_category->id) {
            return back()->withInput()->with('error', 'لا يمكن أن يكون القسم أباً لنفسه');
        }

        $base = $validated['slug'];
        $i = 1;
        while (DocumentationCategory::where('slug', $validated['slug'])->where('id', '!=', $documentation_category->id)->exists()) {
            $validated['slug'] = $base . '-' . $i++;
        }

        $documentation_category->update($validated);

        return redirect()->route('admin.docs.categories.index')
            ->with('success', 'تم تحديث قسم التوثيق بنجاح');
    }

    public function destroy(DocumentationCategory $documentation_category)
    {
        if ($documentation_category->pages()->exists()) {
            return back()->with('error', 'لا يمكن الحذف: يوجد صفحات مرتبطة بهذا القسم');
        }
        if ($documentation_category->children()->exists()) {
            return back()->with('error', 'لا يمكن الحذف: يوجد أقسام فرعية');
        }

        $documentation_category->delete();

        return redirect()->route('admin.docs.categories.index')
            ->with('success', 'تم حذف القسم');
    }

    public function toggleActive(DocumentationCategory $documentation_category)
    {
        $documentation_category->update(['is_active' => ! $documentation_category->is_active]);

        return back()->with('success', 'تم تحديث حالة القسم');
    }
}
