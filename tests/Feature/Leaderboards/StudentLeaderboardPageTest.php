<?php

use App\Models\Leaderboard;
use App\Models\LeaderboardEntry;
use App\Models\User;
use App\Models\UserStat;
use Spatie\Permission\Models\Role;

function lbPageStudent(): User
{
    Role::findOrCreate('student', 'web');
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('student');
    UserStat::create(['user_id' => $user->id]);

    return $user;
}

test('student leaderboards index lists active boards', function () {
    Leaderboard::create([
        'name' => 'لوحة ظاهرة',
        'slug' => 'visible-'.uniqid(),
        'type' => 'global',
        'metric' => 'total_points',
        'period' => 'all_time',
        'is_active' => true,
        'is_visible' => true,
    ]);

    $response = $this->actingAs(lbPageStudent())->get(route('gamification.leaderboards.index'));

    $response->assertSuccessful();
    $response->assertSee('لوحة ظاهرة', false);
});

test('student leaderboard show displays entries', function () {
    $student = lbPageStudent();
    $board = Leaderboard::create([
        'name' => 'لوحة التفاصيل',
        'slug' => 'detail-'.uniqid(),
        'type' => 'global',
        'metric' => 'total_points',
        'period' => 'all_time',
        'is_active' => true,
        'is_visible' => true,
    ]);

    LeaderboardEntry::create([
        'leaderboard_id' => $board->id,
        'user_id' => $student->id,
        'rank' => 1,
        'score' => 100,
        'division' => 'bronze',
        'metrics' => [],
        'is_top_1' => true,
        'is_top_3' => true,
        'is_top_10' => true,
    ]);

    $response = $this->actingAs($student)->get(route('gamification.leaderboards.show', $board));

    $response->assertSuccessful();
    $response->assertSee('لوحة التفاصيل', false);
    $response->assertSee('100', false);
});

test('student my rank page loads', function () {
    $response = $this->actingAs(lbPageStudent())->get(route('gamification.leaderboards.my-rank'));

    $response->assertSuccessful();
    $response->assertSee('ترتيبي', false);
});
