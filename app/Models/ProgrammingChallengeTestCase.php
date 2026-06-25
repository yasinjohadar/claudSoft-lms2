<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgrammingChallengeTestCase extends Model
{
    protected $fillable = [
        'programming_challenge_id',
        'input',
        'expected_output',
        'is_hidden',
        'points',
        'timeout_ms',
        'sort_order',
    ];

    protected $casts = [
        'is_hidden' => 'boolean',
        'points' => 'decimal:2',
        'timeout_ms' => 'integer',
        'sort_order' => 'integer',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(ProgrammingChallenge::class, 'programming_challenge_id');
    }
}
