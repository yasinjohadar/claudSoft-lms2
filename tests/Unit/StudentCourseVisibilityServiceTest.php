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
