<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseGroupMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'student_id',
        'role',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the group that owns the member.
     */
    public function group()
    {
        return $this->belongsTo(CourseGroup::class, 'group_id');
    }

    /**
     * Get the student (user) who is a member.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    // Scopes

    /**
     * Scope a query to only include leaders.
     */
    public function scopeLeaders($query)
    {
        return $query->where('role', 'leader');
    }

    /**
     * Scope a query to only include members.
     */
    public function scopeMembers($query)
    {
        return $query->where('role', 'member');
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
     * Check if member is a leader.
     */
    public function isLeader(): bool
    {
        return $this->role === 'leader';
    }

    /**
     * Check if member is a regular member.
     */
    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    /**
     * Promote to leader.
     */
    public function promoteToLeader(): void
    {
        $this->update(['role' => 'leader']);
    }

    /**
     * Demote to member.
     */
    public function demoteToMember(): void
    {
        $this->update(['role' => 'member']);
    }
}
