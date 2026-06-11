<?php

use App\Models\Leaderboard;
use App\Models\PointsTransaction;
use App\Models\User;
use App\Models\UserStat;
use App\Services\Gamification\AchievementRecalculationService;
use Spatie\Permission\Models\Role;

function recalcAdmin(): User
{
    Role::findOrCreate('admin', 'web');
    Role::findOrCreate('student', 'web');
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    return $admin;
}

test('admin can recalculate points and leaderboards', function () {
    $this->mock(AchievementRecalculationService::class, function ($mock) {
        $mock->shouldReceive('migrateGamificationAchievements')->once()->andReturn(0);
        $mock->shouldReceive('recalculateForAllActiveStudents')
            ->once()
            ->andReturn([
                'students' => 1,
                'stats_synced' => 0,
                'initialized' => 0,
                'achievements_completed' => 0,
            ]);
    });

    $student = User::factory()->create(['is_active' => true]);
    $student->assignRole('student');

    UserStat::create([
        'user_id' => $student->id,
        'total_points' => 0,
        'available_points' => 0,
    ]);

    PointsTransaction::create([
        'user_id' => $student->id,
        'points' => 150,
        'type' => 'earn',
        'source' => 'admin_grant',
        'description' => 'test grant',
        'related_type' => User::class,
        'related_id' => $student->id,
    ]);

    Leaderboard::create([
        'name' => 'Recalc Board',
        'slug' => 'recalc-'.uniqid(),
        'type' => 'global',
        'metric' => 'total_points',
        'period' => 'all_time',
        'is_active' => true,
        'is_visible' => true,
    ]);

    $response = $this->actingAs(recalcAdmin())->post(route('admin.gamification.recalculate-all'));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($student->stats()->first()->total_points)->toBe(150);
});

test('recalculate button appears on leaderboards index', function () {
    $response = $this->actingAs(recalcAdmin())->get(route('admin.gamification.leaderboards.index'));

    $response->assertSuccessful();
    $response->assertSee('إعادة احتساب النقاط واللوحات', false);
});
