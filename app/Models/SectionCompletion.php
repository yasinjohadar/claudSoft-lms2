<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SectionCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'section_id',
        'completion_percentage',
        'modules_completed',
        'total_modules',
        'started_at',
        'completed_at',
        'last_activity_at',
    ];

    protected $casts = [
        'completion_percentage' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the student (user) who is completing the section.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the section being completed.
     */
    public function section()
    {
        return $this->belongsTo(CourseSection::class, 'section_id');
    }

    // Scopes

    /**
     * Scope a query to only include completed sections.
     */
    public function scopeCompleted($query)
    {
        return $query->where('completion_percentage', 100);
    }

    /**
     * Scope a query to only include in-progress sections.
     */
    public function scopeInProgress($query)
    {
        return $query->where('completion_percentage', '>', 0)
                     ->where('completion_percentage', '<', 100);
    }

    /**
     * Scope a query to only include not-started sections.
     */
    public function scopeNotStarted($query)
    {
        return $query->where('completion_percentage', 0);
    }

    // Helper Methods

    /**
     * Check if section is completed.
     */
    public function isCompleted(): bool
    {
        return $this->completion_percentage >= 100;
    }

    /**
     * Check if section is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->completion_percentage > 0 && $this->completion_percentage < 100;
    }

    /**
     * Check if section is not started.
     */
    public function isNotStarted(): bool
    {
        return $this->completion_percentage == 0;
    }

    /**
     * Calculate and update completion percentage based on module completions.
     */
    public function calculateCompletionPercentage(): float
    {
        $section = $this->section;
        $student = $this->student;

        if (!$section || !$student) {
            return 0;
        }

        // Get all modules in this section
        $totalModules = $section->modules()->count();

        if ($totalModules === 0) {
            $this->update([
                'completion_percentage' => 100,
                'total_modules' => 0,
                'modules_completed' => 0,
            ]);
            return 100;
        }

        // Get completed modules count
        $completedModules = ModuleCompletion::whereIn('module_id',
            $section->modules()->pluck('id')
        )
        ->where('student_id', $student->id)
        ->where('completion_status', 'completed')
        ->count();

        $percentage = ($completedModules / $totalModules) * 100;

        // Update the section completion
        $this->update([
            'completion_percentage' => $percentage,
            'total_modules' => $totalModules,
            'modules_completed' => $completedModules,
            'last_activity_at' => now(),
        ]);

        // Mark as completed if 100%
        if ($percentage >= 100 && !$this->completed_at) {
            $this->update(['completed_at' => now()]);
        }

        return $percentage;
    }

    /**
     * Mark section as started.
     */
    public function markAsStarted(): void
    {
        if (!$this->started_at) {
            $this->update([
                'started_at' => now(),
                'last_activity_at' => now(),
            ]);
        }
    }

    /**
     * Update last activity timestamp.
     */
    public function touchLastActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Get remaining modules count.
     */
    public function getRemainingModulesCount(): int
    {
        return $this->total_modules - $this->modules_completed;
    }
}
