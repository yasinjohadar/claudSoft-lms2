<?php

use App\Models\StudentWeeklyReport;
use App\Models\StudentWeeklyReportSchedule;
use App\Models\User;
use App\Services\Reports\StudentWeeklyReportScheduleService;
use Illuminate\Support\Facades\DB;

test('it generates reports for due course-group schedule', function () {
    $admin = User::factory()->create();
    $studentInGroup = User::factory()->create();

    $courseCategoryId = DB::table('course_categories')->insertGetId([
        'name' => 'Schedule Category',
        'slug' => 'schedule-category',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $courseId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Schedule Course',
        'slug' => 'schedule-course',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groupId = DB::table('course_groups')->insertGetId([
        'name' => 'Schedule Group',
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

    $schedule = StudentWeeklyReportSchedule::create([
        'name' => 'جدولة اختبارية',
        'is_active' => true,
        'weekday' => now()->dayOfWeek,
        'due_time' => now()->format('H:i:s'),
        'target_scope' => 'specific_students',
        'target_course_id' => $courseId,
        'target_group_id' => $groupId,
        'next_run_at' => now()->subMinute(),
        'created_by_admin_id' => $admin->id,
    ]);

    $service = app(StudentWeeklyReportScheduleService::class);
    $created = $service->runDueSchedules();

    expect($created)->toBe(1);
    expect(StudentWeeklyReport::count())->toBe(1);
    expect(StudentWeeklyReport::first()?->student_id)->toBe($studentInGroup->id);
    expect($schedule->fresh()->next_run_at->isFuture())->toBeTrue();
});

