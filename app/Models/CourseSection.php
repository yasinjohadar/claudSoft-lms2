<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseSection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'is_visible',
        'is_locked',
        'show_unavailable',
        'unlock_conditions',
        'available_from',
        'available_until',
        'sort_order',
        'order_index',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'is_locked' => 'boolean',
        'show_unavailable' => 'boolean',
        'unlock_conditions' => 'array',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
    ];

    // Relationships

    /**
     * Get the course that owns the section.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the modules for the section.
     */
    public function modules()
    {
        return $this->hasMany(CourseModule::class, 'section_id')->orderBy('sort_order');
    }

    /**
     * Get the access restrictions for the section.
     */
    public function accessRestrictions()
    {
        return $this->hasMany(SectionAccessRestriction::class, 'section_id');
    }

    /**
     * Get the completion records for the section.
     */
    public function completions()
    {
        return $this->hasMany(SectionCompletion::class, 'section_id');
    }

    /**
     * Get the questions linked to this section.
     */
    public function questions()
    {
        return $this->belongsToMany(QuestionBank::class, 'course_section_questions', 'course_section_id', 'question_id')
            ->withPivot(['question_order', 'question_grade', 'is_required', 'settings'])
            ->orderBy('course_section_questions.question_order')
            ->withTimestamps();
    }

    /**
     * Get the user who created the section.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the section.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes

    /**
     * Scope a query to only include visible sections.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope a query to only include unlocked sections.
     */
    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }

    /**
     * Scope a query to only include available sections based on dates.
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
     * Check if the section is available.
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
     * Check if the section is unlocked for a student.
     */
    public function isUnlockedFor(User $student): bool
    {
        if (!$this->is_locked) {
            return true;
        }

        // Check unlock conditions
        if (!$this->unlock_conditions) {
            return false;
        }

        // TODO: Implement unlock conditions logic
        // For now, return true if no conditions or locked is false
        return !$this->is_locked;
    }

    /**
     * Get completion percentage for a student.
     */
    public function getCompletionPercentageFor(User $student): float
    {
        $completion = $this->completions()
                           ->where('student_id', $student->id)
                           ->first();

        return $completion ? $completion->completion_percentage : 0;
    }
}
