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

test('it resolves courses for student report using target course when set', function () {
    $student = User::factory()->create();

    $courseCategoryId = DB::table('course_categories')->insertGetId([
        'name' => 'Category Target',
        'slug' => 'category-target',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $targetCourseId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Target Course',
        'slug' => 'target-course',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $otherCourseId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Other Course',
        'slug' => 'other-course',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groupId = DB::table('course_groups')->insertGetId([
        'name' => 'Group Target',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach ([$targetCourseId, $otherCourseId] as $courseId) {
        DB::table('course_group_courses')->insert([
            'course_id' => $courseId,
            'group_id' => $groupId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    DB::table('course_group_members')->insert([
        'group_id' => $groupId,
        'student_id' => $student->id,
        'role' => 'member',
        'joined_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $report = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'target_course_id' => $targetCourseId,
        'target_group_id' => $groupId,
        'report_title' => 'Targeted report',
        'status' => StudentWeeklyReport::STATUS_DRAFT,
    ]);

    $service = app(StudentWeeklyReportService::class);
    $courses = $service->resolveCoursesForStudentReport($student->id, $report);

    expect($courses)->toHaveCount(1);
    expect($courses->first()->id)->toBe($targetCourseId);
});

test('it resolves group courses for student report when target course is not set', function () {
    $student = User::factory()->create();

    $courseCategoryId = DB::table('course_categories')->insertGetId([
        'name' => 'Category Groups',
        'slug' => 'category-groups',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groupCourseId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Group Course',
        'slug' => 'group-course',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $outsideCourseId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Outside Course',
        'slug' => 'outside-course',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groupId = DB::table('course_groups')->insertGetId([
        'name' => 'Student Group',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('course_group_courses')->insert([
        'course_id' => $groupCourseId,
        'group_id' => $groupId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('course_group_members')->insert([
        'group_id' => $groupId,
        'student_id' => $student->id,
        'role' => 'member',
        'joined_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $report = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'report_title' => 'Group scoped report',
        'status' => StudentWeeklyReport::STATUS_DRAFT,
    ]);

    $service = app(StudentWeeklyReportService::class);
    $courses = $service->resolveCoursesForStudentReport($student->id, $report);

    expect($courses->pluck('id')->all())->toBe([$groupCourseId]);
    expect($courses->pluck('id')->all())->not->toContain($outsideCourseId);
});

test('it flattens multi module lesson payload entries', function () {
    $service = app(StudentWeeklyReportService::class);

    $flattened = $service->flattenLessonsPayload([
        [
            'course_id' => 10,
            'module_ids' => [101, 102],
        ],
        [
            'course_id' => 11,
            'module_id' => 201,
        ],
    ]);

    expect($flattened)->toBe([
        ['course_id' => 10, 'module_id' => 101],
        ['course_id' => 10, 'module_id' => 102],
        ['course_id' => 11, 'module_id' => 201],
    ]);
});

test('it saves multiple modules for the same course on a student report', function () {
    $student = User::factory()->create();

    $courseCategoryId = DB::table('course_categories')->insertGetId([
        'name' => 'Category Save',
        'slug' => 'category-save',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $courseId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Save Course',
        'slug' => 'save-course',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groupId = DB::table('course_groups')->insertGetId([
        'name' => 'Save Group',
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
        'student_id' => $student->id,
        'role' => 'member',
        'joined_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $moduleOneId = DB::table('course_modules')->insertGetId([
        'course_id' => $courseId,
        'module_type' => 'resource',
        'modulable_id' => 1,
        'modulable_type' => 'App\\Models\\Resource',
        'title' => 'Module One',
        'sort_order' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $moduleTwoId = DB::table('course_modules')->insertGetId([
        'course_id' => $courseId,
        'module_type' => 'resource',
        'modulable_id' => 2,
        'modulable_type' => 'App\\Models\\Resource',
        'title' => 'Module Two',
        'sort_order' => 2,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $report = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'target_course_id' => $courseId,
        'report_title' => 'Save modules report',
        'status' => StudentWeeklyReport::STATUS_DRAFT,
    ]);

    $service = app(StudentWeeklyReportService::class);
    $service->saveStudentReport($report, [
        'student_details' => '<p>Report body</p>',
        'student_notes' => 'Notes',
        'lessons' => [
            ['course_id' => $courseId, 'module_id' => $moduleOneId],
            ['course_id' => $courseId, 'module_id' => $moduleTwoId],
        ],
    ]);

    $report->refresh()->load('selectedLessons');

    expect($report->student_details)->toBe('<p>Report body</p>');
    expect($report->selectedLessons)->toHaveCount(2);
    expect($report->selectedLessons->pluck('module_id')->sort()->values()->all())
        ->toBe(collect([$moduleOneId, $moduleTwoId])->sort()->values()->all());
});

test('it prevents resubmitting an already submitted weekly report', function () {
    $student = User::factory()->create();

    $report = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'report_title' => 'Submitted report',
        'status' => StudentWeeklyReport::STATUS_SUBMITTED,
        'submitted_at' => now(),
    ]);

    $service = app(StudentWeeklyReportService::class);

    expect(fn () => $service->submitReport($report, [
        'student_details' => 'Updated',
        'student_notes' => null,
        'lessons' => [],
    ]))->toThrow(ValidationException::class);
});

test('it prevents editing a submitted weekly report', function () {
    $student = User::factory()->create();

    $report = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'report_title' => 'Locked report',
        'status' => StudentWeeklyReport::STATUS_SUBMITTED,
        'submitted_at' => now(),
    ]);

    $service = app(StudentWeeklyReportService::class);

    expect(fn () => $service->saveStudentReport($report, [
        'student_details' => 'Changed',
        'student_notes' => null,
        'lessons' => [],
    ]))->toThrow(ValidationException::class);
});

test('not submitted scope includes draft and closed reports without submitted_at', function () {
    $student = User::factory()->create();

    $draft = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'report_title' => 'Draft report',
        'status' => StudentWeeklyReport::STATUS_DRAFT,
    ]);

    $closed = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'report_title' => 'Closed report',
        'status' => StudentWeeklyReport::STATUS_CLOSED,
        'closed_at' => now(),
    ]);

    $submitted = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'report_title' => 'Submitted report',
        'status' => StudentWeeklyReport::STATUS_SUBMITTED,
        'submitted_at' => now(),
    ]);

    $reviewed = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'report_title' => 'Reviewed report',
        'status' => StudentWeeklyReport::STATUS_REVIEWED,
        'submitted_at' => now(),
        'reviewed_at' => now(),
    ]);

    $notSubmittedIds = StudentWeeklyReport::query()->notSubmitted()->pluck('id')->all();

    expect($notSubmittedIds)->toContain($draft->id, $closed->id);
    expect($notSubmittedIds)->not->toContain($submitted->id, $reviewed->id);
});

test('it filters submitted admin reports by course and group', function () {
    $student = User::factory()->create();

    $courseCategoryId = DB::table('course_categories')->insertGetId([
        'name' => 'Category Filter',
        'slug' => 'category-filter',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $courseAId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Course A Filter',
        'slug' => 'course-a-filter',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $courseBId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Course B Filter',
        'slug' => 'course-b-filter',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groupAId = DB::table('course_groups')->insertGetId([
        'name' => 'Group A Filter',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groupBId = DB::table('course_groups')->insertGetId([
        'name' => 'Group B Filter',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach ([[$courseAId, $groupAId], [$courseBId, $groupBId]] as [$courseId, $groupId]) {
        DB::table('course_group_courses')->insert([
            'course_id' => $courseId,
            'group_id' => $groupId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $reportA = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'target_course_id' => $courseAId,
        'target_group_id' => $groupAId,
        'report_title' => 'Submitted A',
        'status' => StudentWeeklyReport::STATUS_SUBMITTED,
        'submitted_at' => now(),
    ]);

    $reportB = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'target_course_id' => $courseBId,
        'target_group_id' => $groupBId,
        'report_title' => 'Submitted B',
        'status' => StudentWeeklyReport::STATUS_REVIEWED,
        'submitted_at' => now(),
        'reviewed_at' => now(),
    ]);

    $service = app(StudentWeeklyReportService::class);

    $filtered = $service->getSubmittedReportsForAdmin($courseAId, $groupAId);

    expect($filtered->pluck('id')->all())->toBe([$reportA->id]);
    expect($filtered->pluck('id')->all())->not->toContain($reportB->id);
});

test('it filters pending admin reports by course and group including draft and closed', function () {
    $student = User::factory()->create();

    $courseCategoryId = DB::table('course_categories')->insertGetId([
        'name' => 'Category Pending',
        'slug' => 'category-pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $courseId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Course Pending',
        'slug' => 'course-pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $otherCourseId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Other Course Pending',
        'slug' => 'other-course-pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groupId = DB::table('course_groups')->insertGetId([
        'name' => 'Group Pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $otherGroupId = DB::table('course_groups')->insertGetId([
        'name' => 'Other Group Pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach ([[$courseId, $groupId], [$otherCourseId, $otherGroupId]] as [$linkedCourseId, $linkedGroupId]) {
        DB::table('course_group_courses')->insert([
            'course_id' => $linkedCourseId,
            'group_id' => $linkedGroupId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $draftReport = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'target_course_id' => $courseId,
        'target_group_id' => $groupId,
        'report_title' => 'Pending draft',
        'status' => StudentWeeklyReport::STATUS_DRAFT,
    ]);

    $closedReport = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'target_course_id' => $courseId,
        'target_group_id' => $groupId,
        'report_title' => 'Pending closed',
        'status' => StudentWeeklyReport::STATUS_CLOSED,
        'closed_at' => now(),
    ]);

    $submittedReport = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'target_course_id' => $courseId,
        'target_group_id' => $groupId,
        'report_title' => 'Already submitted',
        'status' => StudentWeeklyReport::STATUS_SUBMITTED,
        'submitted_at' => now(),
    ]);

    $otherGroupReport = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'target_course_id' => $otherCourseId,
        'target_group_id' => $otherGroupId,
        'report_title' => 'Other group draft',
        'status' => StudentWeeklyReport::STATUS_DRAFT,
    ]);

    $service = app(StudentWeeklyReportService::class);
    $pending = $service->getPendingReportsForAdmin($courseId, $groupId);

    expect($pending->pluck('id')->sort()->values()->all())
        ->toBe(collect([$draftReport->id, $closedReport->id])->sort()->values()->all());
    expect($pending->pluck('id')->all())->not->toContain($submittedReport->id, $otherGroupReport->id);
});

test('it returns all admin reports across every status without status filter', function () {
    $student = User::factory()->create();

    $draft = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'report_title' => 'All draft',
        'status' => StudentWeeklyReport::STATUS_DRAFT,
    ]);

    $submitted = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'report_title' => 'All submitted',
        'status' => StudentWeeklyReport::STATUS_SUBMITTED,
        'submitted_at' => now(),
    ]);

    $reviewed = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'report_title' => 'All reviewed',
        'status' => StudentWeeklyReport::STATUS_REVIEWED,
        'submitted_at' => now(),
        'reviewed_at' => now(),
    ]);

    $closed = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'report_title' => 'All closed',
        'status' => StudentWeeklyReport::STATUS_CLOSED,
        'closed_at' => now(),
    ]);

    $service = app(StudentWeeklyReportService::class);
    $allReports = $service->getAllReportsForAdmin(perPage: 50);

    expect($allReports->pluck('id')->all())->toContain($draft->id, $submitted->id, $reviewed->id, $closed->id);
});

test('it filters all admin reports by course group and status', function () {
    $student = User::factory()->create();

    $courseCategoryId = DB::table('course_categories')->insertGetId([
        'name' => 'Category All',
        'slug' => 'category-all',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $courseId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Course All',
        'slug' => 'course-all',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $otherCourseId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Other Course All',
        'slug' => 'other-course-all',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groupId = DB::table('course_groups')->insertGetId([
        'name' => 'Group All',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $otherGroupId = DB::table('course_groups')->insertGetId([
        'name' => 'Other Group All',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach ([[$courseId, $groupId], [$otherCourseId, $otherGroupId]] as [$linkedCourseId, $linkedGroupId]) {
        DB::table('course_group_courses')->insert([
            'course_id' => $linkedCourseId,
            'group_id' => $linkedGroupId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $matchingDraft = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'target_course_id' => $courseId,
        'target_group_id' => $groupId,
        'report_title' => 'Matching draft',
        'status' => StudentWeeklyReport::STATUS_DRAFT,
    ]);

    $matchingSubmitted = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'target_course_id' => $courseId,
        'target_group_id' => $groupId,
        'report_title' => 'Matching submitted',
        'status' => StudentWeeklyReport::STATUS_SUBMITTED,
        'submitted_at' => now(),
    ]);

    $otherGroupDraft = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'target_course_id' => $otherCourseId,
        'target_group_id' => $otherGroupId,
        'report_title' => 'Other group draft',
        'status' => StudentWeeklyReport::STATUS_DRAFT,
    ]);

    $service = app(StudentWeeklyReportService::class);

    $filteredDraft = $service->getAllReportsForAdmin($courseId, $groupId, StudentWeeklyReport::STATUS_DRAFT, 50);
    $statusCounts = $service->getAllReportsStatusCounts($courseId, $groupId);

    expect($filteredDraft->pluck('id')->all())->toBe([$matchingDraft->id]);
    expect($statusCounts)->toBe([
        'total' => 2,
        'draft' => 1,
        'submitted' => 1,
        'reviewed' => 0,
        'closed' => 0,
    ]);
    expect($filteredDraft->pluck('id')->all())->not->toContain($matchingSubmitted->id, $otherGroupDraft->id);
});

test('it groups admin created reports into batches with student details', function () {
    $admin = User::factory()->create();
    $studentOne = User::factory()->create();
    $studentTwo = User::factory()->create();

    $courseCategoryId = DB::table('course_categories')->insertGetId([
        'name' => 'Category Created',
        'slug' => 'category-created',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $courseId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Course Created',
        'slug' => 'course-created',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groupId = DB::table('course_groups')->insertGetId([
        'name' => 'Group Created',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('course_group_courses')->insert([
        'course_id' => $courseId,
        'group_id' => $groupId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $createdAt = now()->startOfMinute();
    $dueAt = now()->addWeek();

    foreach ([$studentOne, $studentTwo] as $student) {
        StudentWeeklyReport::create([
            'student_id' => $student->id,
            'created_by_admin_id' => $admin->id,
            'target_course_id' => $courseId,
            'target_group_id' => $groupId,
            'report_title' => 'Batch Report Title',
            'due_at' => $dueAt,
            'status' => StudentWeeklyReport::STATUS_DRAFT,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    StudentWeeklyReport::create([
        'student_id' => $studentOne->id,
        'report_title' => 'Report without admin',
        'status' => StudentWeeklyReport::STATUS_DRAFT,
    ]);

    $service = app(StudentWeeklyReportService::class);
    $batches = $service->getAdminCreatedReportBatches($courseId, $groupId, 50);
    $stats = $service->getAdminCreatedBatchStats($courseId, $groupId);

    expect($batches)->toHaveCount(1);
    expect($batches->first()['students_count'])->toBe(2);

    $batchDetails = $service->getAdminCreatedBatchByKey($batches->first()['key']);
    expect($batchDetails['student_reports'])->toHaveCount(2);

    expect($stats)->toBe([
        'batches_count' => 1,
        'students_count' => 2,
        'submitted_count' => 0,
        'pending_count' => 2,
    ]);
});

test('it separates admin created report batches by title and due date', function () {
    $admin = User::factory()->create();
    $student = User::factory()->create();

    $courseCategoryId = DB::table('course_categories')->insertGetId([
        'name' => 'Category Separate',
        'slug' => 'category-separate-' . uniqid(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $courseId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Course Separate',
        'slug' => 'course-separate-' . uniqid(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groupId = DB::table('course_groups')->insertGetId([
        'name' => 'Group Separate ' . uniqid(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $base = [
        'student_id' => $student->id,
        'created_by_admin_id' => $admin->id,
        'target_course_id' => $courseId,
        'target_group_id' => $groupId,
        'created_at' => now()->startOfMinute(),
        'updated_at' => now()->startOfMinute(),
        'status' => StudentWeeklyReport::STATUS_DRAFT,
    ];

    StudentWeeklyReport::create(array_merge($base, [
        'report_title' => 'Batch A Separate',
        'due_at' => now()->addDays(3),
    ]));

    StudentWeeklyReport::create(array_merge($base, [
        'report_title' => 'Batch B Separate',
        'due_at' => now()->addDays(7),
    ]));

    $service = app(StudentWeeklyReportService::class);
    $batches = $service->getAdminCreatedReportBatches($courseId, $groupId, 50);

    expect($batches)->toHaveCount(2);
    expect($batches->pluck('report_title')->sort()->values()->all())
        ->toBe(['Batch A Separate', 'Batch B Separate']);
});

test('it loads admin created batch students on a separate batch key lookup', function () {
    $admin = User::factory()->create();
    $student = User::factory()->create();

    $report = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'created_by_admin_id' => $admin->id,
        'report_title' => 'Lookup Batch Report',
        'due_at' => now()->addWeek(),
        'status' => StudentWeeklyReport::STATUS_DRAFT,
        'created_at' => now()->startOfMinute(),
        'updated_at' => now()->startOfMinute(),
    ]);

    $service = app(StudentWeeklyReportService::class);
    $batches = $service->getAdminCreatedReportBatches(perPage: 50);
    $batchKey = $batches->firstWhere('report_title', 'Lookup Batch Report')['key'] ?? null;

    expect($batchKey)->not->toBeNull();

    $batch = $service->getAdminCreatedBatchByKey($batchKey);

    expect($batch)->not->toBeNull();
    expect($batch['student_reports'])->toHaveCount(1);
    expect($batch['student_reports']->first()->id)->toBe($report->id);
});

test('it filters admin created batch students by search and status', function () {
    $admin = User::factory()->create();
    $studentDraft = User::factory()->create([
        'name' => 'Milad Ebrahim',
        'email' => 'milad@example.com',
    ]);
    $studentSubmitted = User::factory()->create([
        'name' => 'Motaz Dalol',
        'email' => 'motaz@example.com',
    ]);

    $createdAt = now()->startOfMinute();

    $draftReport = StudentWeeklyReport::create([
        'student_id' => $studentDraft->id,
        'created_by_admin_id' => $admin->id,
        'report_title' => 'Filter Batch Report',
        'status' => StudentWeeklyReport::STATUS_DRAFT,
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ]);

    $submittedReport = StudentWeeklyReport::create([
        'student_id' => $studentSubmitted->id,
        'created_by_admin_id' => $admin->id,
        'report_title' => 'Filter Batch Report',
        'status' => StudentWeeklyReport::STATUS_SUBMITTED,
        'submitted_at' => now(),
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ]);

    $service = app(StudentWeeklyReportService::class);
    $batch = $service->getAdminCreatedBatchByKey(
        $service->getAdminCreatedReportBatches(perPage: 50)->first()['key']
    );

    $byEmail = $service->filterAdminCreatedBatchStudents($batch, 'milad@example.com', null);
    expect($byEmail['student_reports'])->toHaveCount(1);
    expect($byEmail['student_reports']->first()->id)->toBe($draftReport->id);

    $byStatus = $service->filterAdminCreatedBatchStudents($batch, null, StudentWeeklyReport::STATUS_SUBMITTED);
    expect($byStatus['student_reports'])->toHaveCount(1);
    expect($byStatus['student_reports']->first()->id)->toBe($submittedReport->id);
});

test('it stores admin feedback and marks report as reviewed', function () {
    $student = User::factory()->create();

    $report = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'report_title' => 'Review report',
        'status' => StudentWeeklyReport::STATUS_SUBMITTED,
        'submitted_at' => now(),
    ]);

    $service = app(StudentWeeklyReportService::class);
    $updated = $service->addAdminFeedback($report, 'أحسنت، استمر.');

    expect($updated->admin_feedback)->toBe('أحسنت، استمر.');
    expect($updated->status)->toBe(StudentWeeklyReport::STATUS_REVIEWED);
    expect($updated->reviewed_at)->not->toBeNull();
});

test('it resolves selectable modules only for the report group visibility rules', function () {
    $student = User::factory()->create();

    $courseCategoryId = DB::table('course_categories')->insertGetId([
        'name' => 'Selectable Modules Category',
        'slug' => 'selectable-modules-category',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $courseId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Selectable Modules Course',
        'slug' => 'selectable-modules-course',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groupId = DB::table('course_groups')->insertGetId([
        'name' => 'Selectable Modules Group',
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
        'student_id' => $student->id,
        'role' => 'member',
        'joined_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sectionId = DB::table('course_sections')->insertGetId([
        'course_id' => $courseId,
        'title' => 'Section A',
        'is_visible' => true,
        'sort_order' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $allowedModuleId = DB::table('course_modules')->insertGetId([
        'course_id' => $courseId,
        'section_id' => $sectionId,
        'module_type' => 'lesson',
        'title' => 'Allowed Lesson',
        'sort_order' => 1,
        'is_visible' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $restrictedModuleId = DB::table('course_modules')->insertGetId([
        'course_id' => $courseId,
        'section_id' => $sectionId,
        'module_type' => 'lesson',
        'title' => 'Restricted Lesson',
        'sort_order' => 2,
        'is_visible' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $otherGroupId = DB::table('course_groups')->insertGetId([
        'name' => 'Other Group',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('module_access_restrictions')->insert([
        'module_id' => $restrictedModuleId,
        'restriction_type' => 'group',
        'restriction_id' => $otherGroupId,
        'access_type' => 'allow',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $report = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'target_course_id' => $courseId,
        'target_group_id' => $groupId,
        'report_title' => 'Group filtered report',
        'status' => StudentWeeklyReport::STATUS_DRAFT,
    ]);

    $service = app(StudentWeeklyReportService::class);
    $modules = $service->resolveSelectableModulesForStudentReport($student->id, $report, $courseId);

    expect($modules->pluck('id')->all())->toBe([$allowedModuleId]);
    expect($service->isModuleAllowedForStudentReport($student->id, $report, $courseId, $allowedModuleId))->toBeTrue();
    expect($service->isModuleAllowedForStudentReport($student->id, $report, $courseId, $restrictedModuleId))->toBeFalse();
});

test('it groups selectable modules by course section', function () {
    $student = User::factory()->create();

    $courseCategoryId = DB::table('course_categories')->insertGetId([
        'name' => 'Grouped Modules Category',
        'slug' => 'grouped-modules-category',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $courseId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Grouped Modules Course',
        'slug' => 'grouped-modules-course',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $groupId = DB::table('course_groups')->insertGetId([
        'name' => 'Grouped Modules Group',
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
        'student_id' => $student->id,
        'role' => 'member',
        'joined_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sectionOneId = DB::table('course_sections')->insertGetId([
        'course_id' => $courseId,
        'title' => 'Images -8',
        'is_visible' => true,
        'sort_order' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sectionTwoId = DB::table('course_sections')->insertGetId([
        'course_id' => $courseId,
        'title' => 'List -9',
        'is_visible' => true,
        'sort_order' => 2,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $lessonId = DB::table('course_modules')->insertGetId([
        'course_id' => $courseId,
        'section_id' => $sectionOneId,
        'module_type' => 'lesson',
        'title' => 'Lesson in section one',
        'sort_order' => 1,
        'is_visible' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('module_completions')->insert([
        'student_id' => $student->id,
        'module_id' => $lessonId,
        'completion_status' => 'completed',
        'completed_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $quizId = DB::table('course_modules')->insertGetId([
        'course_id' => $courseId,
        'section_id' => $sectionOneId,
        'module_type' => 'quiz',
        'title' => 'Quiz in section one',
        'sort_order' => 2,
        'is_visible' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $docId = DB::table('course_modules')->insertGetId([
        'course_id' => $courseId,
        'section_id' => $sectionTwoId,
        'module_type' => 'documentation',
        'title' => 'Doc in section two',
        'sort_order' => 1,
        'is_visible' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $report = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'target_course_id' => $courseId,
        'target_group_id' => $groupId,
        'report_title' => 'Grouped modules report',
        'status' => StudentWeeklyReport::STATUS_DRAFT,
    ]);

    $service = app(StudentWeeklyReportService::class);
    $groups = $service->resolveSelectableModuleGroupsForStudentReport($student->id, $report, $courseId);

    expect($groups)->toHaveCount(2);
    expect($groups->first()['section_title'])->toBe('Images -8');
    expect(collect($groups->first()['modules'])->pluck('id')->all())->toBe([$lessonId, $quizId]);
    expect(collect($groups->first()['modules'])->firstWhere('id', $lessonId)['is_completed'])->toBeTrue();
    expect(collect($groups->first()['modules'])->firstWhere('id', $quizId)['is_completed'])->toBeFalse();
    expect($groups->last()['section_title'])->toBe('List -9');
    expect($groups->last()['modules'][0]['type_label'])->toBe('توثيق');
    expect($groups->last()['modules'][0]['id'])->toBe($docId);
});

test('it groups selected lessons by section for display with completion status', function () {
    $student = User::factory()->create();

    $courseCategoryId = DB::table('course_categories')->insertGetId([
        'name' => 'Display Group Category',
        'slug' => 'display-group-category',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $courseId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Display Group Course',
        'slug' => 'display-group-course',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sectionOneId = DB::table('course_sections')->insertGetId([
        'course_id' => $courseId,
        'title' => 'Section Alpha',
        'is_visible' => true,
        'sort_order' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $sectionTwoId = DB::table('course_sections')->insertGetId([
        'course_id' => $courseId,
        'title' => 'Section Beta',
        'is_visible' => true,
        'sort_order' => 2,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $completedModuleId = DB::table('course_modules')->insertGetId([
        'course_id' => $courseId,
        'section_id' => $sectionOneId,
        'module_type' => 'lesson',
        'title' => 'Completed lesson',
        'sort_order' => 1,
        'is_visible' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $openModuleId = DB::table('course_modules')->insertGetId([
        'course_id' => $courseId,
        'section_id' => $sectionTwoId,
        'module_type' => 'quiz',
        'title' => 'Open quiz',
        'sort_order' => 1,
        'is_visible' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('module_completions')->insert([
        'student_id' => $student->id,
        'module_id' => $completedModuleId,
        'completion_status' => 'completed',
        'completed_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $report = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'target_course_id' => $courseId,
        'report_title' => 'Display grouped lessons',
        'status' => StudentWeeklyReport::STATUS_SUBMITTED,
        'submitted_at' => now(),
    ]);

    DB::table('student_weekly_report_lessons')->insert([
        [
            'student_weekly_report_id' => $report->id,
            'course_id' => $courseId,
            'module_id' => $completedModuleId,
            'lesson_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'student_weekly_report_id' => $report->id,
            'course_id' => $courseId,
            'module_id' => $openModuleId,
            'lesson_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $service = app(StudentWeeklyReportService::class);
    $groups = $service->groupSelectedLessonsBySectionForDisplay($report->fresh());

    expect($groups)->toHaveCount(2);
    expect($groups->first()['section_title'])->toBe('Section Alpha');
    expect($groups->first()['items'][0]['is_completed'])->toBeTrue();
    expect($groups->last()['section_title'])->toBe('Section Beta');
    expect($groups->last()['items'][0]['is_completed'])->toBeFalse();
    expect($groups->last()['items'][0]['type_label'])->toBe('اختبار');
});

