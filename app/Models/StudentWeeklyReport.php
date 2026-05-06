<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentWeeklyReport extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'student_id',
        'created_by_admin_id',
        'target_course_id',
        'target_group_id',
        'report_title',
        'student_details',
        'student_notes',
        'admin_feedback',
        'due_at',
        'submitted_at',
        'reviewed_at',
        'closed_at',
        'status',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    public function targetCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'target_course_id');
    }

    public function targetGroup(): BelongsTo
    {
        return $this->belongsTo(CourseGroup::class, 'target_group_id');
    }

    public function selectedLessons(): HasMany
    {
        return $this->hasMany(StudentWeeklyReportLesson::class);
    }
}

