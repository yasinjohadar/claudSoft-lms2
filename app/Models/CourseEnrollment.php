<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'student_id',
        'enrollment_date',
        'enrollment_status',
        'enrolled_by',
        'completion_percentage',
        'progress',
        'last_accessed_at',
        'completed_at',
        'certificate_issued',
        'grade',
    ];

    protected $casts = [
        'enrollment_date' => 'datetime',
        'last_accessed_at' => 'datetime',
        'completed_at' => 'datetime',
        'certificate_issued' => 'boolean',
        'completion_percentage' => 'decimal:2',
        'grade' => 'decimal:2',
        'progress' => 'array',
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
     * Get the student (user) who is enrolled.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the user who enrolled this student.
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
     * Scope a query to only include completed enrollments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('enrollment_status', 'completed');
    }

    /**
     * Scope a query to only include pending enrollments.
     */
    public function scopePending($query)
    {
        return $query->where('enrollment_status', 'pending');
    }

    /**
     * Scope a query to only include suspended enrollments.
     */
    public function scopeSuspended($query)
    {
        return $query->where('enrollment_status', 'suspended');
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
     * Check if enrollment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->enrollment_status === 'completed';
    }

    /**
     * Check if enrollment is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->enrollment_status === 'suspended';
    }

    /**
     * Check if enrollment is pending.
     */
    public function isPending(): bool
    {
        return $this->enrollment_status === 'pending';
    }

    /**
     * Update enrollment progress.
     */
    public function updateProgress(array $progressData): void
    {
        $this->update([
            'progress' => array_merge($this->progress ?? [], $progressData),
            'last_accessed_at' => now(),
        ]);
    }

    /**
     * Calculate and update completion percentage.
     */
    public function calculateCompletionPercentage(): float
    {
        try {
            $course = $this->course;
            $student = $this->student;

            if (!$course || !$student) {
                return 0;
            }

            // Get all required modules in the course
            $requiredModulesQuery = $course->modules()->where('is_required', true);
            $totalModules = $requiredModulesQuery->count();
            
            // If no required modules, count all modules as required (fallback)
            if ($totalModules === 0) {
                $totalModules = $course->modules()->count();
                $moduleIds = $course->modules()->pluck('course_modules.id');
            } else {
                $moduleIds = $requiredModulesQuery->pluck('course_modules.id');
            }

            if ($totalModules === 0) {
                return 0;
            }

            // Get completed modules count
            $completedModules = ModuleCompletion::whereIn('module_id', $moduleIds)
                ->where('student_id', $student->id)
                ->where('completion_status', 'completed')
                ->count();

            $percentage = ($completedModules / $totalModules) * 100;

            // Update the enrollment
            $this->update(['completion_percentage' => $percentage]);

            // Check if course is completed
            if ($percentage >= ($course->passing_percentage ?? 100)) {
                $this->markAsCompleted();
            }

            return $percentage;
        } catch (\Exception $e) {
            \Log::error('Error in calculateCompletionPercentage for enrollment ' . $this->id . ': ' . $e->getMessage());
            return $this->completion_percentage ?? 0;
        }
    }

    /**
     * Mark enrollment as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'enrollment_status' => 'completed',
            'completed_at' => now(),
            'completion_percentage' => 100,
        ]);
    }

    /**
     * Issue certificate.
     */
    public function issueCertificate(): void
    {
        if ($this->isCompleted() && !$this->certificate_issued) {
            $this->update(['certificate_issued' => true]);
        }
    }

    /**
     * Update last accessed timestamp.
     */
    public function touchLastAccessed(): void
    {
        $this->update(['last_accessed_at' => now()]);
    }

    /**
     * Get enrollment duration in days.
     */
    public function getDurationInDays(): int
    {
        $endDate = $this->completed_at ?? now();
        return $this->enrollment_date->diffInDays($endDate);
    }

    /**
     * Check if student has passed the course.
     */
    public function hasPassed(): bool
    {
        if (!$this->course) {
            return false;
        }

        $passingPercentage = $this->course->passing_percentage ?? 100;
        return $this->completion_percentage >= $passingPercentage;
    }
}
