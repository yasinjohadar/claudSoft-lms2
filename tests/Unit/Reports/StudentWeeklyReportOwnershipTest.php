<?php

use App\Models\StudentWeeklyReport;

test('belongsToStudentId compares student ids as integers', function () {
    $report = new StudentWeeklyReport(['student_id' => 94]);

    expect($report->belongsToStudentId('94'))->toBeTrue();
    expect($report->belongsToStudentId(94))->toBeTrue();
    expect($report->belongsToStudentId(95))->toBeFalse();
    expect($report->belongsToStudentId(null))->toBeFalse();
});
