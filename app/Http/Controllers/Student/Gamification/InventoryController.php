<?php

namespace App\Http\Controllers\Student\Gamification;

use App\Http\Controllers\Controller;
use App\Models\UserInventory;
use App\Services\Gamification\InventoryService;
use App\Services\Gamification\BoosterService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    protected InventoryService $inventoryService;
    protected BoosterService $boosterService;

    public function __construct(
        InventoryService $inventoryService,
        BoosterService $boosterService
    ) {
        $this->inventoryService = $inventoryService;
        $this->boosterService = $boosterService;
    }

    /**
     * عرض المخزون
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $type = $request->input('type');
        $status = $request->input('status');

        $inventory = $this->inventoryService->getUserInventory($user, $type, $status);

        // إضافة معلومات الوقت المتبقي
        $inventory->each(function($item) {
            if ($item->expires_at) {
                $item->time_remaining = [
                    'seconds' => now()->diffInSeconds($item->expires_at, false),
                    'human' => now()->diffForHumans($item->expires_at),
                ];
            }
        });

        $stats = $this->inventoryService->getInventoryStats($user);

        return response()->json([
            'success' => true,
            'inventory' => $inventory,
            'stats' => $stats,
        ]);
    }

    /**
     * عرض العناصر النشطة
     */
    public function active(Request $request)
    {
        $user = $request->user();

        $activeItems = $this->inventoryService->getActiveItems($user);

        // المعززات النشطة
        $activeBoosters = $this->boosterService->getAllActiveBoosters($user);

        return response()->json([
            'success' => true,
            'active_items' => $activeItems,
            'active_boosters' => $activeBoosters,
        ]);
    }

    /**
     * تفعيل عنصر
     */
    public function activate(Request $request, UserInventory $inventory)
    {
        $user = $request->user();

        // التحقق من ملكية العنصر
        if ($inventory->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتفعيل هذا العنصر.',
            ], 403);
        }

        $item = $inventory->shopItem;

        // تحديد نوع التفعيل
        if (in_array($item->type, ['xp_booster', 'points_booster', 'streak_protection'])) {
            $success = $this->boosterService->activateBooster($user, $inventory);
        } else {
            $success = $this->inventoryService->activateItem($user, $inventory);
        }

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تفعيل العنصر.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تفعيل العنصر بنجاح!',
            'inventory' => $inventory->fresh(['shopItem']),
        ]);
    }

    /**
     * إلغاء تفعيل عنصر
     */
    public function deactivate(Request $request, UserInventory $inventory)
    {
        $user = $request->user();

        // التحقق من ملكية العنصر
        if ($inventory->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بإلغاء تفعيل هذا العنصر.',
            ], 403);
        }

        $item = $inventory->shopItem;

        // تحديد نوع الإلغاء
        if (in_array($item->type, ['xp_booster', 'points_booster', 'streak_protection'])) {
            $success = $this->boosterService->deactivateBooster($user, $inventory);
        } else {
            $success = $this->inventoryService->deactivateItem($user, $inventory);
        }

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'فشل إلغاء تفعيل العنصر.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء تفعيل العنصر بنجاح!',
            'inventory' => $inventory->fresh(['shopItem']),
        ]);
    }

    /**
     * استخدام عنصر قابل للاستهلاك
     */
    public function consume(Request $request, UserInventory $inventory)
    {
        $user = $request->user();

        // التحقق من ملكية العنصر
        if ($inventory->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك باستخدام هذا العنصر.',
            ], 403);
        }

        $success = $this->inventoryService->consumeItem($user, $inventory);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'فشل استخدام العنصر. تأكد من أن العنصر قابل للاستهلاك ومتوفر.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم استخدام العنصر بنجاح!',
            'inventory' => $inventory->fresh(['shopItem']),
            'new_stats' => $user->fresh()->stats,
        ]);
    }

    /**
     * عرض التفاصيل
     */
    public function show(Request $request, UserInventory $inventory)
    {
        $user = $request->user();

        // التحقق من ملكية العنصر
        if ($inventory->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بعرض هذا العنصر.',
            ], 403);
        }

        $inventory->load('shopItem.category', 'purchase');

        $timeRemaining = null;
        if ($inventory->expires_at) {
            $timeRemaining = [
                'seconds' => now()->diffInSeconds($inventory->expires_at, false),
                'human' => now()->diffForHumans($inventory->expires_at),
                'percentage' => $inventory->activated_at
                    ? round((now()->diffInSeconds($inventory->activated_at) / $inventory->activated_at->diffInSeconds($inventory->expires_at)) * 100, 2)
                    : 0,
            ];
        }

        return response()->json([
            'success' => true,
            'inventory' => $inventory,
            'time_remaining' => $timeRemaining,
        ]);
    }

    /**
     * عرض العناصر التجميلية
     */
    public function cosmetics(Request $request)
    {
        $user = $request->user();

        $cosmetics = $this->inventoryService->getUserInventory($user, null, 'owned')
            ->filter(function($item) {
                return in_array($item->shopItem->type, ['avatar', 'profile_frame', 'theme', 'title']);
            });

        return response()->json([
            'success' => true,
            'cosmetics' => $cosmetics->values(),
        ]);
    }

    /**
     * عرض المعززات
     */
    public function boosters(Request $request)
    {
        $user = $request->user();

        $boosters = $this->inventoryService->getUserInventory($user, null, 'owned')
            ->filter(function($item) {
                return in_array($item->shopItem->type, ['xp_booster', 'points_booster', 'streak_protection']);
            });

        $boosterStats = $this->boosterService->getUserBoosterStats($user);

        return response()->json([
            'success' => true,
            'boosters' => $boosters->values(),
            'stats' => $boosterStats,
        ]);
    }

    /**
     * عرض العناصر المستهلكة
     */
    public function consumables(Request $request)
    {
        $user = $request->user();

        $consumables = $this->inventoryService->getUserInventory($user, null, 'owned')
            ->filter(function($item) {
                return $item->shopItem->is_consumable && $item->quantity > 0;
            });

        return response()->json([
            'success' => true,
            'consumables' => $consumables->values(),
        ]);
    }

    /**
     * إحصائيات المخزون
     */
    public function stats(Request $request)
    {
        $user = $request->user();

        $stats = $this->inventoryService->getInventoryStats($user);

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}
