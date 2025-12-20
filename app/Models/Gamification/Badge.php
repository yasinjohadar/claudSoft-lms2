<?php

namespace App\Models\Gamification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $table = 'gamification_badges';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'type',
        'category',
        'rarity',
        'criteria',
        'requirement_type',
        'requirement_value',
        'points_reward',
        'points_value',
        'is_active',
        'is_visible',
        'is_hidden',
        'sort_order',
        'color_code',
        'awarded_count',
    ];

    protected $casts = [
        'requirement_value' => 'integer',
        'points_reward' => 'integer',
        'points_value' => 'integer',
        'is_active' => 'boolean',
        'is_visible' => 'boolean',
        'is_hidden' => 'boolean',
        'sort_order' => 'integer',
        'awarded_count' => 'integer',
        'criteria' => 'array',
    ];

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'gamification_user_badges')
            ->withPivot('awarded_at')
            ->withTimestamps();
    }

    public function userBadges()
    {
        return $this->belongsToMany(\App\Models\User::class, 'gamification_user_badges', 'badge_id', 'user_id')
            ->withPivot('awarded_at')
            ->withTimestamps();
    }
}
