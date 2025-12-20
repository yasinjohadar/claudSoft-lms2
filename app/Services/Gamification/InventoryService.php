<?php

namespace App\Services\Gamification;

use App\Models\User;
use App\Models\Gamification\ShopItem;
use App\Models\Gamification\UserInventory;
use App\Models\Gamification\UserPurchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InventoryService
{
    /**
     * إضافة عنصر للمخزون
     */
    public function addItemToInventory(User $user, ShopItem $item, UserPurchase $purchase): ?UserInventory
    {
        try {
            // حساب تاريخ الانتهاء للعناصر المؤقتة
            $expiresAt = null;
            if ($item->duration_days) {
                $expiresAt = now()->addDays($item->duration_days);
            }

            // التحقق من وجود العنصر مسبقاً (للعناصر القابلة للتكديس)
            if ($item->is_stackable) {
                $existing = UserInventory::where('user_id', $user->id)
                    ->where('shop_item_id', $item->id)
                    ->where('status', 'owned')
                    ->first();

                if ($existing) {
                    $existing->increment('quantity');
                    $existing->update(['acquired_at' => now()]);
                    return $existing;
                }
            }

            // إنشاء سجل جديد في المخزون
            $inventory = UserInventory::create([
                'user_id' => $user->id,
                'shop_item_id' => $item->id,
                'purchase_id' => $purchase->id,
                'quantity' => 1,
                'status' => 'owned',
                'is_active' => false,
                'acquired_at' => now(),
                'expires_at' => $expiresAt,
            ]);

            // تفعيل العنصر تلقائياً حسب النوع
            if ($this->shouldAutoActivate($item)) {
                $this->activateItem($user, $inventory);
            }

            Log::info('Item added to inventory', [
                'user_id' => $user->id,
                'item_id' => $item->id,
                'inventory_id' => $inventory->id,
            ]);

            return $inventory;

        } catch (\Exception $e) {
            Log::error('Failed to add item to inventory', [
                'user_id' => $user->id,
                'item_id' => $item->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * تحديد ما إذا كان يجب تفعيل العنصر تلقائياً
     */
    protected function shouldAutoActivate(ShopItem $item): bool
    {
        // تفعيل تلقائي للعناصر الدائمة مثل الأفاتارات والثيمات
        return in_array($item->type, ['avatar', 'profile_frame', 'theme']);
    }

    /**
     * تفعيل عنصر من المخزون
     */
    public function activateItem(User $user, UserInventory $inventory): bool
    {
        try {
            return DB::transaction(function () use ($user, $inventory) {
                $item = $inventory->shopItem;

                // إلغاء تفعيل العناصر الأخرى من نفس النوع (للعناصر الحصرية)
                if ($this->isExclusiveType($item->type)) {
                    UserInventory::where('user_id', $user->id)
                        ->whereHas('shopItem', function($q) use ($item) {
                            $q->where('type', $item->type);
                        })
                        ->where('id', '!=', $inventory->id)
                        ->update(['is_active' => false]);
                }

                // تفعيل العنصر
                $inventory->update([
                    'is_active' => true,
                    'activated_at' => now(),
                ]);

                // تطبيق تأثير العنصر
                $this->applyItemEffect($user, $item);

                Log::info('Item activated', [
                    'user_id' => $user->id,
                    'inventory_id' => $inventory->id,
                    'item_type' => $item->type,
                ]);

                return true;
            });

        } catch (\Exception $e) {
            Log::error('Failed to activate item', [
                'user_id' => $user->id,
                'inventory_id' => $inventory->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * إلغاء تفعيل عنصر
     */
    public function deactivateItem(User $user, UserInventory $inventory): bool
    {
        try {
            $item = $inventory->shopItem;

            $inventory->update([
                'is_active' => false,
                'deactivated_at' => now(),
            ]);

            // إزالة تأثير العنصر
            $this->removeItemEffect($user, $item);

            Log::info('Item deactivated', [
                'user_id' => $user->id,
                'inventory_id' => $inventory->id,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to deactivate item', [
                'user_id' => $user->id,
                'inventory_id' => $inventory->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * استخدام عنصر قابل للاستهلاك
     */
    public function consumeItem(User $user, UserInventory $inventory): bool
    {
        try {
            return DB::transaction(function () use ($user, $inventory) {
                $item = $inventory->shopItem;

                if (!$item->is_consumable) {
                    Log::warning('Item is not consumable', [
                        'item_id' => $item->id,
                    ]);
                    return false;
                }

                if ($inventory->quantity <= 0) {
                    Log::warning('No items available to consume', [
                        'inventory_id' => $inventory->id,
                    ]);
                    return false;
                }

                // تطبيق تأثير العنصر
                $this->applyConsumableEffect($user, $item);

                // تقليل الكمية
                $inventory->decrement('quantity');

                // تحديث الحالة إذا انتهت الكمية
                if ($inventory->quantity <= 0) {
                    $inventory->update([
                        'status' => 'consumed',
                        'consumed_at' => now(),
                    ]);
                }

                Log::info('Item consumed', [
                    'user_id' => $user->id,
                    'item_id' => $item->id,
                    'remaining_quantity' => $inventory->quantity,
                ]);

                return true;
            });

        } catch (\Exception $e) {
            Log::error('Failed to consume item', [
                'user_id' => $user->id,
                'inventory_id' => $inventory->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * تطبيق تأثير العنصر
     */
    protected function applyItemEffect(User $user, ShopItem $item): void
    {
        $effects = $item->effects ?? [];

        foreach ($effects as $effect => $value) {
            switch ($effect) {
                case 'xp_multiplier':
                    // يتم التعامل معه في BoosterService
                    break;

                case 'points_multiplier':
                    // يتم التعامل معه في BoosterService
                    break;

                case 'streak_protection':
                    // تفعيل حماية السلسلة
                    $user->stats->update(['has_streak_protection' => true]);
                    break;

                case 'avatar':
                    // تغيير الأفاتار
                    $user->update(['avatar' => $value]);
                    break;

                case 'profile_frame':
                    // تغيير إطار الملف الشخصي
                    $user->update(['profile_frame' => $value]);
                    break;

                case 'theme':
                    // تغيير الثيم
                    $user->update(['preferred_theme' => $value]);
                    break;
            }
        }
    }

    /**
     * إزالة تأثير العنصر
     */
    protected function removeItemEffect(User $user, ShopItem $item): void
    {
        $effects = $item->effects ?? [];

        foreach ($effects as $effect => $value) {
            switch ($effect) {
                case 'streak_protection':
                    $user->stats->update(['has_streak_protection' => false]);
                    break;

                case 'avatar':
                    $user->update(['avatar' => null]);
                    break;

                case 'profile_frame':
                    $user->update(['profile_frame' => null]);
                    break;

                case 'theme':
                    $user->update(['preferred_theme' => null]);
                    break;
            }
        }
    }

    /**
     * تطبيق تأثير العنصر القابل للاستهلاك
     */
    protected function applyConsumableEffect(User $user, ShopItem $item): void
    {
        $effects = $item->effects ?? [];

        foreach ($effects as $effect => $value) {
            switch ($effect) {
                case 'bonus_points':
                    $user->stats->increment('available_points', $value);
                    break;

                case 'bonus_xp':
                    $levelService = app(LevelService::class);
                    $levelService->awardXP($user, $value, 'consumable_item', $item->name);
                    break;

                case 'bonus_gems':
                    $user->stats->increment('available_gems', $value);
                    break;

                case 'restore_streak':
                    // استعادة السلسلة
                    $user->stats->update([
                        'current_streak' => $user->stats->longest_streak,
                    ]);
                    break;

                case 'quiz_retry':
                    // منح إعادة محاولة اختبار
                    // يتم التعامل معه في نظام الاختبارات
                    break;
            }
        }
    }

    /**
     * التحقق من نوع العنصر الحصري
     */
    protected function isExclusiveType(string $type): bool
    {
        return in_array($type, [
            'avatar',
            'profile_frame',
            'theme',
            'title',
        ]);
    }

    /**
     * التحقق من امتلاك المستخدم لعنصر
     */
    public function hasItem(User $user, ShopItem $item): bool
    {
        return UserInventory::where('user_id', $user->id)
            ->where('shop_item_id', $item->id)
            ->whereIn('status', ['owned', 'active'])
            ->exists();
    }

    /**
     * الحصول على كمية العنصر المملوكة
     */
    public function getItemQuantity(User $user, ShopItem $item): int
    {
        return UserInventory::where('user_id', $user->id)
            ->where('shop_item_id', $item->id)
            ->whereIn('status', ['owned', 'active'])
            ->sum('quantity');
    }

    /**
     * الحصول على مخزون المستخدم
     */
    public function getUserInventory(User $user, ?string $type = null, ?string $status = null)
    {
        $query = UserInventory::where('user_id', $user->id)
            ->with('shopItem.category');

        if ($type) {
            $query->whereHas('shopItem', function($q) use ($type) {
                $q->where('type', $type);
            });
        }

        if ($status) {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', ['owned', 'active']);
        }

        return $query->orderByDesc('is_active')
            ->orderByDesc('acquired_at')
            ->get();
    }

    /**
     * الحصول على العناصر النشطة
     */
    public function getActiveItems(User $user)
    {
        return UserInventory::where('user_id', $user->id)
            ->where('is_active', true)
            ->where('status', 'owned')
            ->with('shopItem.category')
            ->get();
    }

    /**
     * فحص وتحديث العناصر المنتهية
     */
    public function checkExpiredItems(): int
    {
        $expired = UserInventory::where('status', 'owned')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $inventory) {
            $inventory->update([
                'status' => 'expired',
                'is_active' => false,
            ]);

            // إزالة التأثيرات
            $this->removeItemEffect($inventory->user, $inventory->shopItem);
        }

        Log::info('Expired items updated', ['count' => $expired->count()]);

        return $expired->count();
    }

    /**
     * إحصائيات المخزون
     */
    public function getInventoryStats(User $user): array
    {
        $totalItems = UserInventory::where('user_id', $user->id)
            ->whereIn('status', ['owned', 'active'])
            ->sum('quantity');

        $activeItems = UserInventory::where('user_id', $user->id)
            ->where('is_active', true)
            ->count();

        $consumedItems = UserInventory::where('user_id', $user->id)
            ->where('status', 'consumed')
            ->count();

        $expiredItems = UserInventory::where('user_id', $user->id)
            ->where('status', 'expired')
            ->count();

        $itemsByType = UserInventory::where('user_id', $user->id)
            ->whereIn('status', ['owned', 'active'])
            ->join('shop_items', 'user_inventory.shop_item_id', '=', 'shop_items.id')
            ->selectRaw('shop_items.type, SUM(user_inventory.quantity) as count')
            ->groupBy('shop_items.type')
            ->pluck('count', 'type');

        $itemsByCategory = UserInventory::where('user_id', $user->id)
            ->whereIn('status', ['owned', 'active'])
            ->join('shop_items', 'user_inventory.shop_item_id', '=', 'shop_items.id')
            ->join('shop_categories', 'shop_items.category_id', '=', 'shop_categories.id')
            ->selectRaw('shop_categories.name, COUNT(*) as count')
            ->groupBy('shop_categories.name')
            ->pluck('count', 'name');

        return [
            'total_items' => $totalItems,
            'active_items' => $activeItems,
            'consumed_items' => $consumedItems,
            'expired_items' => $expiredItems,
            'items_by_type' => $itemsByType,
            'items_by_category' => $itemsByCategory,
        ];
    }
}
