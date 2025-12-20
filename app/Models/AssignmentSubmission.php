<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'submission_text',
        'submitted_links',
        'submitted_files',
        'status',
        'submitted_at',
        'is_late',
        'grade',
        'feedback',
        'graded_by',
        'graded_at',
        'attempt_number',
    ];

    protected $casts = [
        'submitted_links' => 'array',
        'submitted_files' => 'array',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'is_late' => 'boolean',
        'grade' => 'decimal:2',
    ];

    /**
     * Get the assignment that owns the submission.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Get the student who made the submission.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the instructor who graded the submission.
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Check if the submission has been graded.
     */
    public function isGraded(): bool
    {
        return $this->status === 'graded' && $this->grade !== null;
    }

    /**
     * Check if the submission is submitted.
     */
    public function isSubmitted(): bool
    {
        return in_array($this->status, ['submitted', 'graded', 'returned']);
    }

    /**
     * Check if the submission is a draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Calculate the final grade with late penalty if applicable.
     */
    public function getFinalGrade(): ?float
    {
        if ($this->grade === null) {
            return null;
        }

        if (!$this->is_late) {
            return (float) $this->grade;
        }

        $penalty = $this->assignment->late_penalty_percentage ?? 0;
        $deduction = ($this->grade * $penalty) / 100;

        return max(0, $this->grade - $deduction);
    }

    /**
     * Get the grade percentage.
     */
    public function getGradePercentage(): ?float
    {
        if ($this->grade === null) {
            return null;
        }

        $maxGrade = $this->assignment->max_grade ?? 100;
        return ($this->getFinalGrade() / $maxGrade) * 100;
    }
}
