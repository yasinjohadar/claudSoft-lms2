<?php

namespace App\Http\Controllers\Student\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Gamification\ShopItem;
use App\Models\Gamification\ShopCategory;
use App\Services\Gamification\ShopService;
use App\Services\Gamification\BoosterService;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    protected ShopService $shopService;
    protected BoosterService $boosterService;

    public function __construct(
        ShopService $shopService,
        BoosterService $boosterService
    ) {
        $this->shopService = $shopService;
        $this->boosterService = $boosterService;
    }

    /**
     * عرض المتجر
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $categoryId = $request->input('category_id');

        // الحصول على الفئات
        $categories = ShopCategory::where('is_active', true)
            ->withCount(['items' => function($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('sort_order')
            ->get();

        // الحصول على العناصر المتاحة
        $items = $this->shopService->getAvailableItems($user, $categoryId);

        // رصيد المستخدم
        $balance = [
            'points' => $user->stats->available_points ?? 0,
            'gems' => $user->stats->available_gems ?? 0,
        ];

        return view('student.pages.gamification.shop', compact(
            'categories',
            'items',
            'balance',
            'categoryId'
        ));
    }

    /**
     * عرض العناصر المميزة
     */
    public function featured(Request $request)
    {
        $user = $request->user();

        $featuredItems = $this->shopService->getFeaturedItems();

        // إضافة معلومات للمستخدم
        foreach ($featuredItems as $item) {
            $item->can_purchase = $this->shopService->canUserPurchase($user, $item);
            $item->final_price_points = $this->shopService->calculateFinalPrice($item, 'points');
            $item->final_price_gems = $this->shopService->calculateFinalPrice($item, 'gems');
        }

        return response()->json([
            'success' => true,
            'featured_items' => $featuredItems,
        ]);
    }

    /**
     * عرض العروض المحدودة
     */
    public function timeLimitedOffers(Request $request)
    {
        $user = $request->user();

        $offers = $this->shopService->getTimeLimitedOffers();

        // إضافة معلومات للمستخدم
        foreach ($offers as $item) {
            $item->can_purchase = $this->shopService->canUserPurchase($user, $item);
            $item->final_price_points = $this->shopService->calculateFinalPrice($item, 'points');
            $item->final_price_gems = $this->shopService->calculateFinalPrice($item, 'gems');
            $item->time_remaining = [
                'seconds' => now()->diffInSeconds($item->discount_expires_at, false),
                'human' => now()->diffForHumans($item->discount_expires_at),
            ];
        }

        return response()->json([
            'success' => true,
            'limited_offers' => $offers,
        ]);
    }

    /**
     * عرض تفاصيل عنصر
     */
    public function show(Request $request, ShopItem $shopItem)
    {
        $user = $request->user();

        $shopItem->load('category', 'requiredBadge');

        // معلومات الشراء
        $purchaseInfo = [
            'can_purchase' => $this->shopService->canUserPurchase($user, $shopItem),
            'has_enough_points' => $this->shopService->hasEnoughBalance($user, $shopItem->price_points, 'points'),
            'has_enough_gems' => $this->shopService->hasEnoughBalance($user, $shopItem->price_gems, 'gems'),
            'final_price_points' => $this->shopService->calculateFinalPrice($shopItem, 'points'),
            'final_price_gems' => $this->shopService->calculateFinalPrice($shopItem, 'gems'),
            'is_owned' => $this->shopService->inventoryService->hasItem($user, $shopItem),
            'owned_quantity' => $this->shopService->inventoryService->getItemQuantity($user, $shopItem),
        ];

        return response()->json([
            'success' => true,
            'item' => $shopItem,
            'purchase_info' => $purchaseInfo,
            'user_balance' => [
                'points' => $user->stats->available_points,
                'gems' => $user->stats->available_gems,
            ],
        ]);
    }

    /**
     * شراء عنصر
     */
    public function purchase(Request $request, ShopItem $shopItem)
    {
        $user = $request->user();

        $validated = $request->validate([
            'payment_method' => 'required|in:points,gems',
        ]);

        $purchase = $this->shopService->purchaseItem($user, $shopItem, $validated['payment_method']);

        if (!$purchase) {
            return response()->json([
                'success' => false,
                'message' => 'فشل الشراء. تأكد من توفر المتطلبات والرصيد الكافي.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم الشراء بنجاح! 🎉',
            'purchase' => $purchase->load('shopItem'),
            'new_balance' => [
                'points' => $user->fresh()->stats->available_points,
                'gems' => $user->fresh()->stats->available_gems,
            ],
        ]);
    }

    /**
     * عرض الفئات
     */
    public function categories()
    {
        $categories = ShopCategory::where('is_active', true)
            ->withCount(['items' => function($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'categories' => $categories,
        ]);
    }

    /**
     * عرض عناصر فئة معينة
     */
    public function categoryItems(Request $request, ShopCategory $shopCategory)
    {
        $user = $request->user();

        $items = $this->shopService->getAvailableItems($user, $shopCategory->id);

        return response()->json([
            'success' => true,
            'category' => $shopCategory,
            'items' => $items,
        ]);
    }

    /**
     * عرض سجل المشتريات
     */
    public function purchaseHistory(Request $request)
    {
        $user = $request->user();
        $period = $request->input('period');

        $purchases = $this->shopService->getUserPurchases($user, $period);

        return response()->json([
            'success' => true,
            'purchases' => $purchases,
        ]);
    }

    /**
     * إحصائيات الشراء
     */
    public function myStats(Request $request)
    {
        $user = $request->user();

        $stats = $this->shopService->getUserPurchaseStats($user);

        // إضافة توصيات المعززات
        $boosterRecommendations = $this->boosterService->getRecommendedBoosters($user);

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'booster_recommendations' => $boosterRecommendations,
        ]);
    }

    /**
     * البحث في المتجر
     */
    public function search(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $items = ShopItem::where('is_active', true)
            ->where('in_stock', true)
            ->where(function($q) use ($validated) {
                $q->where('name', 'like', "%{$validated['query']}%")
                  ->orWhere('description', 'like', "%{$validated['query']}%");
            })
            ->with('category')
            ->orderBy('total_purchases', 'desc')
            ->limit(20)
            ->get();

        // إضافة معلومات الشراء
        foreach ($items as $item) {
            $item->can_purchase = $this->shopService->canUserPurchase($user, $item);
            $item->final_price_points = $this->shopService->calculateFinalPrice($item, 'points');
            $item->final_price_gems = $this->shopService->calculateFinalPrice($item, 'gems');
        }

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }
}
