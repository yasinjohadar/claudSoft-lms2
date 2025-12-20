<?php

namespace App\Models\Gamification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    use HasFactory;

    protected $table = 'gamification_challenges';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'type',
        'difficulty',
        'target_type',
        'target_value',
        'points_reward',
        'reward_xp',
        'reward_gems',
        'badge_id',
        'starts_at',
        'ends_at',
        'is_active',
        'auto_assign',
        'sort_order',
    ];

    protected $casts = [
        'target_value' => 'integer',
        'points_reward' => 'integer',
        'reward_xp' => 'integer',
        'reward_gems' => 'integer',
        'sort_order' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'auto_assign' => 'boolean',
    ];

    /**
     * العلاقة مع الشارة
     */
    public function badge()
    {
        return $this->belongsTo(\App\Models\Gamification\Badge::class, 'badge_id');
    }

    /**
     * العلاقة مع تحديات المستخدمين
     */
    public function userChallenges()
    {
        return $this->hasMany(\App\Models\UserChallenge::class, 'challenge_id');
    }
}
