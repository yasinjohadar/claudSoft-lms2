<?php

namespace App\Services\Gamification;

use App\Models\User;
use App\Models\Gamification\ShopItem;
use App\Models\Gamification\ShopCategory;
use App\Models\Gamification\UserPurchase;
use App\Models\Gamification\UserInventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ShopService
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * شراء عنصر من المتجر
     */
    public function purchaseItem(User $user, ShopItem $item, string $paymentMethod = 'points'): ?UserPurchase
    {
        try {
            return DB::transaction(function () use ($user, $item, $paymentMethod) {
                // التحقق من توفر العنصر
                if (!$item->is_active || !$item->in_stock) {
                    Log::warning('Item not available for purchase', [
                        'item_id' => $item->id,
                        'user_id' => $user->id,
                    ]);
                    return null;
                }

                // التحقق من الكمية المتاحة
                if ($item->stock_quantity !== null && $item->stock_quantity <= 0) {
                    Log::warning('Item out of stock', [
                        'item_id' => $item->id,
                        'user_id' => $user->id,
                    ]);
                    return null;
                }

                // التحقق من شروط الشراء
                if (!$this->canUserPurchase($user, $item)) {
                    return null;
                }

                // حساب السعر النهائي
                $finalPrice = $this->calculateFinalPrice($item, $paymentMethod);

                // التحقق من رصيد المستخدم
                if (!$this->hasEnoughBalance($user, $finalPrice, $paymentMethod)) {
                    Log::warning('Insufficient balance', [
                        'user_id' => $user->id,
                        'item_id' => $item->id,
                        'payment_method' => $paymentMethod,
                        'required' => $finalPrice,
                    ]);
                    return null;
                }

                // خصم المبلغ من رصيد المستخدم
                $this->deductBalance($user, $finalPrice, $paymentMethod);

                // إنشاء سجل الشراء
                $purchase = UserPurchase::create([
                    'user_id' => $user->id,
                    'shop_item_id' => $item->id,
                    'payment_method' => $paymentMethod,
                    'original_price' => $paymentMethod === 'points' ? $item->price_points : $item->price_gems,
                    'discount_percentage' => $item->discount_percentage ?? 0,
                    'final_price' => $finalPrice,
                    'purchased_at' => now(),
                    'metadata' => [
                        'item_name' => $item->name,
                        'category' => $item->category->name ?? null,
                    ],
                ]);

                // إضافة العنصر للمخزون
                $this->inventoryService->addItemToInventory($user, $item, $purchase);

                // تقليل الكمية المتاحة
                if ($item->stock_quantity !== null) {
                    $item->decrement('stock_quantity');
                }

                // تحديث إحصائيات العنصر
                $item->increment('total_purchases');
                $item->update(['last_purchased_at' => now()]);

                // تحديث إحصائيات المستخدم
                $user->stats->increment('total_shop_purchases');
                $user->stats->increment('total_spent_points', $paymentMethod === 'points' ? $finalPrice : 0);
                $user->stats->increment('total_spent_gems', $paymentMethod === 'gems' ? $finalPrice : 0);

                Log::info('Item purchased successfully', [
                    'user_id' => $user->id,
                    'item_id' => $item->id,
                    'purchase_id' => $purchase->id,
                    'payment_method' => $paymentMethod,
                    'price' => $finalPrice,
                ]);

                return $purchase;
            });

        } catch (\Exception $e) {
            Log::error('Failed to purchase item', [
                'user_id' => $user->id,
                'item_id' => $item->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * التحقق من إمكانية شراء المستخدم للعنصر
     */
    protected function canUserPurchase(User $user, ShopItem $item): bool
    {
        // التحقق من المستوى المطلوب
        if ($item->required_level && $user->stats->current_level < $item->required_level) {
            Log::warning('User level too low', [
                'user_id' => $user->id,
                'user_level' => $user->stats->current_level,
                'required_level' => $item->required_level,
            ]);
            return false;
        }

        // التحقق من حد الشراء للمستخدم
        if ($item->purchase_limit) {
            $userPurchases = UserPurchase::where('user_id', $user->id)
                ->where('shop_item_id', $item->id)
                ->count();

            if ($userPurchases >= $item->purchase_limit) {
                Log::warning('User reached purchase limit', [
                    'user_id' => $user->id,
                    'item_id' => $item->id,
                    'purchases' => $userPurchases,
                    'limit' => $item->purchase_limit,
                ]);
                return false;
            }
        }

        // التحقق من الشارة المطلوبة
        if ($item->required_badge_id) {
            $hasBadge = $user->badges()
                ->where('badge_id', $item->required_badge_id)
                ->exists();

            if (!$hasBadge) {
                Log::warning('User missing required badge', [
                    'user_id' => $user->id,
                    'required_badge_id' => $item->required_badge_id,
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * حساب السعر النهائي مع الخصم
     */
    protected function calculateFinalPrice(ShopItem $item, string $paymentMethod): int
    {
        $originalPrice = $paymentMethod === 'points' ? $item->price_points : $item->price_gems;

        if (!$item->discount_percentage || $item->discount_percentage <= 0) {
            return $originalPrice;
        }

        // التحقق من صلاحية الخصم
        if ($item->discount_expires_at && $item->discount_expires_at < now()) {
            return $originalPrice;
        }

        $discount = ($originalPrice * $item->discount_percentage) / 100;
        return (int) ($originalPrice - $discount);
    }

    /**
     * التحقق من رصيد المستخدم
     */
    protected function hasEnoughBalance(User $user, int $amount, string $paymentMethod): bool
    {
        $stats = $user->stats;

        if ($paymentMethod === 'points') {
            return $stats->available_points >= $amount;
        } elseif ($paymentMethod === 'gems') {
            return $stats->available_gems >= $amount;
        }

        return false;
    }

    /**
     * خصم المبلغ من رصيد المستخدم
     */
    protected function deductBalance(User $user, int $amount, string $paymentMethod): void
    {
        $stats = $user->stats;

        if ($paymentMethod === 'points') {
            $stats->decrement('available_points', $amount);
        } elseif ($paymentMethod === 'gems') {
            $stats->decrement('available_gems', $amount);
        }
    }

    /**
     * الحصول على العناصر المتاحة للشراء
     */
    public function getAvailableItems(User $user, ?int $categoryId = null)
    {
        $query = ShopItem::where('is_active', true)
            ->where('in_stock', true)
            ->with('category');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $items = $query->orderBy('sort_order')
            ->orderBy('price_points')
            ->get();

        // إضافة معلومات إضافية لكل عنصر
        foreach ($items as $item) {
            $item->can_purchase = $this->canUserPurchase($user, $item);
            $item->has_enough_points = $this->hasEnoughBalance($user, $item->price_points, 'points');
            $item->has_enough_gems = $this->hasEnoughBalance($user, $item->price_gems, 'gems');
            $item->final_price_points = $this->calculateFinalPrice($item, 'points');
            $item->final_price_gems = $this->calculateFinalPrice($item, 'gems');

            // التحقق من ملكية العنصر
            $item->is_owned = $this->inventoryService->hasItem($user, $item);
            $item->owned_quantity = $this->inventoryService->getItemQuantity($user, $item);
        }

        return $items;
    }

    /**
     * الحصول على العروض الخاصة
     */
    public function getFeaturedItems(int $limit = 6)
    {
        return ShopItem::where('is_active', true)
            ->where('is_featured', true)
            ->with('category')
            ->orderByDesc('discount_percentage')
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    /**
     * الحصول على العروض المحدودة بالوقت
     */
    public function getTimeLimitedOffers()
    {
        return ShopItem::where('is_active', true)
            ->whereNotNull('discount_expires_at')
            ->where('discount_expires_at', '>', now())
            ->where('discount_percentage', '>', 0)
            ->with('category')
            ->orderBy('discount_expires_at')
            ->get();
    }

    /**
     * الحصول على سجل مشتريات المستخدم
     */
    public function getUserPurchases(User $user, ?string $period = null)
    {
        $query = UserPurchase::where('user_id', $user->id)
            ->with('shopItem.category')
            ->latest('purchased_at');

        if ($period) {
            switch ($period) {
                case 'today':
                    $query->whereDate('purchased_at', today());
                    break;
                case 'week':
                    $query->whereBetween('purchased_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereBetween('purchased_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
            }
        }

        return $query->get();
    }

    /**
     * إحصائيات مشتريات المستخدم
     */
    public function getUserPurchaseStats(User $user): array
    {
        $stats = $user->stats;

        $totalPurchases = UserPurchase::where('user_id', $user->id)->count();

        $totalSpentPoints = UserPurchase::where('user_id', $user->id)
            ->where('payment_method', 'points')
            ->sum('final_price');

        $totalSpentGems = UserPurchase::where('user_id', $user->id)
            ->where('payment_method', 'gems')
            ->sum('final_price');

        $purchasesToday = UserPurchase::where('user_id', $user->id)
            ->whereDate('purchased_at', today())
            ->count();

        $purchasesThisWeek = UserPurchase::where('user_id', $user->id)
            ->whereBetween('purchased_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $purchasesThisMonth = UserPurchase::where('user_id', $user->id)
            ->whereBetween('purchased_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $favoriteCategory = UserPurchase::where('user_id', $user->id)
            ->join('shop_items', 'user_purchases.shop_item_id', '=', 'shop_items.id')
            ->join('shop_categories', 'shop_items.category_id', '=', 'shop_categories.id')
            ->selectRaw('shop_categories.name, COUNT(*) as count')
            ->groupBy('shop_categories.name')
            ->orderByDesc('count')
            ->first();

        return [
            'total_purchases' => $totalPurchases,
            'total_spent_points' => $totalSpentPoints,
            'total_spent_gems' => $totalSpentGems,
            'purchases_today' => $purchasesToday,
            'purchases_this_week' => $purchasesThisWeek,
            'purchases_this_month' => $purchasesThisMonth,
            'available_points' => $stats->available_points,
            'available_gems' => $stats->available_gems,
            'favorite_category' => $favoriteCategory?->name ?? null,
        ];
    }

    /**
     * إحصائيات المتجر (للإدارة)
     */
    public function getShopStatistics(): array
    {
        $totalItems = ShopItem::count();
        $availableItems = ShopItem::where('is_active', true)->count();
        $outOfStockItems = ShopItem::where('in_stock', false)->count();

        $totalPurchases = UserPurchase::count();
        $totalRevenue = [
            'points' => UserPurchase::where('payment_method', 'points')->sum('final_price'),
            'gems' => UserPurchase::where('payment_method', 'gems')->sum('final_price'),
        ];

        $topSellingItems = ShopItem::orderByDesc('total_purchases')
            ->limit(10)
            ->get(['id', 'name', 'total_purchases', 'price_points', 'price_gems']);

        $recentPurchases = UserPurchase::with(['user:id,name,email', 'shopItem:id,name,icon'])
            ->latest('purchased_at')
            ->limit(10)
            ->get();

        $purchasesByCategory = ShopItem::join('user_purchases', 'shop_items.id', '=', 'user_purchases.shop_item_id')
            ->join('shop_categories', 'shop_items.category_id', '=', 'shop_categories.id')
            ->selectRaw('shop_categories.name, COUNT(*) as count, SUM(user_purchases.final_price) as revenue')
            ->groupBy('shop_categories.name')
            ->get();

        return [
            'total_items' => $totalItems,
            'available_items' => $availableItems,
            'out_of_stock_items' => $outOfStockItems,
            'total_purchases' => $totalPurchases,
            'total_revenue' => $totalRevenue,
            'top_selling_items' => $topSellingItems,
            'recent_purchases' => $recentPurchases,
            'purchases_by_category' => $purchasesByCategory,
        ];
    }

    /**
     * تطبيق خصم على عنصر
     */
    public function applyDiscount(ShopItem $item, int $percentage, ?Carbon $expiresAt = null): bool
    {
        try {
            $item->update([
                'discount_percentage' => $percentage,
                'discount_expires_at' => $expiresAt,
            ]);

            Log::info('Discount applied to item', [
                'item_id' => $item->id,
                'discount' => $percentage,
                'expires_at' => $expiresAt,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to apply discount', [
                'item_id' => $item->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * إزالة الخصم من عنصر
     */
    public function removeDiscount(ShopItem $item): bool
    {
        try {
            $item->update([
                'discount_percentage' => null,
                'discount_expires_at' => null,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to remove discount', [
                'item_id' => $item->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * تحديث الخصومات المنتهية
     */
    public function updateExpiredDiscounts(): int
    {
        $expired = ShopItem::whereNotNull('discount_expires_at')
            ->where('discount_expires_at', '<', now())
            ->update([
                'discount_percentage' => null,
                'discount_expires_at' => null,
            ]);

        Log::info('Expired discounts updated', ['count' => $expired]);

        return $expired;
    }
}
