<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'instructions',
        'course_id',
        'lesson_id',
        'max_grade',
        'submission_type',
        'max_links',
        'max_files',
        'max_file_size',
        'available_from',
        'due_date',
        'late_submission_until',
        'allow_late_submission',
        'late_penalty_percentage',
        'allow_resubmission',
        'max_resubmissions',
        'resubmit_after_grading_only',
        'extra_attempts_granted',
        'is_published',
        'is_visible',
        'sort_order',
        'attachments',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'attachments' => 'array',
        'extra_attempts_granted' => 'array',
        'available_from' => 'datetime',
        'due_date' => 'datetime',
        'late_submission_until' => 'datetime',
        'is_published' => 'boolean',
        'is_visible' => 'boolean',
        'allow_late_submission' => 'boolean',
        'allow_resubmission' => 'boolean',
        'resubmit_after_grading_only' => 'boolean',
    ];

    /**
     * Get the course that owns the assignment.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the lesson that owns the assignment.
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get the submissions for the assignment.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    /**
     * Get the creator of the assignment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the updater of the assignment.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Check if the assignment is currently available.
     */
    public function isAvailable(): bool
    {
        $now = now();

        if ($this->available_from && $now->lt($this->available_from)) {
            return false;
        }

        return $this->is_published && $this->is_visible;
    }

    /**
     * Check if the assignment is past due.
     */
    public function isPastDue(): bool
    {
        return $this->due_date && now()->gt($this->due_date);
    }

    /**
     * Check if late submissions are still allowed.
     */
    public function canSubmitLate(): bool
    {
        if (!$this->allow_late_submission) {
            return false;
        }

        if (!$this->late_submission_until) {
            return true;
        }

        return now()->lte($this->late_submission_until);
    }

    /**
     * Check if student can resubmit for this assignment.
     */
    public function canResubmit($studentIdOrSubmission): bool
    {
        // Handle both student ID and submission object
        if ($studentIdOrSubmission instanceof AssignmentSubmission) {
            $submission = $studentIdOrSubmission;
            $studentId = $submission->student_id;
        } else {
            $studentId = $studentIdOrSubmission;
            $submission = $this->submissions()
                ->where('student_id', $studentId)
                ->orderBy('attempt_number', 'desc')
                ->first();
        }

        // If resubmission is not allowed at all
        if (!$this->allow_resubmission) {
            return false;
        }

        // If must wait for grading and not yet graded
        if ($submission && $this->resubmit_after_grading_only && !$submission->isGraded()) {
            return false;
        }

        // Check if max resubmissions limit is reached (including extra attempts)
        if ($this->max_resubmissions !== null) {
            $resubmissionCount = $this->submissions()
                ->where('student_id', $studentId)
                ->where('attempt_number', '>', 1)
                ->count();

            // Get extra attempts granted for this student
            $extraAttempts = $this->getExtraAttemptsForStudent($studentId);
            $totalAllowed = $this->max_resubmissions + $extraAttempts;

            if ($resubmissionCount >= $totalAllowed) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get remaining resubmissions for a student.
     */
    public function getRemainingResubmissions(int $studentId): ?int
    {
        if (!$this->allow_resubmission) {
            return 0;
        }

        if ($this->max_resubmissions === null) {
            return null; // Unlimited
        }

        $resubmissionCount = $this->submissions()
            ->where('student_id', $studentId)
            ->where('attempt_number', '>', 1)
            ->count();

        // Include extra attempts granted
        $extraAttempts = $this->getExtraAttemptsForStudent($studentId);
        $totalAllowed = $this->max_resubmissions + $extraAttempts;

        return max(0, $totalAllowed - $resubmissionCount);
    }

    /**
     * Get extra attempts granted for a specific student.
     */
    public function getExtraAttemptsForStudent(int $studentId): int
    {
        if (!$this->extra_attempts_granted || !is_array($this->extra_attempts_granted)) {
            return 0;
        }

        return (int) ($this->extra_attempts_granted[$studentId] ?? 0);
    }

    /**
     * Grant extra resubmission attempts to a student.
     */
    public function grantExtraAttempt(int $studentId, int $attemptsToGrant = 1): void
    {
        $extraAttempts = $this->extra_attempts_granted ?? [];
        $currentExtra = (int) ($extraAttempts[$studentId] ?? 0);
        $extraAttempts[$studentId] = $currentExtra + $attemptsToGrant;

        $this->update(['extra_attempts_granted' => $extraAttempts]);
    }
}
