<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseModule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'section_id',
        'module_type',
        'modulable_id',
        'modulable_type',
        'title',
        'description',
        'sort_order',
        'is_visible',
        'is_required',
        'unlock_conditions',
        'available_from',
        'available_until',
        'is_graded',
        'max_score',
        'completion_type',
        'estimated_duration',
        'attempts_allowed',
        'time_limit',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'is_required' => 'boolean',
        'is_graded' => 'boolean',
        'unlock_conditions' => 'array',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
        'max_score' => 'decimal:2',
    ];

    // Relationships

    /**
     * Get the course that owns the module.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the section that owns the module.
     */
    public function section()
    {
        return $this->belongsTo(CourseSection::class, 'section_id');
    }

    /**
     * Get the parent modulable model (lesson, video, resource, etc).
     */
    public function modulable()
    {
        return $this->morphTo();
    }

    /**
     * Get the completion records for the module.
     */
    public function completions()
    {
        return $this->hasMany(ModuleCompletion::class, 'module_id');
    }

    /**
     * Get the access restrictions for the module.
     */
    public function accessRestrictions()
    {
        return $this->hasMany(ModuleAccessRestriction::class, 'module_id');
    }

    // Scopes

    /**
     * Scope a query to only include visible modules.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope a query to only include available modules based on dates.
     */
    public function scopeAvailable($query)
    {
        $now = now();
        return $query->where(function($q) use ($now) {
            $q->whereNull('available_from')->orWhere('available_from', '<=', $now);
        })->where(function($q) use ($now) {
            $q->whereNull('available_until')->orWhere('available_until', '>=', $now);
        });
    }

    // Helper Methods

    /**
     * Check if the module is available.
     */
    public function isAvailable(): bool
    {
        $now = now();

        if ($this->available_from && $this->available_from > $now) {
            return false;
        }

        if ($this->available_until && $this->available_until < $now) {
            return false;
        }

        return true;
    }

    /**
     * Check if the module is completed by a user.
     */
    public function isCompletedBy(User $user): bool
    {
        return $this->completions()
                    ->where('student_id', $user->id)
                    ->where('completion_status', 'completed')
                    ->exists();
    }

    /**
     * Get completion status for a user.
     */
    public function getCompletionFor(User $user)
    {
        return $this->completions()
                    ->where('student_id', $user->id)
                    ->first();
    }

    /**
     * Mark module as completed by a user.
     */
    public function markAsCompletedBy(User $user, ?float $score = null): ModuleCompletion
    {
        return $this->completions()->updateOrCreate(
            ['student_id' => $user->id],
            [
                'completion_status' => 'completed',
                'completed_at' => now(),
                'score' => $score,
            ]
        );
    }
}
