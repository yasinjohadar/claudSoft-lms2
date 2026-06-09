<?php

use App\Models\Badge;
use App\Models\User;
use App\Support\Gamification\BadgeCriteriaMapper;
use Spatie\Permission\Models\Role;

function adminUserForBadges(): User
{
    $role = Role::findOrCreate('admin', 'web');
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('admin badges index reads from badges table used by students', function () {
    $admin = adminUserForBadges();
    $seededCount = Badge::count();

    expect($seededCount)->toBeGreaterThan(0);

    $response = $this->actingAs($admin)->get(route('admin.gamification.badges.index'));

    $response->assertSuccessful();
    $response->assertSee(Badge::orderBy('sort_order')->value('name'), false);
});

test('admin store creates badge visible to student query', function () {
    $admin = adminUserForBadges();
    $slug = 'admin-created-' . uniqid();

    $response = $this->actingAs($admin)->post(route('admin.gamification.badges.store'), [
        'name' => 'شارة من الأدمن',
        'slug' => $slug,
        'description' => 'وصف اختبار',
        'icon' => '🎖️',
        'type' => 'progress',
        'category' => 'lessons',
        'rarity' => 'common',
        'requirement_type' => 'lessons_completed',
        'requirement_value' => 3,
        'points_reward' => 25,
        'sort_order' => 99,
        'is_active' => '1',
        'is_visible' => '1',
    ]);

    $response->assertRedirect(route('admin.gamification.badges.index'));

    $badge = Badge::where('slug', $slug)->first();

    expect($badge)->not->toBeNull();
    expect($badge->criteria)->toBe(['lessons_completed' => 3]);
    expect($badge->points_value)->toBe(25);

    $visibleToStudent = Badge::where('is_active', true)
        ->where('is_visible', true)
        ->where('slug', $slug)
        ->exists();

    expect($visibleToStudent)->toBeTrue();
});

test('badge criteria mapper converts admin form fields to runtime criteria', function () {
    expect(BadgeCriteriaMapper::formToCriteria('quizzes_passed', 10))
        ->toBe(['quizzes_completed' => 10]);

    expect(BadgeCriteriaMapper::formToCriteria('streak_days', 7))
        ->toBe(['current_streak' => 7]);

    expect(BadgeCriteriaMapper::formToCriteria(null, null))->toBeNull();
});

test('badge criteria mapper extracts form fields from stored criteria', function () {
    $form = BadgeCriteriaMapper::criteriaToForm(['lessons_completed' => 10]);

    expect($form['requirement_type'])->toBe('lessons_completed');
    expect($form['requirement_value'])->toBe(10);
});
