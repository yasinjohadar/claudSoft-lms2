<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupCourseEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'group_id',
        'enrolled_by',
        'enrollment_date',
        'enrollment_status',
        'auto_enroll_new_members',
    ];

    protected $casts = [
        'enrollment_date' => 'datetime',
        'auto_enroll_new_members' => 'boolean',
    ];

    // Relationships

    /**
     * Get the course that owns the enrollment.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the group that is enrolled.
     */
    public function group()
    {
        return $this->belongsTo(CourseGroup::class, 'group_id');
    }

    /**
     * Get the user who enrolled the group.
     */
    public function enrolledBy()
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    // Scopes

    /**
     * Scope a query to only include active enrollments.
     */
    public function scopeActive($query)
    {
        return $query->where('enrollment_status', 'active');
    }

    /**
     * Scope a query to only include suspended enrollments.
     */
    public function scopeSuspended($query)
    {
        return $query->where('enrollment_status', 'suspended');
    }

    /**
     * Scope a query to only include cancelled enrollments.
     */
    public function scopeCancelled($query)
    {
        return $query->where('enrollment_status', 'cancelled');
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('enrollment_status', $status);
    }

    // Helper Methods

    /**
     * Check if enrollment is active.
     */
    public function isActive(): bool
    {
        return $this->enrollment_status === 'active';
    }

    /**
     * Check if enrollment is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->enrollment_status === 'suspended';
    }

    /**
     * Check if enrollment is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->enrollment_status === 'cancelled';
    }

    /**
     * Suspend the enrollment.
     */
    public function suspend(): void
    {
        $this->update(['enrollment_status' => 'suspended']);
    }

    /**
     * Activate the enrollment.
     */
    public function activate(): void
    {
        $this->update(['enrollment_status' => 'active']);
    }

    /**
     * Cancel the enrollment.
     */
    public function cancel(): void
    {
        $this->update(['enrollment_status' => 'cancelled']);
    }

    /**
     * Enroll all current group members.
     */
    public function enrollAllMembers(): array
    {
        $group = $this->group;
        $course = $this->course;

        if (!$group || !$course) {
            return [];
        }

        $enrolled = [];
        $members = $group->members;

        foreach ($members as $member) {
            $enrollment = CourseEnrollment::firstOrCreate(
                [
                    'course_id' => $course->id,
                    'student_id' => $member->student_id,
                ],
                [
                    'enrollment_status' => 'active',
                    'enrolled_by' => $this->enrolled_by,
                    'enrollment_date' => now(),
                ]
            );

            $enrolled[] = $enrollment;
        }

        return $enrolled;
    }

    /**
     * Get total enrolled members count.
     */
    public function getEnrolledMembersCount(): int
    {
        $group = $this->group;
        $course = $this->course;

        if (!$group || !$course) {
            return 0;
        }

        return CourseEnrollment::where('course_id', $course->id)
                               ->whereIn('student_id', $group->members->pluck('student_id'))
                               ->count();
    }
}
