<?php

use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;
use App\Models\UserStat;
use App\Events\Gamification\AchievementUnlocked;
use App\Services\Gamification\AchievementRecalculationService;
use App\Services\Gamification\GamificationService;
use App\Services\Gamification\LeaderboardService;
use App\Support\Gamification\AchievementCriteriaMapper;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;

function adminUserForAchievements(): User
{
    $role = Role::findOrCreate('admin', 'web');
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function studentUserForAchievements(): User
{
    Role::findOrCreate('student', 'web');
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('student');

    return $user;
}

test('achievement criteria mapper converts form fields to criteria', function () {
    expect(AchievementCriteriaMapper::formToAchievementData('lessons_completed', 10))
        ->toBe(['criteria' => ['field' => 'lessons_completed'], 'target_value' => 10]);

    expect(AchievementCriteriaMapper::formToAchievementData('quizzes_passed', 5))
        ->toBe(['criteria' => ['field' => 'quizzes_completed'], 'target_value' => 5]);

    expect(AchievementCriteriaMapper::formToAchievementData('points_earned', 100))
        ->toBe(['criteria' => ['field' => 'total_points'], 'target_value' => 100]);

    expect(AchievementCriteriaMapper::formToAchievementData(null, null))->toBeNull();
});

test('achievement criteria mapper extracts form fields from stored criteria', function () {
    $form = AchievementCriteriaMapper::criteriaToForm(['field' => 'total_points'], 200);

    expect($form['requirement_type'])->toBe('points_earned');
    expect($form['requirement_value'])->toBe(200);
});

test('achievement criteria mapper formats display text in arabic', function () {
    $text = AchievementCriteriaMapper::formatForDisplay(['field' => 'lessons_completed'], 10);

    expect($text)->toBe('دروس مكتملة: 10');
});

test('admin store creates achievement in achievements table with correct criteria', function () {
    $admin = adminUserForAchievements();
    $slug = 'test-achievement-'.uniqid();

    $response = $this->actingAs($admin)->post(route('admin.gamification.achievements.store'), [
        'name' => 'إنجاز اختبار',
        'slug' => $slug,
        'description' => 'وصف',
        'icon' => '🎯',
        'tier' => 'gold',
        'requirement_type' => 'lessons_completed',
        'requirement_value' => 5,
        'points_reward' => 50,
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('admin.gamification.achievements.index'));

    $achievement = Achievement::where('slug', $slug)->first();

    expect($achievement)->not->toBeNull();
    expect($achievement->criteria)->toBe(['field' => 'lessons_completed']);
    expect($achievement->target_value)->toBe(5);
    expect($achievement->points_reward)->toBe(50);
});

test('admin achievements recalculate route triggers recalculation service', function () {
    $this->mock(AchievementRecalculationService::class, function ($mock) {
        $mock->shouldReceive('migrateGamificationAchievements')->once()->andReturn(0);
        $mock->shouldReceive('recalculateForAllActiveStudents')
            ->once()
            ->andReturn([
                'students' => 3,
                'stats_synced' => 2,
                'initialized' => 1,
                'achievements_completed' => 4,
            ]);
    });

    $admin = adminUserForAchievements();

    $response = $this->actingAs($admin)->post(route('admin.gamification.achievements.recalculate-all'));

    $response->assertRedirect();
    $response->assertSessionHas('success');
});

test('student achievements page shows completed achievements', function () {
    $student = studentUserForAchievements();

    $achievement = Achievement::create([
        'name' => 'Student Page Test',
        'slug' => 'student-page-'.uniqid(),
        'icon' => '⭐',
        'tier' => 'silver',
        'type' => 'general',
        'target_value' => 1,
        'criteria' => ['field' => 'lessons_completed'],
        'points_reward' => 10,
        'is_active' => true,
    ]);

    UserAchievement::create([
        'user_id' => $student->id,
        'achievement_id' => $achievement->id,
        'status' => 'completed',
        'current_value' => 1,
        'target_value' => 1,
        'progress_percentage' => 100,
        'started_at' => now(),
        'completed_at' => now(),
    ]);

    $response = $this->actingAs($student)->get(route('gamification.achievements.index'));

    $response->assertSuccessful();
    $response->assertSee('Student Page Test', false);
    $response->assertSee('إنجازات مكتملة', false);
});

test('achievement recalculation service completes eligible achievements', function () {
    Event::fake([AchievementUnlocked::class]);

    $student = studentUserForAchievements();

    $achievement = Achievement::create([
        'name' => 'Service Recalc Test',
        'slug' => 'service-recalc-'.uniqid(),
        'icon' => '🏆',
        'tier' => 'bronze',
        'type' => 'general',
        'target_value' => 5,
        'criteria' => ['field' => 'total_points'],
        'points_reward' => 0,
        'is_active' => true,
    ]);

    UserStat::create([
        'user_id' => $student->id,
        'total_points' => 100,
    ]);

    $result = app(AchievementRecalculationService::class)->recalculateForUser($student);

    expect($result['achievements_completed'])->toBeGreaterThanOrEqual(1);

    $userAchievement = UserAchievement::where('user_id', $student->id)
        ->where('achievement_id', $achievement->id)
        ->first();

    expect($userAchievement)->not->toBeNull();
    expect($userAchievement->status)->toBe('completed');
});

test('global recalculate includes achievements in success message', function () {
    $this->mock(GamificationService::class, function ($mock) {
        $mock->shouldReceive('recalculateAllStudentStats')
            ->once()
            ->andReturn(['recalculated' => 1, 'failed' => 0]);
    });

    $this->mock(LeaderboardService::class, function ($mock) {
        $mock->shouldReceive('updateAllLeaderboards')
            ->once()
            ->andReturn([]);
    });

    $this->mock(AchievementRecalculationService::class, function ($mock) {
        $mock->shouldReceive('migrateGamificationAchievements')->once()->andReturn(0);
        $mock->shouldReceive('recalculateForAllActiveStudents')
            ->once()
            ->andReturn([
                'students' => 1,
                'stats_synced' => 1,
                'initialized' => 0,
                'achievements_completed' => 2,
            ]);
    });

    $admin = adminUserForAchievements();

    $response = $this->actingAs($admin)->post(route('admin.gamification.recalculate-all'));

    $response->assertRedirect();
    $response->assertSessionHas('success');
    expect(session('success'))->toContain('إنجاز');
});
