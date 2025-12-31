<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'image',
        'max_members',
        'is_visible',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships

    /**
     * Get the courses associated with this group (Many-to-Many).
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_group_courses', 'group_id', 'course_id')
                    ->withTimestamps();
    }

    /**
     * Get the first course (for backward compatibility).
     * @deprecated Use courses() instead
     */
    public function course()
    {
        return $this->courses()->first();
    }

    /**
     * Get the members of the group.
     */
    public function members()
    {
        return $this->hasMany(CourseGroupMember::class, 'group_id');
    }

    /**
     * Get the students in this group.
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'course_group_members', 'group_id', 'student_id')
                    ->withPivot(['role', 'joined_at'])
                    ->withTimestamps();
    }

    /**
     * Get the group leaders.
     */
    public function leaders()
    {
        return $this->belongsToMany(User::class, 'course_group_members', 'group_id', 'student_id')
                    ->wherePivot('role', 'leader')
                    ->withPivot(['role', 'joined_at'])
                    ->withTimestamps();
    }

    /**
     * Get the user who created the group.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the group enrollments.
     */
    public function groupEnrollments()
    {
        return $this->hasMany(GroupCourseEnrollment::class, 'group_id');
    }

    // Scopes

    /**
     * Scope a query to only include visible groups.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope a query to only include active groups.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper Methods

    /**
     * Check if group is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if group is full.
     */
    public function isFull(): bool
    {
        if (!$this->max_members) {
            return false;
        }

        return $this->members()->count() >= $this->max_members;
    }

    /**
     * Get current members count.
     */
    public function getMembersCount(): int
    {
        return $this->members()->count();
    }

    /**
     * Get available slots.
     */
    public function getAvailableSlots(): ?int
    {
        if (!$this->max_members) {
            return null;
        }

        return max(0, $this->max_members - $this->getMembersCount());
    }

    /**
     * Check if user is a member.
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->where('student_id', $user->id)->exists();
    }

    /**
     * Check if user is a leader.
     */
    public function hasLeader(User $user): bool
    {
        return $this->members()
                    ->where('student_id', $user->id)
                    ->where('role', 'leader')
                    ->exists();
    }

    /**
     * Add a member to the group.
     * Automatically enrolls the student in all courses associated with this group.
     */
    public function addMember(User $user, string $role = 'member'): ?CourseGroupMember
    {
        if ($this->isFull()) {
            return null;
        }

        if ($this->hasMember($user)) {
            return null;
        }

        // Add member to group
        $member = $this->members()->create([
            'student_id' => $user->id,
            'role' => $role,
            'joined_at' => now(),
        ]);

        // Automatically enroll student in all group courses
        if ($member) {
            $this->enrollStudentInGroupCourses($user);
        }

        return $member;
    }

    /**
     * Remove a member from the group.
     */
    public function removeMember(User $user): bool
    {
        return $this->members()->where('student_id', $user->id)->delete() > 0;
    }

    /**
     * Enroll student in all courses associated with this group.
     * If student is already enrolled, update the enrollment status to active.
     *
     * @param User $student
     * @param int|null $enrolledBy User ID who is enrolling the student (defaults to group creator or auth user)
     * @return array Returns array with 'enrolled' count and 'updated' count
     */
    public function enrollStudentInGroupCourses(User $student, ?int $enrolledBy = null): array
    {
        $enrolledCount = 0;
        $updatedCount = 0;

        // Get all courses associated with this group
        $courses = $this->courses;

        if ($courses->isEmpty()) {
            return [
                'enrolled' => 0,
                'updated' => 0,
                'total' => 0
            ];
        }

        // Determine who is enrolling the student
        $enrolledById = $enrolledBy ?? auth()->id() ?? $this->created_by;

        foreach ($courses as $course) {
            // Check if student is already enrolled in this course
            $existingEnrollment = \App\Models\CourseEnrollment::where('course_id', $course->id)
                ->where('student_id', $student->id)
                ->first();

            if ($existingEnrollment) {
                // Update existing enrollment
                $existingEnrollment->update([
                    'enrollment_status' => 'active',
                    'enrollment_date' => now(),
                    'enrolled_by' => $enrolledById,
                ]);
                $updatedCount++;
            } else {
                // Create new enrollment
                \App\Models\CourseEnrollment::create([
                    'course_id' => $course->id,
                    'student_id' => $student->id,
                    'enrollment_status' => 'active',
                    'enrollment_date' => now(),
                    'enrolled_by' => $enrolledById,
                    'completion_percentage' => 0,
                    'certificate_issued' => false,
                ]);
                $enrolledCount++;
            }
        }

        return [
            'enrolled' => $enrolledCount,
            'updated' => $updatedCount,
            'total' => $courses->count()
        ];
    }
}
