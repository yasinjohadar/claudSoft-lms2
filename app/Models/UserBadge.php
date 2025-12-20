<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserBadge extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'badge_id',
        'awarded_at',
        'reason',
        'related_type',
        'related_id',
        'progress',
        'is_seen',
        'is_featured',
        'points_awarded',
        'metadata',
    ];

    protected $casts = [
        'awarded_at' => 'datetime',
        'progress' => 'decimal:2',
        'is_seen' => 'boolean',
        'is_featured' => 'boolean',
        'points_awarded' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the user who owns the badge
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the badge
     */
    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }

    /**
     * Get the related model (polymorphic)
     */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for featured badges
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for unseen badges
     */
    public function scopeUnseen($query)
    {
        return $query->where('is_seen', false);
    }
}
