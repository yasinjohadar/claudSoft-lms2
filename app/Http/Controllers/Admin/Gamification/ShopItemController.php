<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Gamification\ShopItem;
use App\Models\Gamification\ShopCategory;
use App\Services\Gamification\ShopService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShopItemController extends Controller
{
    protected ShopService $shopService;

    public function __construct(ShopService $shopService)
    {
        $this->shopService = $shopService;
    }

    /**
     * عرض قائمة العناصر
     */
    public function index(Request $request)
    {
        $query = ShopItem::with('category');

        // فلترة حسب الفئة
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // فلترة حسب النوع
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // فلترة حسب الحالة
        if ($request->filled('is_available')) {
            $query->where('is_available', $request->is_available);
        }

        // فلترة حسب المخزون
        if ($request->filled('in_stock')) {
            $query->where('in_stock', $request->in_stock);
        }

        // البحث
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $items = $query->orderBy('category_id')
            ->orderBy('sort_order')
            ->paginate(50);

        $categories = ShopCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('admin.pages.gamification.shop.items', compact('items', 'categories'));
    }

    /**
     * عرض نموذج إنشاء عنصر
     * ملاحظة: يتم الإنشاء عبر الـ index page مباشرة
     */
    public function create()
    {
        return redirect()->route('admin.gamification.shop.items.index')
            ->with('info', 'يمكنك إضافة العناصر مباشرة من صفحة القائمة');
    }

    /**
     * حفظ عنصر جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:shop_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|string|max:50',
            'icon' => 'nullable|string|max:10',
            'price_points' => 'nullable|integer|min:0',
            'price_gems' => 'nullable|integer|min:0',
            'required_level' => 'nullable|integer|min:1',
            'required_badge_id' => 'nullable|exists:badges,id',
            'duration_days' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'purchase_limit' => 'nullable|integer|min:1',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'discount_expires_at' => 'nullable|date',
            'is_available' => 'boolean',
            'in_stock' => 'boolean',
            'is_stackable' => 'boolean',
            'is_consumable' => 'boolean',
            'is_featured' => 'boolean',
            'effects' => 'nullable|array',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(6);

        $item = ShopItem::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء العنصر بنجاح!',
            'item' => $item->load('category'),
        ], 201);
    }

    /**
     * عرض تفاصيل عنصر
     */
    public function show(ShopItem $shopItem)
    {
        $shopItem->load('category', 'requiredBadge');

        // إحصائيات العنصر
        $stats = [
            'total_purchases' => $shopItem->total_purchases,
            'revenue' => [
                'points' => $shopItem->purchases()->where('payment_method', 'points')->sum('final_price'),
                'gems' => $shopItem->purchases()->where('payment_method', 'gems')->sum('final_price'),
            ],
            'recent_purchases' => $shopItem->purchases()
                ->with('user:id,name,email')
                ->latest('purchased_at')
                ->limit(10)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'item' => $shopItem,
            'stats' => $stats,
        ]);
    }

    /**
     * تحديث عنصر
     */
    public function update(Request $request, ShopItem $shopItem)
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|exists:shop_categories,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'type' => 'sometimes|string|max:50',
            'icon' => 'nullable|string|max:10',
            'price_points' => 'nullable|integer|min:0',
            'price_gems' => 'nullable|integer|min:0',
            'required_level' => 'nullable|integer|min:1',
            'required_badge_id' => 'nullable|exists:badges,id',
            'duration_days' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'purchase_limit' => 'nullable|integer|min:1',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'discount_expires_at' => 'nullable|date',
            'is_available' => 'boolean',
            'in_stock' => 'boolean',
            'is_stackable' => 'boolean',
            'is_consumable' => 'boolean',
            'is_featured' => 'boolean',
            'effects' => 'nullable|array',
            'sort_order' => 'nullable|integer',
        ]);

        $shopItem->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث العنصر بنجاح!',
            'item' => $shopItem->fresh(['category']),
        ]);
    }

    /**
     * حذف عنصر
     */
    public function destroy(ShopItem $shopItem)
    {
        // التحقق من عدم وجود مشتريات
        if ($shopItem->purchases()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف العنصر. يوجد مشتريات مرتبطة به.',
            ], 400);
        }

        $shopItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف العنصر بنجاح!',
        ]);
    }

    /**
     * تفعيل/تعطيل عنصر
     */
    public function toggleActive(ShopItem $shopItem)
    {
        $shopItem->update([
            'is_available' => !$shopItem->is_available,
        ]);

        $status = $shopItem->is_available ? 'تفعيل' : 'تعطيل';

        return response()->json([
            'success' => true,
            'message' => "تم {$status} العنصر بنجاح!",
            'item' => $shopItem,
        ]);
    }

    /**
     * تطبيق خصم على عنصر
     */
    public function applyDiscount(Request $request, ShopItem $shopItem)
    {
        $validated = $request->validate([
            'discount_percentage' => 'required|integer|min:1|max:100',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $success = $this->shopService->applyDiscount(
            $shopItem,
            $validated['discount_percentage'],
            $validated['expires_at'] ?? null
        );

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تطبيق الخصم.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تطبيق الخصم بنجاح!',
            'item' => $shopItem->fresh(),
        ]);
    }

    /**
     * إزالة الخصم
     */
    public function removeDiscount(ShopItem $shopItem)
    {
        $success = $this->shopService->removeDiscount($shopItem);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'فشل إزالة الخصم.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إزالة الخصم بنجاح!',
            'item' => $shopItem->fresh(),
        ]);
    }

    /**
     * تحديث المخزون
     */
    public function updateStock(Request $request, ShopItem $shopItem)
    {
        $validated = $request->validate([
            'stock_quantity' => 'required|integer|min:0',
            'in_stock' => 'boolean',
        ]);

        $shopItem->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المخزون بنجاح!',
            'item' => $shopItem,
        ]);
    }

    /**
     * إحصائيات المتجر
     */
    public function statistics()
    {
        $stats = $this->shopService->getShopStatistics();

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * العناصر الأكثر مبيعاً
     */
    public function topSelling(Request $request)
    {
        $limit = $request->input('limit', 10);

        $items = ShopItem::with('category')
            ->orderByDesc('total_purchases')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }

    /**
     * العناصر المميزة
     */
    public function featured()
    {
        $items = ShopItem::where('is_featured', true)
            ->where('is_available', true)
            ->with('category')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }
}
