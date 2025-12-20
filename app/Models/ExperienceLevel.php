<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExperienceLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'name',
        'description',
        'title',
        'xp_required',
        'xp_to_next',
        'points_reward',
        'badge_id',
        'unlocked_features',
        'unlocked_rewards',
        'icon',
        'color_code',
        'sort_order',
        'users_count',
        'is_active',
    ];

    protected $casts = [
        'level' => 'integer',
        'xp_required' => 'integer',
        'xp_to_next' => 'integer',
        'points_reward' => 'integer',
        'badge_id' => 'integer',
        'unlocked_features' => 'array',
        'unlocked_rewards' => 'array',
        'sort_order' => 'integer',
        'users_count' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the badge awarded at this level
     */
    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }

    /**
     * Get users at this experience level
     */
    public function users()
    {
        return $this->hasMany(User::class, 'experience_level_id');
    }

    /**
     * Scope for active levels
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by level number
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope for levels up to a certain level
     */
    public function scopeUpToLevel($query, $level)
    {
        return $query->where('level', '<=', $level);
    }

    /**
     * Scope for levels requiring minimum XP
     */
    public function scopeMinXp($query, $xp)
    {
        return $query->where('xp_required', '<=', $xp);
    }

    /**
     * Scope for next level after given level
     */
    public function scopeNextLevel($query, $currentLevel)
    {
        return $query->where('level', '>', $currentLevel)
            ->orderBy('level', 'asc')
            ->limit(1);
    }

    /**
     * Scope for previous level before given level
     */
    public function scopePreviousLevel($query, $currentLevel)
    {
        return $query->where('level', '<', $currentLevel)
            ->orderBy('level', 'desc')
            ->limit(1);
    }

    /**
     * Get level for specific XP amount
     */
    public static function getLevelForXp($xp)
    {
        return static::where('xp_required', '<=', $xp)
            ->orderBy('level', 'desc')
            ->first();
    }

    /**
     * Get all levels ordered by level number
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('level', 'asc');
    }
}
