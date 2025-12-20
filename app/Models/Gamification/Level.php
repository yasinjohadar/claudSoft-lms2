<?php

namespace App\Models\Gamification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    protected $table = 'gamification_levels';

    protected $fillable = [
        'level',
        'name',
        'xp_required',
        'points_reward',
        'gems_reward',
    ];

    protected $casts = [
        'level' => 'integer',
        'xp_required' => 'integer',
        'points_reward' => 'integer',
        'gems_reward' => 'integer',
    ];
}
