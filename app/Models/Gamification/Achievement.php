<?php

namespace App\Models\Gamification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $table = 'gamification_achievements';

    protected $fillable = [
        'name',
        'description',
        'icon',
        'tier',
        'requirement_type',
        'requirement_value',
        'points_reward',
        'is_active',
    ];

    protected $casts = [
        'requirement_value' => 'integer',
        'points_reward' => 'integer',
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'gamification_user_achievements')
            ->withPivot('unlocked_at')
            ->withTimestamps();
    }
}
