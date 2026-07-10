<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialActivityComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'social_activity_id',
        'user_id',
        'content',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(SocialActivity::class, 'social_activity_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
