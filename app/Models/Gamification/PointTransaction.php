<?php

namespace App\Models\Gamification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model
{
    use HasFactory;

    protected $table = 'gamification_point_transactions';

    protected $fillable = [
        'user_id',
        'points',
        'type',
        'source',
        'reason',
        'related_type',
        'related_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'points' => 'integer',
        'related_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
