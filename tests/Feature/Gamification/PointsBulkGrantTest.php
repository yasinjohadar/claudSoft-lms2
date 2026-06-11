<?php

use App\Models\CourseEnrollment;
use App\Models\PointsTransaction;
use App\Models\User;
use App\Models\UserStat;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

function pointsGrantAdmin(): User
{
    $role = Role::findOrCreate('admin', 'web');
    $admin = User::factory()->create();
    $admin->assignRole($role);

    return $admin;
}

function pointsGrantStudent(array $overrides = []): User
{
    $role = Role::findOrCreate('student', 'web');
    $student = User::factory()->create($overrides);
    $student->assignRole($role);
    UserStat::create(['user_id' => $student->id]);

    return $student;
}

test('admin can access bulk points grant form', function () {
    $admin = pointsGrantAdmin();

    $response = $this->actingAs($admin)->get(route('admin.gamification.points.create'));

    $response->assertSuccessful();
    $response->assertSee('منح أو تعويض نقاط', false);
    $response->assertSee('عدة مجموعات', false);
});

test('it awards bonus points to single student', function () {
    $admin = pointsGrantAdmin();
    $student = pointsGrantStudent();

    $response = $this->actingAs($admin)->post(route('admin.gamification.points.store'), [
        'operation' => 'bonus',
        'target_type' => 'single',
        'user_id' => $student->id,
        'points' => 100,
        'reason' => 'مكافأة تشجيعية',
    ]);

    $response->assertRedirect(route('admin.gamification.points.index'));
    $response->assertSessionHas('success');

    expect($student->fresh()->stats->available_points)->toBe(100);
    expect(PointsTransaction::where('user_id', $student->id)->where('type', 'bonus')->count())->toBe(1);
});

test('it awards bonus points to all course enrollments', function () {
    $admin = pointsGrantAdmin();
    $studentOne = pointsGrantStudent();
    $studentTwo = pointsGrantStudent();

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

    $response = $this->actingAs($admin)->post(route('admin.gamification.points.store'), [
        'operation' => 'bonus',
        'target_type' => 'course',
        'course_id' => $courseId,
        'points' => 50,
        'reason' => 'مكافأة كورس',
    ]);

    $response->assertRedirect(route('admin.gamification.points.index'));
    $response->assertSessionHas('success');

    expect($studentOne->fresh()->stats->available_points)->toBe(50);
    expect($studentTwo->fresh()->stats->available_points)->toBe(50);
});

test('it awards bonus points to members of multiple groups', function () {
    $admin = pointsGrantAdmin();
    $studentOne = pointsGrantStudent();
    $studentTwo = pointsGrantStudent();
    $studentThree = pointsGrantStudent();

    $groupOneId = DB::table('course_groups')->insertGetId([
        'name' => 'G1 ' . uniqid(),
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groupTwoId = DB::table('course_groups')->insertGetId([
        'name' => 'G2 ' . uniqid(),
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach ([$studentOne, $studentTwo] as $student) {
        DB::table('course_group_members')->insert([
            'group_id' => $groupOneId,
            'student_id' => $student->id,
            'role' => 'member',
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    DB::table('course_group_members')->insert([
        'group_id' => $groupTwoId,
        'student_id' => $studentThree->id,
        'role' => 'member',
        'joined_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($admin)->post(route('admin.gamification.points.store'), [
        'operation' => 'bonus',
        'target_type' => 'multiple_groups',
        'group_ids' => [$groupOneId, $groupTwoId],
        'points' => 25,
        'reason' => 'مكافأة مجموعات',
    ]);

    $response->assertRedirect(route('admin.gamification.points.index'));

    expect($studentOne->fresh()->stats->available_points)->toBe(25);
    expect($studentTwo->fresh()->stats->available_points)->toBe(25);
    expect($studentThree->fresh()->stats->available_points)->toBe(25);
});

test('preview returns recipient counts for bonus operation', function () {
    $admin = pointsGrantAdmin();
    $studentOne = pointsGrantStudent();
    $studentTwo = pointsGrantStudent();

    $response = $this->actingAs($admin)->postJson(route('admin.gamification.points.preview-recipients'), [
        'operation' => 'bonus',
        'target_type' => 'multiple',
        'user_ids' => [$studentOne->id, $studentTwo->id],
        'points' => 10,
    ]);

    $response->assertSuccessful();
    $response->assertJson([
        'total_students' => 2,
        'points_per_student' => 10,
        'total_points' => 20,
        'operation' => 'bonus',
    ]);
});

test('backfill operation completes without requiring points field', function () {
    $admin = pointsGrantAdmin();
    $student = pointsGrantStudent();

    $response = $this->actingAs($admin)->post(route('admin.gamification.points.store'), [
        'operation' => 'backfill',
        'target_type' => 'single',
        'user_id' => $student->id,
        'reason' => 'تعويض نشاط سابق',
    ]);

    $response->assertRedirect(route('admin.gamification.points.index'));
    $response->assertSessionHas('success');
});
