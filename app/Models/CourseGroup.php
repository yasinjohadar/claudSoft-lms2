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
     */
    public function addMember(User $user, string $role = 'member'): ?CourseGroupMember
    {
        if ($this->isFull()) {
            return null;
        }

        if ($this->hasMember($user)) {
            return null;
        }

        return $this->members()->create([
            'student_id' => $user->id,
            'role' => $role,
            'joined_at' => now(),
        ]);
    }

    /**
     * Remove a member from the group.
     */
    public function removeMember(User $user): bool
    {
        return $this->members()->where('student_id', $user->id)->delete() > 0;
    }
}
