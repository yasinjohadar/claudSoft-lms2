<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentWeeklyReportLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_weekly_report_id',
        'course_id',
        'lesson_id',
        'module_id',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(StudentWeeklyReport::class, 'student_weekly_report_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }
}

