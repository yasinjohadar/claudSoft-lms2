<?php

namespace App\Services\Gamification;

use App\Models\User;
use App\Models\ShopItem;
use App\Models\UserInventory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BoosterService
{
    /**
     * الحصول على مضاعف XP النشط للمستخدم
     */
    public function getActiveXPMultiplier(User $user): float
    {
        $cacheKey = "user_{$user->id}_xp_multiplier";

        return Cache::remember($cacheKey, 300, function () use ($user) {
            $multiplier = 1.0;

            // الحصول على المعززات النشطة
            $activeBoosters = $this->getActiveBoostersOfType($user, 'xp_booster');

            foreach ($activeBoosters as $booster) {
                $item = $booster->shopItem;
                $effects = $item->effects ?? [];

                if (isset($effects['xp_multiplier'])) {
                    $multiplier += ($effects['xp_multiplier'] - 1.0);
                }
            }

            return $multiplier;
        });
    }

    /**
     * الحصول على مضاعف النقاط النشط للمستخدم
     */
    public function getActivePointsMultiplier(User $user): float
    {
        $cacheKey = "user_{$user->id}_points_multiplier";

        return Cache::remember($cacheKey, 300, function () use ($user) {
            $multiplier = 1.0;

            // الحصول على المعززات النشطة
            $activeBoosters = $this->getActiveBoostersOfType($user, 'points_booster');

            foreach ($activeBoosters as $booster) {
                $item = $booster->shopItem;
                $effects = $item->effects ?? [];

                if (isset($effects['points_multiplier'])) {
                    $multiplier += ($effects['points_multiplier'] - 1.0);
                }
            }

            return $multiplier;
        });
    }

    /**
     * التحقق من حماية السلسلة النشطة
     */
    public function hasActiveStreakProtection(User $user): bool
    {
        $cacheKey = "user_{$user->id}_streak_protection";

        return Cache::remember($cacheKey, 300, function () use ($user) {
            // التحقق من الحماية الدائمة
            if ($user->stats->has_streak_protection) {
                return true;
            }

            // التحقق من المعززات المؤقتة
            $protection = UserInventory::where('user_id', $user->id)
                ->where('is_active', true)
                ->where('status', 'owned')
                ->whereHas('shopItem', function($q) {
                    $q->where('type', 'streak_protection');
                })
                ->where(function($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })
                ->exists();

            return $protection;
        });
    }

    /**
     * استخدام حماية السلسلة
     */
    public function useStreakProtection(User $user): bool
    {
        try {
            // البحث عن حماية السلسلة النشطة
            $protection = UserInventory::where('user_id', $user->id)
                ->where('is_active', true)
                ->where('status', 'owned')
                ->whereHas('shopItem', function($q) {
                    $q->where('type', 'streak_protection')
                      ->where('is_consumable', true);
                })
                ->first();

            if (!$protection) {
                return false;
            }

            // استهلاك العنصر
            $inventoryService = app(InventoryService::class);
            $consumed = $inventoryService->consumeItem($user, $protection);

            if ($consumed) {
                Log::info('Streak protection used', [
                    'user_id' => $user->id,
                    'inventory_id' => $protection->id,
                ]);

                // تنظيف الكاش
                Cache::forget("user_{$user->id}_streak_protection");

                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('Failed to use streak protection', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * تفعيل معزز
     */
    public function activateBooster(User $user, UserInventory $inventory): bool
    {
        try {
            $item = $inventory->shopItem;

            // التحقق من أن العنصر معزز
            if (!in_array($item->type, ['xp_booster', 'points_booster', 'streak_protection'])) {
                Log::warning('Item is not a booster', [
                    'item_id' => $item->id,
                    'type' => $item->type,
                ]);
                return false;
            }

            // تفعيل المعزز
            $inventory->update([
                'is_active' => true,
                'activated_at' => now(),
            ]);

            // تنظيف الكاش
            $this->clearUserBoosterCache($user);

            Log::info('Booster activated', [
                'user_id' => $user->id,
                'item_id' => $item->id,
                'type' => $item->type,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to activate booster', [
                'user_id' => $user->id,
                'inventory_id' => $inventory->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * إلغاء تفعيل معزز
     */
    public function deactivateBooster(User $user, UserInventory $inventory): bool
    {
        try {
            $inventory->update([
                'is_active' => false,
                'deactivated_at' => now(),
            ]);

            // تنظيف الكاش
            $this->clearUserBoosterCache($user);

            Log::info('Booster deactivated', [
                'user_id' => $user->id,
                'inventory_id' => $inventory->id,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to deactivate booster', [
                'user_id' => $user->id,
                'inventory_id' => $inventory->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * الحصول على المعززات النشطة من نوع معين
     */
    protected function getActiveBoostersOfType(User $user, string $type)
    {
        return UserInventory::where('user_id', $user->id)
            ->where('is_active', true)
            ->where('status', 'owned')
            ->whereHas('shopItem', function($q) use ($type) {
                $q->where('type', $type);
            })
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->with('shopItem')
            ->get();
    }

    /**
     * الحصول على جميع المعززات النشطة للمستخدم
     */
    public function getAllActiveBoosters(User $user): array
    {
        $xpMultiplier = $this->getActiveXPMultiplier($user);
        $pointsMultiplier = $this->getActivePointsMultiplier($user);
        $streakProtection = $this->hasActiveStreakProtection($user);

        $activeBoosters = UserInventory::where('user_id', $user->id)
            ->where('is_active', true)
            ->where('status', 'owned')
            ->whereHas('shopItem', function($q) {
                $q->whereIn('type', ['xp_booster', 'points_booster', 'streak_protection']);
            })
            ->with('shopItem')
            ->get();

        $boosterDetails = $activeBoosters->map(function($inventory) {
            $item = $inventory->shopItem;
            $timeRemaining = null;

            if ($inventory->expires_at) {
                $timeRemaining = [
                    'seconds' => now()->diffInSeconds($inventory->expires_at, false),
                    'human' => now()->diffForHumans($inventory->expires_at),
                    'expires_at' => $inventory->expires_at,
                ];
            }

            return [
                'id' => $inventory->id,
                'name' => $item->name,
                'type' => $item->type,
                'icon' => $item->icon,
                'effects' => $item->effects,
                'activated_at' => $inventory->activated_at,
                'expires_at' => $inventory->expires_at,
                'time_remaining' => $timeRemaining,
            ];
        });

        return [
            'xp_multiplier' => $xpMultiplier,
            'points_multiplier' => $pointsMultiplier,
            'has_streak_protection' => $streakProtection,
            'active_boosters' => $boosterDetails,
        ];
    }

    /**
     * تطبيق مضاعف XP على القيمة
     */
    public function applyXPMultiplier(User $user, int $baseXP): int
    {
        $multiplier = $this->getActiveXPMultiplier($user);
        return (int) ($baseXP * $multiplier);
    }

    /**
     * تطبيق مضاعف النقاط على القيمة
     */
    public function applyPointsMultiplier(User $user, int $basePoints): int
    {
        $multiplier = $this->getActivePointsMultiplier($user);
        return (int) ($basePoints * $multiplier);
    }

    /**
     * تنظيف كاش المعززات للمستخدم
     */
    public function clearUserBoosterCache(User $user): void
    {
        Cache::forget("user_{$user->id}_xp_multiplier");
        Cache::forget("user_{$user->id}_points_multiplier");
        Cache::forget("user_{$user->id}_streak_protection");
    }

    /**
     * فحص وتحديث المعززات المنتهية
     */
    public function checkExpiredBoosters(): int
    {
        $expired = UserInventory::where('is_active', true)
            ->where('status', 'owned')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->whereHas('shopItem', function($q) {
                $q->whereIn('type', ['xp_booster', 'points_booster', 'streak_protection']);
            })
            ->get();

        foreach ($expired as $inventory) {
            $this->deactivateBooster($inventory->user, $inventory);

            $inventory->update([
                'status' => 'expired',
            ]);
        }

        Log::info('Expired boosters updated', ['count' => $expired->count()]);

        return $expired->count();
    }

    /**
     * الحصول على توصيات المعززات للمستخدم
     */
    public function getRecommendedBoosters(User $user): array
    {
        $stats = $user->stats;
        $recommendations = [];

        // توصية معزز XP للطلاب النشطين
        if ($stats->lessons_completed > 10 && $this->getActiveXPMultiplier($user) <= 1.0) {
            $recommendations[] = [
                'type' => 'xp_booster',
                'reason' => 'أنت طالب نشط! معزز XP سيساعدك على الوصول للمستويات الأعلى بشكل أسرع.',
            ];
        }

        // توصية معزز النقاط للطلاب الذين يجمعون الشارات
        if ($stats->total_badges > 5 && $this->getActivePointsMultiplier($user) <= 1.0) {
            $recommendations[] = [
                'type' => 'points_booster',
                'reason' => 'تجمع الكثير من الشارات! معزز النقاط سيضاعف مكافآتك.',
            ];
        }

        // توصية حماية السلسلة للطلاب ذوي السلاسل الطويلة
        if ($stats->current_streak >= 7 && !$this->hasActiveStreakProtection($user)) {
            $recommendations[] = [
                'type' => 'streak_protection',
                'reason' => "لديك سلسلة رائعة من {$stats->current_streak} يوم! احمها من الانقطاع.",
            ];
        }

        return $recommendations;
    }

    /**
     * إحصائيات المعززات للمستخدم
     */
    public function getUserBoosterStats(User $user): array
    {
        $totalBoostersUsed = UserInventory::where('user_id', $user->id)
            ->whereHas('shopItem', function($q) {
                $q->whereIn('type', ['xp_booster', 'points_booster', 'streak_protection']);
            })
            ->whereIn('status', ['consumed', 'expired'])
            ->count();

        $currentlyActive = UserInventory::where('user_id', $user->id)
            ->where('is_active', true)
            ->where('status', 'owned')
            ->whereHas('shopItem', function($q) {
                $q->whereIn('type', ['xp_booster', 'points_booster', 'streak_protection']);
            })
            ->count();

        $boostersByType = UserInventory::where('user_id', $user->id)
            ->whereHas('shopItem', function($q) {
                $q->whereIn('type', ['xp_booster', 'points_booster', 'streak_protection']);
            })
            ->join('shop_items', 'user_inventory.shop_item_id', '=', 'shop_items.id')
            ->selectRaw('shop_items.type, COUNT(*) as count')
            ->groupBy('shop_items.type')
            ->pluck('count', 'type');

        return [
            'total_boosters_used' => $totalBoostersUsed,
            'currently_active' => $currentlyActive,
            'current_xp_multiplier' => $this->getActiveXPMultiplier($user),
            'current_points_multiplier' => $this->getActivePointsMultiplier($user),
            'has_streak_protection' => $this->hasActiveStreakProtection($user),
            'boosters_by_type' => $boostersByType,
        ];
    }
}
