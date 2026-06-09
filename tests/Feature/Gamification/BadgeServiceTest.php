<?php

use App\Models\Badge;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\UserStat;
use App\Services\Gamification\BadgeService;
use App\Services\Gamification\GamificationService;
function createTestBadge(array $overrides = []): Badge
{
    return Badge::create(array_merge([
        'name' => 'Test Badge',
        'slug' => 'test-badge-' . uniqid(),
        'description' => 'Test description',
        'type' => 'progress',
        'rarity' => 'common',
        'criteria' => ['lessons_completed' => 10],
        'points_value' => 0,
        'is_active' => true,
        'is_visible' => true,
        'is_hidden' => false,
        'sort_order' => 0,
    ], $overrides));
}

test('it awards badge when lessons_completed criteria is met', function () {
    $user = User::factory()->create();
    $threshold = 900000 + random_int(1, 99999);
    $badge = createTestBadge([
        'slug' => 'lessons-test-' . uniqid(),
        'criteria' => ['lessons_completed' => $threshold],
    ]);

    UserStat::create([
        'user_id' => $user->id,
        'lessons_completed' => $threshold,
    ]);

    $service = app(BadgeService::class);
    $awarded = $service->checkAndAwardBadge($user, $badge->slug);

    expect($awarded)->not->toBeNull();
    expect($service->userHasBadge($user, $badge))->toBeTrue();
});

test('it increments quizzes_completed on quiz completion', function () {
    $user = User::factory()->create();
    UserStat::create(['user_id' => $user->id]);

    $service = app(GamificationService::class);
    $result = $service->handleQuizCompletion($user, 1, 8, 10);

    expect($result['success'])->toBeTrue();
    expect($user->fresh()->stats->quizzes_completed)->toBe(1);
});

test('it awards streak badge after login stats are updated', function () {
    $user = User::factory()->create();
    $threshold = 800000 + random_int(1, 99999);
    $badge = createTestBadge([
        'slug' => 'streak-test-' . uniqid(),
        'criteria' => ['current_streak' => $threshold],
    ]);

    UserStat::create([
        'user_id' => $user->id,
        'current_streak' => $threshold,
    ]);

    $service = app(BadgeService::class);
    $awarded = $service->checkAndAwardBadge($user, $badge->slug);

    expect($awarded)->not->toBeNull();
    expect($service->userHasBadge($user, $badge))->toBeTrue();
});

test('it does not auto award badge with null criteria', function () {
    $user = User::factory()->create();
    createTestBadge([
        'slug' => 'manual-only',
        'criteria' => null,
    ]);

    UserStat::create(['user_id' => $user->id]);

    $service = app(BadgeService::class);
    $awarded = $service->checkAllBadgesWithCascade($user);

    expect($awarded)->toBeEmpty();
});

test('it does not auto award badge with unsupported criteria key', function () {
    $user = User::factory()->create();
    createTestBadge([
        'slug' => 'supporter-test',
        'criteria' => ['referrals_count' => 3],
    ]);

    UserStat::create(['user_id' => $user->id]);

    $service = app(BadgeService::class);
    $awarded = $service->checkAllBadgesWithCascade($user);

    expect($awarded)->toBeEmpty();
});

test('it cascades meta badges based on total_badges', function () {
    $user = User::factory()->create();
    $suffix = uniqid();

    $badgeOne = createTestBadge([
        'slug' => 'meta-one-' . $suffix,
        'criteria' => ['lessons_completed' => 700001],
    ]);
    $badgeTwo = createTestBadge([
        'slug' => 'meta-two-' . $suffix,
        'criteria' => ['lessons_completed' => 700002],
    ]);
    $hunter = createTestBadge([
        'slug' => 'badge-hunter-' . $suffix,
        'criteria' => ['total_badges' => 2],
        'rarity' => 'epic',
    ]);

    UserStat::create([
        'user_id' => $user->id,
        'lessons_completed' => 700002,
        'total_badges' => 0,
    ]);

    $service = app(BadgeService::class);
    $awarded = $service->checkAllBadgesWithCascade($user);

    $awardedBadgeIds = collect($awarded)->pluck('badge_id')->all();

    expect($awardedBadgeIds)->toContain($badgeOne->id, $badgeTwo->id, $hunter->id);
    expect($service->userHasBadge($user, $hunter))->toBeTrue();
});

test('getBadgeProgress returns awarded_at for earned badges', function () {
    $user = User::factory()->create();
    $badge = createTestBadge(['slug' => 'progress-test']);

    $awardedAt = now()->subDay();
    UserBadge::create([
        'user_id' => $user->id,
        'badge_id' => $badge->id,
        'awarded_at' => $awardedAt,
    ]);

    UserStat::create([
        'user_id' => $user->id,
        'total_badges' => 1,
    ]);

    $service = app(BadgeService::class);
    $progress = $service->getBadgeProgress($user, $badge);

    expect($progress['earned'])->toBeTrue();
    expect($progress['awarded_at']->toDateTimeString())->toBe($awardedAt->toDateTimeString());
});

test('recalc command resyncs inflated quiz stats to actual attempts', function () {
    $user = User::factory()->create();

    UserStat::create([
        'user_id' => $user->id,
        'quizzes_completed' => 99,
    ]);

    $this->artisan('gamification:recalc-badges', ['--user' => $user->id])
        ->assertSuccessful();

    expect($user->fresh()->stats->quizzes_completed)->toBe(0);
});
