<?php

use App\Models\PointsTransaction;
use App\Models\User;
use App\Models\UserStat;
use App\Services\Gamification\PointsService;
use Illuminate\Support\Facades\Event;
use App\Events\Gamification\PointsEarned;

test('it awards points and dispatches PointsEarned event', function () {
    Event::fake([PointsEarned::class]);

    $user = User::factory()->create();
    UserStat::create(['user_id' => $user->id]);

    $service = app(PointsService::class);
    $transaction = $service->awardPoints(
        $user,
        50,
        'lesson_completion',
        'إتمام درس',
        'App\Models\Lesson',
        1
    );

    expect($transaction)->not->toBeNull();
    expect($user->fresh()->stats->available_points)->toBe(50);
    Event::assertDispatched(PointsEarned::class);
});

test('it prevents duplicate awards for same related entity', function () {
    $user = User::factory()->create();
    UserStat::create(['user_id' => $user->id]);

    $service = app(PointsService::class);

    $first = $service->awardPoints($user, 50, 'lesson_completion', 'درس 1', 'App\Models\Lesson', 99);
    $second = $service->awardPoints($user, 50, 'lesson_completion', 'درس 1', 'App\Models\Lesson', 99);

    expect($first)->not->toBeNull();
    expect($second)->toBeNull();
    expect(PointsTransaction::where('user_id', $user->id)->count())->toBe(1);
    expect($user->fresh()->stats->available_points)->toBe(50);
});

test('it deducts points and updates spent_points', function () {
    $user = User::factory()->create();
    UserStat::create([
        'user_id' => $user->id,
        'total_points' => 100,
        'available_points' => 100,
    ]);

    $service = app(PointsService::class);
    $transaction = $service->deductPoints($user, 30, 'shop_purchase', 'شراء');

    expect($transaction)->not->toBeNull();
    expect($user->fresh()->stats->available_points)->toBe(70);
    expect($user->fresh()->stats->spent_points)->toBe(30);
});

test('it enforces daily activity limits', function () {
    config(['gamification.daily_limits.specific_activities.comment_post' => 1]);

    $user = User::factory()->create();
    UserStat::create(['user_id' => $user->id]);

    $service = app(PointsService::class);

    $first = $service->awardPoints($user, 5, 'comment_post', 'تعليق 1', 'comments', 1);
    $second = $service->awardPoints($user, 5, 'comment_post', 'تعليق 2', 'comments', 2);

    expect($first)->not->toBeNull();
    expect($second)->toBeNull();
});
