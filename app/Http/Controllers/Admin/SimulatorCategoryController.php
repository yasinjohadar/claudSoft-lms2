<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SimulatorCategory;
use App\Services\Simulator\SimulatorCategoryTree;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SimulatorCategoryController extends Controller
{
    public function index(): View
    {
        $categories = SimulatorCategoryTree::flatList();

        return view('admin.lesson-simulators.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $parentOptions = SimulatorCategoryTree::optionsForSelect();

        return view('admin.lesson-simulators.categories.create', compact('parentOptions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCategory($request);

        SimulatorCategory::create($validated);

        return redirect()
            ->route('admin.lesson-simulators.categories.index')
            ->with('success', 'تم إنشاء التصنيف بنجاح.');
    }

    public function edit(SimulatorCategory $category): View
    {
        $parentOptions = SimulatorCategoryTree::optionsForSelect($category->id);

        return view('admin.lesson-simulators.categories.edit', compact('category', 'parentOptions'));
    }

    public function update(Request $request, SimulatorCategory $category): RedirectResponse
    {
        $validated = $this->validateCategory($request, $category->id);

        if (! empty($validated['parent_id']) && (int) $validated['parent_id'] === $category->id) {
            return back()->withInput()->withErrors(['parent_id' => 'لا يمكن أن يكون التصنيف أباً لنفسه.']);
        }

        $excludeIds = SimulatorCategoryTree::excludeIds($category->id);
        if (! empty($validated['parent_id']) && in_array((int) $validated['parent_id'], $excludeIds, true)) {
            return back()->withInput()->withErrors(['parent_id' => 'لا يمكن اختيار تصنيف فرعي كأب.']);
        }

        $category->update($validated);

        return redirect()
            ->route('admin.lesson-simulators.categories.index')
            ->with('success', 'تم تحديث التصنيف.');
    }

    public function destroy(SimulatorCategory $category): RedirectResponse
    {
        if ($category->hasChildren()) {
            return back()->with('error', 'لا يمكن حذف تصنيف يحتوي على تصنيفات فرعية.');
        }

        if ($category->simulators()->exists()) {
            return back()->with('error', 'لا يمكن حذف تصنيف مرتبط بمحاكيات — انقل المحاكيات أولاً.');
        }

        $category->delete();

        return redirect()
            ->route('admin.lesson-simulators.categories.index')
            ->with('success', 'تم حذف التصنيف.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCategory(Request $request, ?int $ignoreId = null): array
    {
        $slugRule = 'nullable|string|max:255|unique:simulator_categories,slug';
        if ($ignoreId) {
            $slugRule .= ','.$ignoreId;
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => $slugRule,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:simulator_categories,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'اسم التصنيف مطلوب',
            'parent_id.exists' => 'التصنيف الأب غير موجود',
        ]);

        $validated['slug'] = filled($validated['slug'] ?? null)
            ? $validated['slug']
            : Str::slug($validated['name']);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['parent_id'] = $validated['parent_id'] ?? null;

        return $validated;
    }
}
