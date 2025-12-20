<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = CourseCategory::with('parent')
            ->withCount('children')
            ->orderBy('order')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.pages.course-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentCategories = CourseCategory::whereNull('parent_id')
            ->active()
            ->orderBy('name')
            ->get();

        return view('admin.pages.course-categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:course_categories,name',
            'slug' => 'nullable|string|max:255|unique:course_categories,slug',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'order' => 'nullable|integer|min:0',
            'parent_id' => 'nullable|exists:course_categories,id',
        ], [
            'name.required' => 'اسم التصنيف مطلوب',
            'name.unique' => 'اسم التصنيف موجود بالفعل',
            'slug.unique' => 'المعرف موجود بالفعل',
            'image.image' => 'يجب أن يكون الملف صورة',
            'image.mimes' => 'نوع الصورة غير مدعوم',
            'image.max' => 'حجم الصورة يجب أن يكون أقل من 2 ميجابايت',
            'parent_id.exists' => 'التصنيف الأب غير موجود',
        ]);

        try {
            DB::beginTransaction();

            $data = [
                'name' => $request->name,
                'slug' => $request->slug ?: Str::slug($request->name),
                'description' => $request->description,
                'icon' => $request->icon,
                'color' => $request->color ?: '#0d6efd',
                'order' => $request->order ?: 0,
                'is_active' => $request->boolean('is_active'),
                'parent_id' => $request->parent_id,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . Str::slug($request->name) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('course-categories', $imageName, 'public');
                $data['image'] = $imagePath;
            }

            $category = CourseCategory::create($data);

            DB::commit();

            return redirect()
                ->route('course-categories.index')
                ->with('success', 'تم إنشاء التصنيف بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء التصنيف: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = CourseCategory::with(['parent', 'children'])
            ->findOrFail($id);

        return view('admin.pages.course-categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = CourseCategory::findOrFail($id);

        $parentCategories = CourseCategory::whereNull('parent_id')
            ->where('id', '!=', $id) // استبعاد التصنيف نفسه
            ->active()
            ->orderBy('name')
            ->get();

        return view('admin.pages.course-categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = CourseCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:course_categories,name,' . $id,
            'slug' => 'nullable|string|max:255|unique:course_categories,slug,' . $id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'order' => 'nullable|integer|min:0',
            'parent_id' => 'nullable|exists:course_categories,id',
        ], [
            'name.required' => 'اسم التصنيف مطلوب',
            'name.unique' => 'اسم التصنيف موجود بالفعل',
            'slug.unique' => 'المعرف موجود بالفعل',
            'image.image' => 'يجب أن يكون الملف صورة',
            'image.mimes' => 'نوع الصورة غير مدعوم',
            'image.max' => 'حجم الصورة يجب أن يكون أقل من 2 ميجابايت',
            'parent_id.exists' => 'التصنيف الأب غير موجود',
        ]);

        try {
            DB::beginTransaction();

            $data = [
                'name' => $request->name,
                'slug' => $request->slug ?: Str::slug($request->name),
                'description' => $request->description,
                'icon' => $request->icon,
                'color' => $request->color ?: '#0d6efd',
                'order' => $request->order ?: 0,
                'is_active' => $request->boolean('is_active'),
                'parent_id' => $request->parent_id,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($category->image && Storage::disk('public')->exists($category->image)) {
                    Storage::disk('public')->delete($category->image);
                }

                $image = $request->file('image');
                $imageName = time() . '_' . Str::slug($request->name) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('course-categories', $imageName, 'public');
                $data['image'] = $imagePath;
            }

            $category->update($data);

            DB::commit();

            return redirect()
                ->route('course-categories.index')
                ->with('success', 'تم تحديث التصنيف بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث التصنيف: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $category = CourseCategory::findOrFail($id);

            // Check if category has children
            if ($category->hasChildren()) {
                return redirect()
                    ->back()
                    ->with('error', 'لا يمكن حذف التصنيف لأنه يحتوي على تصنيفات فرعية');
            }

            // Delete image if exists
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            $category->delete();

            return redirect()
                ->route('course-categories.index')
                ->with('success', 'تم حذف التصنيف بنجاح');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء حذف التصنيف: ' . $e->getMessage());
        }
    }

    /**
     * Restore a soft deleted category.
     */
    public function restore(string $id)
    {
        try {
            $category = CourseCategory::withTrashed()->findOrFail($id);
            $category->restore();

            return redirect()
                ->route('course-categories.index')
                ->with('success', 'تم استعادة التصنيف بنجاح');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء استعادة التصنيف: ' . $e->getMessage());
        }
    }

    /**
     * Force delete a soft deleted category.
     */
    public function forceDelete(string $id)
    {
        try {
            $category = CourseCategory::withTrashed()->findOrFail($id);

            // Delete image if exists
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            $category->forceDelete();

            return redirect()
                ->route('course-categories.index')
                ->with('success', 'تم حذف التصنيف نهائياً');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء حذف التصنيف: ' . $e->getMessage());
        }
    }
}
