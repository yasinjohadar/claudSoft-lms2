<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgrammingChallengeSubmissionFile extends Model
{
    protected $fillable = [
        'programming_challenge_submission_id',
        'programming_language_id',
        'file_role',
        'filename',
        'content',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(ProgrammingChallengeSubmission::class, 'programming_challenge_submission_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(ProgrammingLanguage::class, 'programming_language_id');
    }
}
