<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgrammingChallengeSubmission extends Model
{
    protected $fillable = [
        'programming_challenge_attempt_id',
        'submission_number',
        'status',
        'student_notes',
    ];

    protected $casts = [
        'submission_number' => 'integer',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ProgrammingChallengeAttempt::class, 'programming_challenge_attempt_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProgrammingChallengeSubmissionFile::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(ProgrammingChallengeRun::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }
}
