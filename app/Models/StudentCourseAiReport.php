<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCourseAiReport extends Model
{
    protected $table = 'student_course_ai_reports';

    protected $fillable = [
        'student_id',
        'course_id',
        'course_group_id',
        'created_by',
        'facts',
        'narrative',
        'laravel_ai_model_id',
        'meta',
    ];

    protected $casts = [
        'facts' => 'array',
        'meta' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function courseGroup(): BelongsTo
    {
        return $this->belongsTo(CourseGroup::class, 'course_group_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function laravelAiModel(): BelongsTo
    {
        return $this->belongsTo(LaravelAiModel::class, 'laravel_ai_model_id');
    }
}
