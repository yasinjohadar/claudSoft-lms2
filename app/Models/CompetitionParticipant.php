<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_id',
        'user_id',
        'current_value',
        'rank',
        'is_winner',
        'joined_at',
    ];

    protected $casts = [
        'current_value' => 'integer',
        'rank' => 'integer',
        'is_winner' => 'boolean',
        'joined_at' => 'datetime',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
