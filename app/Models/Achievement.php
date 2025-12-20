<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Achievement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'type',
        'category',
        'tier',
        'criteria',
        'target_value',
        'metric',
        'points_reward',
        'xp_reward',
        'badge_id',
        'is_active',
        'is_secret',
        'is_repeatable',
        'sort_order',
        'color_code',
        'completed_count',
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_active' => 'boolean',
        'is_secret' => 'boolean',
        'is_repeatable' => 'boolean',
        'target_value' => 'integer',
        'points_reward' => 'integer',
        'xp_reward' => 'integer',
        'completed_count' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get the badge awarded for this achievement
     */
    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }

    /**
     * Get all users who have this achievement
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->withPivot([
                'current_value',
                'target_value',
                'progress_percentage',
                'status',
                'started_at',
                'completed_at',
                'claimed_at',
                'related_type',
                'related_id',
                'progress_data',
                'points_claimed',
                'xp_claimed',
                'is_notified'
            ])
            ->withTimestamps();
    }

    /**
     * Get user achievement records
     */
    public function userAchievements()
    {
        return $this->hasMany(UserAchievement::class);
    }

    /**
     * Scope for active achievements
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for visible achievements (not secret)
     */
    public function scopeVisible($query)
    {
        return $query->where('is_secret', false);
    }

    /**
     * Scope by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by tier
     */
    public function scopeOfTier($query, $tier)
    {
        return $query->where('tier', $tier);
    }
}
