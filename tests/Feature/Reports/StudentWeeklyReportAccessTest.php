<?php

use App\Models\StudentWeeklyReport;
use App\Models\User;
use Spatie\Permission\Models\Role;

function weeklyReportAccessStudent(): User
{
    Role::findOrCreate('student', 'web');

    return User::factory()->create([
        'is_active' => true,
    ])->tap(fn (User $user) => $user->assignRole('student'));
}

test('student can open their own weekly report', function () {
    $student = weeklyReportAccessStudent();

    $report = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'report_title' => 'تقرير أسبوعي',
        'status' => StudentWeeklyReport::STATUS_DRAFT,
    ]);

    $this->actingAs($student)
        ->get(route('student.weekly-reports.show', $report))
        ->assertSuccessful();
});

test('student cannot open another students weekly report', function () {
    $student = weeklyReportAccessStudent();
    $otherStudent = weeklyReportAccessStudent();

    $report = StudentWeeklyReport::create([
        'student_id' => $otherStudent->id,
        'report_title' => 'تقرير أسبوعي',
        'status' => StudentWeeklyReport::STATUS_DRAFT,
    ]);

    $this->actingAs($student)
        ->get(route('student.weekly-reports.show', $report))
        ->assertForbidden();
});

test('weekly report ownership check tolerates string auth ids', function () {
    $student = weeklyReportAccessStudent();

    $report = StudentWeeklyReport::create([
        'student_id' => $student->id,
        'report_title' => 'تقرير أسبوعي',
        'status' => StudentWeeklyReport::STATUS_DRAFT,
    ]);

    expect($report->belongsToStudentId((string) $student->id))->toBeTrue();
    expect($report->belongsToStudentId($student->id + 1))->toBeFalse();
});
