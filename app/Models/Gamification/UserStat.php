<?php

namespace App\Models\Gamification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStat extends Model
{
    use HasFactory;

    protected $table = 'gamification_user_stats';

    protected $fillable = [
        'user_id',
        'total_points',
        'total_xp',
        'current_level',
        'gems',
        'total_badges',
        'total_achievements',
        'current_streak',
        'longest_streak',
        'last_activity_date',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'total_points' => 'integer',
        'total_xp' => 'integer',
        'current_level' => 'integer',
        'gems' => 'integer',
        'total_badges' => 'integer',
        'total_achievements' => 'integer',
        'current_streak' => 'integer',
        'longest_streak' => 'integer',
        'last_activity_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
