<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentWeeklyReportSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
        'weekday',
        'due_time',
        'target_scope',
        'target_course_id',
        'target_group_id',
        'target_student_ids',
        'next_run_at',
        'created_by_admin_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'target_course_id' => 'integer',
        'target_group_id' => 'integer',
        'target_student_ids' => 'array',
        'next_run_at' => 'datetime',
    ];

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

    public function calculateNextRun(): \Illuminate\Support\Carbon
    {
        $base = now()->startOfDay();
        $next = $base->copy()->next($this->weekday)->setTimeFromTimeString((string) $this->due_time);

        if ($next->lessThanOrEqualTo(now())) {
            $next->addWeek();
        }

        return $next;
    }
}

