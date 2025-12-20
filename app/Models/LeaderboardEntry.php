<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeaderboardEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'leaderboard_id',
        'user_id',
        'rank',
        'previous_rank',
        'rank_change',
        'score',
        'previous_score',
        'division',
        'previous_division',
        'metrics',
        'is_qualified',
        'last_activity_at',
        'is_top_1',
        'is_top_3',
        'is_top_10',
    ];

    protected $casts = [
        'leaderboard_id' => 'integer',
        'user_id' => 'integer',
        'rank' => 'integer',
        'previous_rank' => 'integer',
        'rank_change' => 'integer',
        'score' => 'integer',
        'previous_score' => 'integer',
        'metrics' => 'array',
        'is_qualified' => 'boolean',
        'last_activity_at' => 'datetime',
        'is_top_1' => 'boolean',
        'is_top_3' => 'boolean',
        'is_top_10' => 'boolean',
    ];

    /**
     * Get the leaderboard this entry belongs to
     */
    public function leaderboard()
    {
        return $this->belongsTo(Leaderboard::class);
    }

    /**
     * Get the user for this entry
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for qualified entries
     */
    public function scopeQualified($query)
    {
        return $query->where('is_qualified', true);
    }

    /**
     * Scope for top 1 entries
     */
    public function scopeTop1($query)
    {
        return $query->where('is_top_1', true);
    }

    /**
     * Scope for top 3 entries
     */
    public function scopeTop3($query)
    {
        return $query->where('is_top_3', true);
    }

    /**
     * Scope for top 10 entries
     */
    public function scopeTop10($query)
    {
        return $query->where('is_top_10', true);
    }

    /**
     * Scope by division
     */
    public function scopeInDivision($query, $division)
    {
        return $query->where('division', $division);
    }

    /**
     * Scope for entries with rank improvement
     */
    public function scopeImproved($query)
    {
        return $query->where('rank_change', '>', 0);
    }

    /**
     * Scope for entries with rank decline
     */
    public function scopeDeclined($query)
    {
        return $query->where('rank_change', '<', 0);
    }

    /**
     * Scope by minimum score
     */
    public function scopeMinScore($query, $minScore)
    {
        return $query->where('score', '>=', $minScore);
    }
}
