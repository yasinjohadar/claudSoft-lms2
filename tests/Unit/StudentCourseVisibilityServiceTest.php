<?php

use App\Models\CourseEnrollment;
use App\Services\Student\StudentCourseVisibilityService;
use Illuminate\Support\Collection;
use Tests\TestCase;

uses(TestCase::class);

test('pending message constant is set in arabic', function () {
    expect(StudentCourseVisibilityService::PENDING_MESSAGE)
        ->toContain('قيد المعالجة');
});

test('exclude hidden enrollments returns same collection when no hidden ids', function () {
    $service = Mockery::mock(StudentCourseVisibilityService::class)->makePartial();
    $service->shouldReceive('hiddenCourseIds')->andReturn([]);

    $student = new \App\Models\User;
    $student->id = 1;

    $enrollments = collect([
        new CourseEnrollment(['course_id' => 10, 'student_id' => 1]),
        new CourseEnrollment(['course_id' => 20, 'student_id' => 1]),
    ]);

    $filtered = $service->excludeHiddenEnrollments($enrollments, $student);

    expect($filtered)->toHaveCount(2);
});

test('exclude hidden enrollments removes gated group courses and keeps others', function () {
    $service = Mockery::mock(StudentCourseVisibilityService::class)->makePartial();
    $service->shouldReceive('hiddenCourseIds')->andReturn([10, 11]);

    $student = new \App\Models\User;
    $student->id = 1;

    $enrollments = collect([
        new CourseEnrollment(['course_id' => 10, 'student_id' => 1]),
        new CourseEnrollment(['course_id' => 20, 'student_id' => 1]),
        new CourseEnrollment(['course_id' => 11, 'student_id' => 1]),
    ]);

    /** @var Collection $filtered */
    $filtered = $service->excludeHiddenEnrollments($enrollments, $student);

    expect($filtered)->toHaveCount(1)
        ->and($filtered->first()->course_id)->toBe(20);
});

test('is course hidden uses hidden course ids', function () {
    $service = Mockery::mock(StudentCourseVisibilityService::class)->makePartial();
    $service->shouldReceive('hiddenCourseIds')->andReturn([55]);

    $student = new \App\Models\User;
    $student->id = 1;

    expect($service->isCourseHiddenForStudent(55, $student))->toBeTrue()
        ->and($service->isCourseHiddenForStudent(99, $student))->toBeFalse();
});

test('hidden course ids merges pending gated and group pivot hidden courses', function () {
    $service = Mockery::mock(StudentCourseVisibilityService::class)->makePartial();
    $service->shouldReceive('pendingGatedHiddenCourseIds')->andReturn([10, 11]);
    $service->shouldReceive('groupPivotHiddenCourseIds')->andReturn([11, 20]);

    $student = new \App\Models\User;
    $student->id = 1;

    $ids = $service->hiddenCourseIds($student);

    expect($ids)->toEqualCanonicalizing([10, 11, 20]);
});

test('hide reason prefers pending membership message over group pivot hide', function () {
    $service = Mockery::mock(StudentCourseVisibilityService::class)->makePartial();
    $service->shouldReceive('pendingGatedHiddenCourseIds')->andReturn([10]);
    $service->shouldReceive('groupPivotHiddenCourseIds')->andReturn([10, 20]);

    $student = new \App\Models\User;
    $student->id = 1;

    expect($service->hideReasonForCourse(10, $student))
        ->toBe(StudentCourseVisibilityService::PENDING_MESSAGE)
        ->and($service->hideReasonForCourse(20, $student))
        ->toBe(StudentCourseVisibilityService::GROUP_COURSE_HIDDEN_MESSAGE)
        ->and($service->hideReasonForCourse(99, $student))
        ->toBeNull();
});

test('group pivot hide keeps course when another membership group shows it', function () {
    $studentId = 9001;
    $courseShown = 9101;
    $courseHiddenOnly = 9102;
    $groupVisible = 9201;
    $groupHidden = 9202;
    $groupHiddenOnly = 9203;

    Illuminate\Support\Facades\Schema::dropIfExists('course_group_members');
    Illuminate\Support\Facades\Schema::dropIfExists('course_group_courses');

    Illuminate\Support\Facades\Schema::create('course_group_courses', function ($table) {
        $table->id();
        $table->unsignedBigInteger('group_id');
        $table->unsignedBigInteger('course_id');
        $table->boolean('is_visible')->default(true);
    });

    Illuminate\Support\Facades\Schema::create('course_group_members', function ($table) {
        $table->id();
        $table->unsignedBigInteger('group_id');
        $table->unsignedBigInteger('student_id');
    });

    Illuminate\Support\Facades\DB::table('course_group_courses')->insert([
        ['group_id' => $groupVisible, 'course_id' => $courseShown, 'is_visible' => true],
        ['group_id' => $groupHidden, 'course_id' => $courseShown, 'is_visible' => false],
        ['group_id' => $groupHiddenOnly, 'course_id' => $courseHiddenOnly, 'is_visible' => false],
    ]);

    Illuminate\Support\Facades\DB::table('course_group_members')->insert([
        ['group_id' => $groupVisible, 'student_id' => $studentId],
        ['group_id' => $groupHidden, 'student_id' => $studentId],
        ['group_id' => $groupHiddenOnly, 'student_id' => $studentId],
    ]);

    $service = new StudentCourseVisibilityService;
    $student = new \App\Models\User;
    $student->id = $studentId;

    $hidden = $service->groupPivotHiddenCourseIds($student);

    expect($hidden)->toEqualCanonicalizing([$courseHiddenOnly])
        ->and($hidden)->not->toContain($courseShown);
});
