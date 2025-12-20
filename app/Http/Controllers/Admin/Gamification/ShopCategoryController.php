<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Gamification\ShopCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShopCategoryController extends Controller
{
    /**
     * عرض قائمة فئات المتجر
     */
    public function index(Request $request)
    {
        $query = ShopCategory::withCount('items');

        // البحث
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        // فلترة حسب الحالة
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $categories = $query->orderBy('sort_order')
            ->paginate(20);

        return view('admin.pages.gamification.shop.categories', compact('categories'));
    }

    /**
     * حفظ فئة جديدة
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $category = ShopCategory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الفئة بنجاح!',
            'category' => $category,
        ], 201);
    }

    /**
     * عرض تفاصيل فئة
     */
    public function show(ShopCategory $shopCategory)
    {
        $shopCategory->loadCount(['items', 'items as available_items_count' => function($q) {
            $q->where('is_available', true);
        }]);

        $shopCategory->load(['items' => function($q) {
            $q->orderBy('sort_order')->limit(10);
        }]);

        return response()->json([
            'success' => true,
            'category' => $shopCategory,
        ]);
    }

    /**
     * تحديث فئة
     */
    public function update(Request $request, ShopCategory $shopCategory)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $shopCategory->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الفئة بنجاح!',
            'category' => $shopCategory->fresh(),
        ]);
    }

    /**
     * حذف فئة
     */
    public function destroy(ShopCategory $shopCategory)
    {
        // التحقق من عدم وجود عناصر في الفئة
        if ($shopCategory->items()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف الفئة. يوجد عناصر مرتبطة بها.',
            ], 400);
        }

        $shopCategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الفئة بنجاح!',
        ]);
    }

    /**
     * تفعيل/تعطيل فئة
     */
    public function toggleActive(ShopCategory $shopCategory)
    {
        $shopCategory->update([
            'is_active' => !$shopCategory->is_active,
        ]);

        $status = $shopCategory->is_active ? 'تفعيل' : 'تعطيل';

        return response()->json([
            'success' => true,
            'message' => "تم {$status} الفئة بنجاح!",
            'category' => $shopCategory,
        ]);
    }
}
