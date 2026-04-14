<?php

namespace App\Http\Controllers\Api\Student\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Gamification\ShopCategory;
use App\Models\Gamification\ShopItem;
use App\Models\Gamification\UserInventory;
use App\Services\Gamification\ShopService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopApiController extends Controller
{
    public function __construct(private readonly ShopService $shopService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $categoryId = $request->query('category_id');

        $categories = ShopCategory::query()
            ->where('is_active', true)
            ->withCount(['items' => function ($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'items' => $this->shopService->getAvailableItems($user, $categoryId ? (int) $categoryId : null)->values(),
                'featured_items' => $this->shopService->getFeaturedItems()->values(),
                'limited_offers' => $this->shopService->getTimeLimitedOffers()->values(),
                'stats' => $this->shopService->getUserPurchaseStats($user),
            ],
        ]);
    }

    public function show(Request $request, ShopItem $item): JsonResponse
    {
        $user = $request->user();
        $item->load('category', 'requiredBadge');

        $isOwned = UserInventory::query()
            ->where('user_id', $user->id)
            ->where('shop_item_id', $item->id)
            ->where('quantity', '>', 0)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'item' => $item,
                'is_owned' => $isOwned,
                'user_balance' => [
                    'points' => (int) ($user->stats->available_points ?? 0),
                    'gems' => (int) ($user->stats->available_gems ?? 0),
                ],
            ],
        ]);
    }

    public function purchase(Request $request, ShopItem $item): JsonResponse
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:points,gems',
        ]);
        $user = $request->user();

        $purchase = $this->shopService->purchaseItem($user, $item, $validated['payment_method']);
        if (! $purchase) {
            return response()->json([
                'success' => false,
                'message' => 'فشل تنفيذ عملية الشراء.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم الشراء بنجاح.',
            'data' => [
                'purchase' => $purchase->load('shopItem'),
                'new_balance' => [
                    'points' => (int) ($user->fresh()->stats->available_points ?? 0),
                    'gems' => (int) ($user->fresh()->stats->available_gems ?? 0),
                ],
            ],
        ]);
    }
}
