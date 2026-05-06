<?php

use App\Models\StudentWeeklyReport;
use App\Models\User;
use App\Services\Reports\StudentWeeklyReportService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

test('it closes overdue weekly reports', function () {
    $student = User::factory()->create();

    $report = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'report_title' => 'تقرير أسبوعي',
        'due_at' => now()->subDay(),
        'status' => StudentWeeklyReport::STATUS_DRAFT,
    ]);

    $service = app(StudentWeeklyReportService::class);
    $closed = $service->closeOverdueReports();

    expect($closed)->toBe(1);
    expect($report->fresh()->status)->toBe(StudentWeeklyReport::STATUS_CLOSED);
    expect($report->fresh()->closed_at)->not->toBeNull();
});

test('it resolves students by selected course and group', function () {
    $studentInGroup = User::factory()->create();
    $studentOutsideGroup = User::factory()->create();

    $courseCategoryId = DB::table('course_categories')->insertGetId([
        'name' => 'Category 1',
        'slug' => 'category-1',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $courseId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Course A',
        'slug' => 'course-a',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groupId = DB::table('course_groups')->insertGetId([
        'name' => 'Group A',
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

    $service = app(StudentWeeklyReportService::class);
    $students = $service->resolveStudentsByCourseAndGroup($courseId, $groupId);

    expect($students->pluck('id')->all())->toBe([$studentInGroup->id]);
    expect($students->pluck('id')->all())->not->toContain($studentOutsideGroup->id);
});

test('it rejects group that is not linked to the selected course', function () {
    $courseCategoryId = DB::table('course_categories')->insertGetId([
        'name' => 'Category 2',
        'slug' => 'category-2',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $courseId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Course B',
        'slug' => 'course-b',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groupId = DB::table('course_groups')->insertGetId([
        'name' => 'Group B',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $service = app(StudentWeeklyReportService::class);

    expect(fn () => $service->resolveStudentsByCourseAndGroup($courseId, $groupId))
        ->toThrow(ValidationException::class);
});

