<?php

use App\Models\Gamification\ShopCategory;
use App\Models\Gamification\ShopItem;
use App\Models\PointsTransaction;
use App\Models\User;
use App\Models\UserStat;
use App\Services\Gamification\ShopService;

test('shop purchase records points transaction', function () {
    $user = User::factory()->create();
    UserStat::create([
        'user_id' => $user->id,
        'total_points' => 500,
        'available_points' => 500,
    ]);

    $category = ShopCategory::create([
        'name' => 'Test Category '.uniqid(),
        'is_active' => true,
    ]);

    $item = ShopItem::create([
        'category_id' => $category->id,
        'name' => 'Test Item',
        'price_points' => 100,
        'price_gems' => 0,
        'is_active' => true,
        'in_stock' => true,
    ]);

    $purchase = app(ShopService::class)->purchaseItem($user, $item, 'points');

    expect($purchase)->not->toBeNull();
    expect(
        PointsTransaction::where('user_id', $user->id)
            ->where('source', 'shop_purchase')
            ->where('type', 'spend')
            ->exists()
    )->toBeTrue();
    expect($user->fresh()->stats->available_points)->toBe(400);
});
