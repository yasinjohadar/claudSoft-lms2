<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ModuleCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'module_id',
        'completion_status',
        'score',
        'time_spent',
        'started_at',
        'completed_at',
        'last_accessed_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_accessed_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the student (user) who completed the module.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the module that was completed.
     */
    public function module()
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }

    // Scopes

    /**
     * Scope a query to only include completed records.
     */
    public function scopeCompleted($query)
    {
        return $query->where('completion_status', 'completed');
    }

    /**
     * Scope a query to only include in-progress records.
     */
    public function scopeInProgress($query)
    {
        return $query->where('completion_status', 'in_progress');
    }

    /**
     * Scope a query to only include not-started records.
     */
    public function scopeNotStarted($query)
    {
        return $query->where('completion_status', 'not_started');
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('completion_status', $status);
    }

    // Helper Methods

    /**
     * Check if module is completed.
     */
    public function isCompleted(): bool
    {
        return $this->completion_status === 'completed';
    }

    /**
     * Check if module is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->completion_status === 'in_progress';
    }

    /**
     * Check if module is not started.
     */
    public function isNotStarted(): bool
    {
        return $this->completion_status === 'not_started';
    }

    /**
     * Mark as started.
     */
    public function markAsStarted(): void
    {
        if ($this->isNotStarted()) {
            $this->update([
                'completion_status' => 'in_progress',
                'started_at' => now(),
                'last_accessed_at' => now(),
            ]);
        }
    }

    /**
     * Mark as completed.
     */
    public function markAsCompleted(?float $score = null): void
    {
        $this->update([
            'completion_status' => 'completed',
            'completed_at' => now(),
            'last_accessed_at' => now(),
            'score' => $score ?? $this->score,
        ]);
    }

    /**
     * Update time spent (in minutes).
     */
    public function addTimeSpent(int $minutes): void
    {
        $this->increment('time_spent', $minutes);
        $this->update(['last_accessed_at' => now()]);
    }

    /**
     * Touch last accessed timestamp.
     */
    public function touchLastAccessed(): void
    {
        $this->update(['last_accessed_at' => now()]);
    }

    /**
     * Get completion percentage (0-100).
     */
    public function getCompletionPercentage(): int
    {
        return $this->isCompleted() ? 100 : ($this->isInProgress() ? 50 : 0);
    }

    /**
     * Get formatted time spent.
     */
    public function getFormattedTimeSpent(): string
    {
        if (!$this->time_spent) {
            return '0 min';
        }

        if ($this->time_spent < 60) {
            return $this->time_spent . ' min';
        }

        $hours = floor($this->time_spent / 60);
        $minutes = $this->time_spent % 60;

        return $hours . 'h ' . $minutes . 'min';
    }
}
