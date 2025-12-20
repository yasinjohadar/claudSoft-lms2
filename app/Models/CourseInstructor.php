<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseInstructor extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'instructor_id',
        'role',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    // Relationships

    /**
     * Get the course that owns the instructor.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the instructor (user).
     */
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    // Scopes

    /**
     * Scope a query to only include main instructors.
     */
    public function scopeMainInstructors($query)
    {
        return $query->where('role', 'main_instructor');
    }

    /**
     * Scope a query to only include co-instructors.
     */
    public function scopeCoInstructors($query)
    {
        return $query->where('role', 'co_instructor');
    }

    /**
     * Scope a query to only include teaching assistants.
     */
    public function scopeTeachingAssistants($query)
    {
        return $query->where('role', 'teaching_assistant');
    }

    /**
     * Scope a query to filter by role.
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    // Helper Methods

    /**
     * Check if instructor is main instructor.
     */
    public function isMainInstructor(): bool
    {
        return $this->role === 'main_instructor';
    }

    /**
     * Check if instructor is co-instructor.
     */
    public function isCoInstructor(): bool
    {
        return $this->role === 'co_instructor';
    }

    /**
     * Check if instructor is teaching assistant.
     */
    public function isTeachingAssistant(): bool
    {
        return $this->role === 'teaching_assistant';
    }

    /**
     * Check if instructor has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->permissions) {
            return false;
        }

        return in_array($permission, $this->permissions);
    }

    /**
     * Grant a permission.
     */
    public function grantPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];

        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->update(['permissions' => $permissions]);
        }
    }

    /**
     * Revoke a permission.
     */
    public function revokePermission(string $permission): void
    {
        if (!$this->permissions) {
            return;
        }

        $permissions = array_filter($this->permissions, function($p) use ($permission) {
            return $p !== $permission;
        });

        $this->update(['permissions' => array_values($permissions)]);
    }
}
