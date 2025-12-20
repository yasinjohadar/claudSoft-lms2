<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PointsTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'points',
        'balance_before',
        'balance_after',
        'source',
        'description',
        'related_type',
        'related_id',
        'multiplier',
        'metadata',
        'admin_id',
        'expires_at',
        'is_expired',
    ];

    protected $casts = [
        'points' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
        'multiplier' => 'decimal:2',
        'metadata' => 'array',
        'expires_at' => 'datetime',
        'is_expired' => 'boolean',
    ];

    /**
     * Get the user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who created this transaction (for manual adjustments)
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the related model (polymorphic)
     */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for earned points
     */
    public function scopeEarned($query)
    {
        return $query->where('type', 'earn');
    }

    /**
     * Scope for spent points
     */
    public function scopeSpent($query)
    {
        return $query->where('type', 'spend');
    }

    /**
     * Scope for bonus points
     */
    public function scopeBonus($query)
    {
        return $query->where('type', 'bonus');
    }

    /**
     * Scope for non-expired transactions
     */
    public function scopeNotExpired($query)
    {
        return $query->where('is_expired', false);
    }

    /**
     * Scope for expired transactions
     */
    public function scopeExpired($query)
    {
        return $query->where('is_expired', true);
    }
}
