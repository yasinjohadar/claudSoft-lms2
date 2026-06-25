<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProgrammingChallengeAttempt extends Model
{
    protected $fillable = [
        'programming_challenge_id',
        'user_id',
        'course_module_id',
        'attempt_number',
        'status',
        'started_at',
        'submitted_at',
        'graded_at',
        'score',
        'max_score',
        'grade_status',
        'feedback',
        'graded_by',
    ];

    protected $casts = [
        'attempt_number' => 'integer',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(ProgrammingChallenge::class, 'programming_challenge_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function courseModule(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ProgrammingChallengeSubmission::class);
    }

    public function latestSubmission(): HasOne
    {
        return $this->hasOne(ProgrammingChallengeSubmission::class)->latestOfMany();
    }

    public function draftSubmission(): HasOne
    {
        return $this->hasOne(ProgrammingChallengeSubmission::class)
            ->where('status', 'draft')
            ->latestOfMany();
    }

    public function runs(): HasMany
    {
        return $this->hasMany(ProgrammingChallengeRun::class);
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, ['submitted', 'graded', 'returned']);
    }

    public function isGraded(): bool
    {
        return $this->status === 'graded' && $this->score !== null;
    }

    public function needsGrading(): bool
    {
        return $this->status === 'submitted' && $this->grade_status === 'pending';
    }

    public function scopePendingGrading($query)
    {
        return $query->where('status', 'submitted')
            ->where(function ($q) {
                $q->whereNull('grade_status')->orWhere('grade_status', 'pending');
            });
    }
}
