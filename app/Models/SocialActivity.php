<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'description',
        'related_type',
        'related_id',
        'metadata',
        'is_public',
        'likes_count',
        'comments_count',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_public' => 'boolean',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(SocialActivityLike::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(SocialActivityComment::class);
    }
}
