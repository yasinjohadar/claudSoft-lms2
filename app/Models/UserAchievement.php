<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'achievement_id',
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
        'is_notified',
    ];

    protected $casts = [
        'current_value' => 'integer',
        'target_value' => 'integer',
        'progress_percentage' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'claimed_at' => 'datetime',
        'progress_data' => 'array',
        'points_claimed' => 'integer',
        'xp_claimed' => 'integer',
        'is_notified' => 'boolean',
    ];

    /**
     * Get the user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the achievement
     */
    public function achievement()
    {
        return $this->belongsTo(Achievement::class);
    }

    /**
     * Get the related model (polymorphic)
     */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for in-progress achievements
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for completed achievements
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for claimed achievements
     */
    public function scopeClaimed($query)
    {
        return $query->where('status', 'claimed');
    }

    /**
     * Scope for unclaimed achievements
     */
    public function scopeUnclaimed($query)
    {
        return $query->where('status', 'completed')
            ->whereNull('claimed_at');
    }
}
