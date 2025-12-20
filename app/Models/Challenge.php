<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Challenge extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'type',
        'frequency',
        'difficulty',
        'requirements',
        'target_value',
        'metric',
        'points_reward',
        'xp_reward',
        'badge_id',
        'start_date',
        'end_date',
        'duration_hours',
        'participation_type',
        'max_participants',
        'min_level',
        'is_team_challenge',
        'team_size',
        'is_active',
        'is_visible',
        'is_featured',
        'participants_count',
        'completed_count',
        'sort_order',
        'color_code',
    ];

    protected $casts = [
        'requirements' => 'array',
        'target_value' => 'integer',
        'points_reward' => 'integer',
        'xp_reward' => 'integer',
        'badge_id' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'duration_hours' => 'integer',
        'max_participants' => 'integer',
        'min_level' => 'integer',
        'is_team_challenge' => 'boolean',
        'team_size' => 'integer',
        'is_active' => 'boolean',
        'is_visible' => 'boolean',
        'is_featured' => 'boolean',
        'participants_count' => 'integer',
        'completed_count' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get the badge awarded for this challenge
     */
    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }

    /**
     * Get all user challenges for this challenge
     */
    public function userChallenges()
    {
        return $this->hasMany(UserChallenge::class);
    }

    /**
     * Get users participating in this challenge
     */
    public function participants()
    {
        return $this->belongsToMany(User::class, 'user_challenges')
            ->withPivot([
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
                'attempts_count'
            ])
            ->withTimestamps();
    }

    /**
     * Scope for active challenges
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for visible challenges
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope for featured challenges
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by difficulty
     */
    public function scopeOfDifficulty($query, $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    /**
     * Scope for ongoing challenges
     */
    public function scopeOngoing($query)
    {
        return $query->where('start_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Scope for upcoming challenges
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    /**
     * Scope for expired challenges
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('end_date')
            ->where('end_date', '<', now());
    }

    /**
     * Scope for team challenges
     */
    public function scopeTeamChallenges($query)
    {
        return $query->where('is_team_challenge', true);
    }

    /**
     * Scope for individual challenges
     */
    public function scopeIndividualChallenges($query)
    {
        return $query->where('is_team_challenge', false);
    }

    /**
     * Scope for daily challenges
     */
    public function scopeDaily($query)
    {
        return $query->where('type', 'daily');
    }

    /**
     * Scope for weekly challenges
     */
    public function scopeWeekly($query)
    {
        return $query->where('type', 'weekly');
    }

    /**
     * Scope for monthly challenges
     */
    public function scopeMonthly($query)
    {
        return $query->where('type', 'monthly');
    }
}
