<?php

use App\Models\Assignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('assignment is visible only to students in the targeted group', function () {
    $studentInGroup = User::factory()->create();
    $studentOutsideGroup = User::factory()->create();

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
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('course_group_courses')->insert([
        'course_id' => $courseId,
        'group_id' => $groupId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach ([$studentInGroup, $studentOutsideGroup] as $student) {
        DB::table('course_enrollments')->insert([
            'course_id' => $courseId,
            'student_id' => $student->id,
            'enrollment_status' => 'active',
            'enrollment_date' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    DB::table('course_group_members')->insert([
        'group_id' => $groupId,
        'student_id' => $studentInGroup->id,
        'role' => 'member',
        'joined_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groupAssignment = Assignment::create([
        'title' => 'Group only assignment',
        'course_id' => $courseId,
        'target_group_id' => $groupId,
        'max_grade' => 100,
        'submission_type' => 'both',
        'is_published' => true,
        'is_visible' => true,
    ]);

    $globalAssignment = Assignment::create([
        'title' => 'All students assignment',
        'course_id' => $courseId,
        'target_group_id' => null,
        'max_grade' => 100,
        'submission_type' => 'both',
        'is_published' => true,
        'is_visible' => true,
    ]);

    expect($groupAssignment->isVisibleToStudent($studentInGroup->id))->toBeTrue();
    expect($groupAssignment->isVisibleToStudent($studentOutsideGroup->id))->toBeFalse();

    expect($globalAssignment->isVisibleToStudent($studentInGroup->id))->toBeTrue();
    expect($globalAssignment->isVisibleToStudent($studentOutsideGroup->id))->toBeTrue();

    $visibleToInGroup = Assignment::query()
        ->visibleToStudent($studentInGroup->id)
        ->where('is_published', true)
        ->pluck('id')
        ->all();

    $visibleToOutside = Assignment::query()
        ->visibleToStudent($studentOutsideGroup->id)
        ->where('is_published', true)
        ->pluck('id')
        ->all();

    expect($visibleToInGroup)->toContain($groupAssignment->id, $globalAssignment->id);
    expect($visibleToOutside)->toContain($globalAssignment->id);
    expect($visibleToOutside)->not->toContain($groupAssignment->id);
});
