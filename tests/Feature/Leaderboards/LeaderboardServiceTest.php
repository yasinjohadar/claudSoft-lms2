<?php

use App\Models\Leaderboard;
use App\Models\LeaderboardEntry;
use App\Models\PointsTransaction;
use App\Models\User;
use App\Models\UserStat;
use App\Services\Gamification\LeaderboardService;
use Spatie\Permission\Models\Role;

function lbStudent(array $stats = []): User
{
    Role::findOrCreate('student', 'web');
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('student');
    UserStat::create(array_merge(['user_id' => $user->id], $stats));

    return $user;
}

function lbBoard(array $overrides = []): Leaderboard
{
    return Leaderboard::create(array_merge([
        'name' => 'Test Board '.uniqid(),
        'slug' => 'test-'.uniqid(),
        'type' => 'global',
        'metric' => 'total_points',
        'period' => 'all_time',
        'max_entries' => 100,
        'min_score' => 0,
        'has_divisions' => true,
        'is_active' => true,
        'is_visible' => true,
        'sort_order' => 0,
    ], $overrides));
}

test('updateLeaderboard ranks students by total points', function () {
    $board = lbBoard();
    $low = lbStudent(['total_points' => 900001, 'available_points' => 900001]);
    $high = lbStudent(['total_points' => 900003, 'available_points' => 900003]);
    $mid = lbStudent(['total_points' => 900002, 'available_points' => 900002]);
    $ids = [$low->id, $high->id, $mid->id];

    app(LeaderboardService::class)->updateLeaderboard($board);

    $entries = LeaderboardEntry::where('leaderboard_id', $board->id)
        ->whereIn('user_id', $ids)
        ->orderByDesc('score')
        ->get();

    expect($entries)->toHaveCount(3);
    expect($entries->pluck('score')->all())->toBe([900003, 900002, 900001]);
});

test('calculateDivision assigns tiers correctly', function () {
    $board = lbBoard();
    $service = app(LeaderboardService::class);
    $method = new ReflectionMethod($service, 'calculateDivision');
    $method->setAccessible(true);

    expect($method->invoke($service, 6000, $board))->toBe('gold');
    expect($method->invoke($service, 50, $board))->toBe('bronze');
    expect($method->invoke($service, 60000, $board))->toBe('diamond');
});

test('weekly period uses points transactions not lifetime stats', function () {
    $board = lbBoard(['period' => 'weekly', 'metric' => 'total_points', 'type' => 'weekly']);
    lbStudent(['total_points' => 9999, 'available_points' => 9999]);
    $quiet = lbStudent(['total_points' => 10, 'available_points' => 10]);

    PointsTransaction::create([
        'user_id' => $quiet->id,
        'type' => 'earn',
        'points' => 500000,
        'balance_before' => 0,
        'balance_after' => 500000,
        'source' => 'lesson_completion',
        'description' => 'test',
        'related_type' => User::class,
        'related_id' => $quiet->id,
        'created_at' => now(),
    ]);

    app(LeaderboardService::class)->updateLeaderboard($board);

    $entry = LeaderboardEntry::where('leaderboard_id', $board->id)
        ->where('user_id', $quiet->id)
        ->first();

    expect($entry)->not->toBeNull();
    expect($entry->score)->toBe(500000);
    expect($entry->rank)->toBe(1);
});

test('entries use metrics column not metadata', function () {
    $board = lbBoard();
    $student = lbStudent(['total_points' => 999999, 'available_points' => 999999, 'current_level' => 5]);

    app(LeaderboardService::class)->updateLeaderboard($board);

    $entry = LeaderboardEntry::where('user_id', $student->id)->first();

    expect($entry)->not->toBeNull();
    expect($entry->metrics)->toBeArray();
    expect($entry->metrics['current_level'])->toBe(5);
    expect($entry->is_top_1)->toBeTrue();
});
