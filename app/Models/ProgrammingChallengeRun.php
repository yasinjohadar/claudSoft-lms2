<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgrammingChallengeRun extends Model
{
    protected $fillable = [
        'programming_challenge_attempt_id',
        'programming_challenge_submission_id',
        'trigger',
        'runtime_slug',
        'stdout',
        'stderr',
        'exit_code',
        'duration_ms',
        'test_results',
    ];

    protected $casts = [
        'exit_code' => 'integer',
        'duration_ms' => 'integer',
        'test_results' => 'array',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ProgrammingChallengeAttempt::class, 'programming_challenge_attempt_id');
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(ProgrammingChallengeSubmission::class, 'programming_challenge_submission_id');
    }
}
