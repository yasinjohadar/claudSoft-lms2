<?php

namespace App\Models\Gamification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leaderboard extends Model
{
    use HasFactory;

    protected $table = 'gamification_leaderboards';

    protected $fillable = [
        'name',
        'description',
        'type',
        'period',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
