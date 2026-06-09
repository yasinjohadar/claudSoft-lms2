<?php

use App\Models\Badge;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Models\UserBadge;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

function manualAwardAdmin(): User
{
    $role = Role::findOrCreate('admin', 'web');
    $admin = User::factory()->create();
    $admin->assignRole($role);

    return $admin;
}

function manualAwardStudent(array $overrides = []): User
{
    $role = Role::findOrCreate('student', 'web');
    $student = User::factory()->create($overrides);
    $student->assignRole($role);

    return $student;
}

function manualAwardBadge(array $overrides = []): Badge
{
    return Badge::create(array_merge([
        'name' => 'Manual Award Badge',
        'slug' => 'manual-award-' . uniqid(),
        'description' => 'Test manual award',
        'type' => 'special',
        'rarity' => 'common',
        'criteria' => null,
        'points_value' => 10,
        'is_active' => true,
        'is_visible' => true,
        'is_hidden' => false,
        'sort_order' => 0,
    ], $overrides));
}

function manualAwardCourseAndGroup(User $studentInGroup, ?User $studentOutsideGroup = null): array
{
    $courseCategoryId = DB::table('course_categories')->insertGetId([
        'name' => 'Cat ' . uniqid(),
        'slug' => 'cat-' . uniqid(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $courseId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Course ' . uniqid(),
        'slug' => 'course-' . uniqid(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groupId = DB::table('course_groups')->insertGetId([
        'name' => 'Group ' . uniqid(),
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('course_group_courses')->insert([
        'course_id' => $courseId,
        'group_id' => $groupId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('course_group_members')->insert([
        'group_id' => $groupId,
        'student_id' => $studentInGroup->id,
        'role' => 'member',
        'joined_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    if ($studentOutsideGroup) {
        CourseEnrollment::create([
            'course_id' => $courseId,
            'student_id' => $studentOutsideGroup->id,
            'enrollment_status' => 'active',
            'enrollment_date' => now(),
            'completion_percentage' => 0,
            'certificate_issued' => false,
        ]);
    }

    CourseEnrollment::create([
        'course_id' => $courseId,
        'student_id' => $studentInGroup->id,
        'enrollment_status' => 'active',
        'enrollment_date' => now(),
        'completion_percentage' => 0,
        'certificate_issued' => false,
    ]);

    return compact('courseId', 'groupId');
}

test('admin can access manual award form', function () {
    $admin = manualAwardAdmin();

    $response = $this->actingAs($admin)->get(route('admin.gamification.badges.award.form'));

    $response->assertSuccessful();
    $response->assertSee('منح شارة يدوياً', false);
});

test('non admin cannot access manual award form', function () {
    $student = manualAwardStudent();

    $response = $this->actingAs($student)->get(route('admin.gamification.badges.award.form'));

    $response->assertForbidden();
});

test('it awards badge to single student', function () {
    $admin = manualAwardAdmin();
    $student = manualAwardStudent();
    $badge = manualAwardBadge();

    $response = $this->actingAs($admin)->post(route('admin.gamification.badges.award.store'), [
        'badge_id' => $badge->id,
        'target_type' => 'single',
        'user_id' => $student->id,
        'reason' => 'تقديراً للتميز',
    ]);

    $response->assertRedirect(route('admin.gamification.badges.show', $badge));
    $response->assertSessionHas('success');

    expect(UserBadge::where('user_id', $student->id)->where('badge_id', $badge->id)->exists())->toBeTrue();
});

test('it skips student who already has the badge', function () {
    $admin = manualAwardAdmin();
    $student = manualAwardStudent();
    $badge = manualAwardBadge();

    UserBadge::create([
        'user_id' => $student->id,
        'badge_id' => $badge->id,
        'awarded_at' => now(),
    ]);

    $response = $this->actingAs($admin)->post(route('admin.gamification.badges.award.store'), [
        'badge_id' => $badge->id,
        'target_type' => 'single',
        'user_id' => $student->id,
    ]);

    $response->assertRedirect(route('admin.gamification.badges.show', $badge));
    $response->assertSessionHas('success');

    expect(UserBadge::where('user_id', $student->id)->where('badge_id', $badge->id)->count())->toBe(1);
    expect(session('success'))->toContain('تخطي');
});

test('it awards badge to all group members', function () {
    $admin = manualAwardAdmin();
    $studentOne = manualAwardStudent();
    $studentTwo = manualAwardStudent();
    $badge = manualAwardBadge();

    $groupId = DB::table('course_groups')->insertGetId([
        'name' => 'Group Bulk ' . uniqid(),
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach ([$studentOne, $studentTwo] as $student) {
        DB::table('course_group_members')->insert([
            'group_id' => $groupId,
            'student_id' => $student->id,
            'role' => 'member',
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $response = $this->actingAs($admin)->post(route('admin.gamification.badges.award.store'), [
        'badge_id' => $badge->id,
        'target_type' => 'group',
        'group_id' => $groupId,
    ]);

    $response->assertRedirect(route('admin.gamification.badges.show', $badge));

    expect(UserBadge::where('badge_id', $badge->id)->whereIn('user_id', [$studentOne->id, $studentTwo->id])->count())->toBe(2);
});

test('it awards badge to all course enrollments', function () {
    $admin = manualAwardAdmin();
    $studentOne = manualAwardStudent();
    $studentTwo = manualAwardStudent();
    $badge = manualAwardBadge();

    $courseCategoryId = DB::table('course_categories')->insertGetId([
        'name' => 'Cat Course ' . uniqid(),
        'slug' => 'cat-course-' . uniqid(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $courseId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Full Course ' . uniqid(),
        'slug' => 'full-course-' . uniqid(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach ([$studentOne, $studentTwo] as $student) {
        CourseEnrollment::create([
            'course_id' => $courseId,
            'student_id' => $student->id,
            'enrollment_status' => 'active',
            'enrollment_date' => now(),
            'completion_percentage' => 0,
            'certificate_issued' => false,
        ]);
    }

    $response = $this->actingAs($admin)->post(route('admin.gamification.badges.award.store'), [
        'badge_id' => $badge->id,
        'target_type' => 'course',
        'course_id' => $courseId,
    ]);

    $response->assertRedirect(route('admin.gamification.badges.show', $badge));

    expect(UserBadge::where('badge_id', $badge->id)->whereIn('user_id', [$studentOne->id, $studentTwo->id])->count())->toBe(2);
});

test('it awards badge only to group members linked to course', function () {
    $admin = manualAwardAdmin();
    $studentInGroup = manualAwardStudent();
    $studentOutsideGroup = manualAwardStudent();
    $badge = manualAwardBadge();

    ['courseId' => $courseId, 'groupId' => $groupId] = manualAwardCourseAndGroup($studentInGroup, $studentOutsideGroup);

    $response = $this->actingAs($admin)->post(route('admin.gamification.badges.award.store'), [
        'badge_id' => $badge->id,
        'target_type' => 'course_group',
        'course_id' => $courseId,
        'group_id' => $groupId,
    ]);

    $response->assertRedirect(route('admin.gamification.badges.show', $badge));

    expect(UserBadge::where('badge_id', $badge->id)->where('user_id', $studentInGroup->id)->exists())->toBeTrue();
    expect(UserBadge::where('badge_id', $badge->id)->where('user_id', $studentOutsideGroup->id)->exists())->toBeFalse();
});

test('preview returns target counts', function () {
    $admin = manualAwardAdmin();
    $student = manualAwardStudent();
    $badge = manualAwardBadge();

    $response = $this->actingAs($admin)->getJson(route('admin.gamification.badges.award.preview', [
        'badge_id' => $badge->id,
        'target_type' => 'single',
        'user_id' => $student->id,
    ]));

    $response->assertSuccessful();
    $response->assertJson([
        'total' => 1,
        'already_have' => 0,
        'will_award' => 1,
    ]);
});
