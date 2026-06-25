<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgrammingChallengeFile extends Model
{
    protected $fillable = [
        'programming_challenge_id',
        'programming_language_id',
        'file_role',
        'filename',
        'content',
        'is_readonly',
    ];

    protected $casts = [
        'is_readonly' => 'boolean',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(ProgrammingChallenge::class, 'programming_challenge_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(ProgrammingLanguage::class, 'programming_language_id');
    }
}
