<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserChallenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'challenge_id',
        'current_value',
        'target_value',
        'progress_percentage',
        'status',
        'joined_at',
        'started_at',
        'completed_at',
        'expires_at',
        'points_earned',
        'xp_earned',
        'rewards_claimed',
        'progress_data',
        'team_id',
        'is_team_leader',
        'is_notified',
        'attempts_count',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'challenge_id' => 'integer',
        'current_value' => 'integer',
        'target_value' => 'integer',
        'progress_percentage' => 'decimal:2',
        'joined_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
        'points_earned' => 'integer',
        'xp_earned' => 'integer',
        'rewards_claimed' => 'boolean',
        'progress_data' => 'array',
        'is_team_leader' => 'boolean',
        'is_notified' => 'boolean',
        'attempts_count' => 'integer',
    ];

    /**
     * Get the user for this challenge
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the challenge
     */
    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    /**
     * Scope for not started challenges
     */
    public function scopeNotStarted($query)
    {
        return $query->where('status', 'not_started');
    }

    /**
     * Scope for in progress challenges
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for completed challenges
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed challenges
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for expired challenges
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope for team leaders
     */
    public function scopeTeamLeaders($query)
    {
        return $query->where('is_team_leader', true);
    }

    /**
     * Scope by team
     */
    public function scopeInTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    /**
     * Scope for unclaimed rewards
     */
    public function scopeUnclaimedRewards($query)
    {
        return $query->where('status', 'completed')
            ->where('rewards_claimed', false);
    }

    /**
     * Scope for active challenges (not expired and not failed)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['not_started', 'in_progress']);
    }

    /**
     * Scope by minimum progress
     */
    public function scopeMinProgress($query, $minProgress)
    {
        return $query->where('progress_percentage', '>=', $minProgress);
    }

    /**
     * Scope for challenges expiring soon
     */
    public function scopeExpiringSoon($query, $hours = 24)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addHours($hours))
            ->where('expires_at', '>=', now())
            ->whereIn('status', ['not_started', 'in_progress']);
    }
}
