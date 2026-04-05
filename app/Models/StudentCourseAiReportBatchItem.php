<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCourseAiReportBatchItem extends Model
{
    protected $table = 'student_course_ai_report_batch_items';

    protected $fillable = [
        'batch_id',
        'student_id',
        'course_group_id',
        'status',
        'student_course_ai_report_id',
        'error_message',
        'narrative_segments',
        'meta',
    ];

    protected $casts = [
        'narrative_segments' => 'array',
        'meta' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(StudentCourseAiReportBatch::class, 'batch_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function courseGroup(): BelongsTo
    {
        return $this->belongsTo(CourseGroup::class, 'course_group_id');
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(StudentCourseAiReport::class, 'student_course_ai_report_id');
    }
}
