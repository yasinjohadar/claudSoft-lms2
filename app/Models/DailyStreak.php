<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailyStreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'activities_count',
        'points_earned',
        'xp_earned',
        'current_streak',
        'longest_streak',
        'streak_start_date',
        'last_login_date',
        'last_streak_date',
        'is_active',
        'freeze_available',
        'freeze_count',
        'milestones',
        'total_points_earned',
        'total_badges_earned',
        'streak_history',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'date' => 'date',
        'activities_count' => 'integer',
        'points_earned' => 'integer',
        'xp_earned' => 'integer',
        'current_streak' => 'integer',
        'longest_streak' => 'integer',
        'streak_start_date' => 'date',
        'last_login_date' => 'date',
        'last_streak_date' => 'date',
        'is_active' => 'boolean',
        'freeze_available' => 'boolean',
        'freeze_count' => 'integer',
        'milestones' => 'array',
        'total_points_earned' => 'integer',
        'total_badges_earned' => 'integer',
        'streak_history' => 'array',
    ];

    /**
     * Get the user this streak belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active streaks
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for streaks with freeze available
     */
    public function scopeFreezeAvailable($query)
    {
        return $query->where('freeze_available', true);
    }

    /**
     * Scope by minimum current streak
     */
    public function scopeMinStreak($query, $minStreak)
    {
        return $query->where('current_streak', '>=', $minStreak);
    }

    /**
     * Scope for top streaks
     */
    public function scopeTopStreaks($query, $limit = 10)
    {
        return $query->orderBy('current_streak', 'desc')->limit($limit);
    }

    /**
     * Scope for longest streaks
     */
    public function scopeLongestStreaks($query, $limit = 10)
    {
        return $query->orderBy('longest_streak', 'desc')->limit($limit);
    }

    /**
     * Scope for recent activity
     */
    public function scopeRecentActivity($query, $days = 7)
    {
        return $query->where('last_login_date', '>=', now()->subDays($days));
    }
}
