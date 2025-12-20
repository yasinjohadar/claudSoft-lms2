<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BulkEnrollmentSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'uploaded_by',
        'file_path',
        'file_name',
        'enrollment_type',
        'group_id',
        'total_students',
        'successful_enrollments',
        'failed_enrollments',
        'skipped_enrollments',
        'errors',
        'success_details',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'errors' => 'array',
        'success_details' => 'array',
        'processed_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the course that owns the session.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the user who uploaded the file.
     */
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the group for group enrollments.
     */
    public function group()
    {
        return $this->belongsTo(CourseGroup::class, 'group_id');
    }

    // Scopes

    /**
     * Scope a query to only include pending sessions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include processing sessions.
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope a query to only include completed sessions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include failed sessions.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // Helper Methods

    /**
     * Check if session is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if session is processing.
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if session is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if session is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark session as processing.
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    /**
     * Mark session as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark session as failed.
     */
    public function markAsFailed(array $errors = []): void
    {
        $this->update([
            'status' => 'failed',
            'errors' => $errors,
            'processed_at' => now(),
        ]);
    }

    /**
     * Get success rate percentage.
     */
    public function getSuccessRate(): float
    {
        if ($this->total_students === 0) {
            return 0;
        }

        return ($this->successful_enrollments / $this->total_students) * 100;
    }

    /**
     * Get failure rate percentage.
     */
    public function getFailureRate(): float
    {
        if ($this->total_students === 0) {
            return 0;
        }

        return ($this->failed_enrollments / $this->total_students) * 100;
    }

    /**
     * Add success enrollment.
     */
    public function addSuccess(array $details = []): void
    {
        $this->increment('successful_enrollments');

        if (!empty($details)) {
            $successDetails = $this->success_details ?? [];
            $successDetails[] = $details;
            $this->update(['success_details' => $successDetails]);
        }
    }

    /**
     * Add failed enrollment.
     */
    public function addFailure(array $error): void
    {
        $this->increment('failed_enrollments');

        $errors = $this->errors ?? [];
        $errors[] = $error;
        $this->update(['errors' => $errors]);
    }

    /**
     * Add skipped enrollment.
     */
    public function addSkipped(): void
    {
        $this->increment('skipped_enrollments');
    }

    /**
     * Check if enrollment is for individuals.
     */
    public function isIndividualEnrollment(): bool
    {
        return $this->enrollment_type === 'individual';
    }

    /**
     * Check if enrollment is for a group.
     */
    public function isGroupEnrollment(): bool
    {
        return $this->enrollment_type === 'group';
    }
}
