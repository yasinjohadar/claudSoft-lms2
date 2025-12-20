<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reward_id',
        'purchased_at',
        'points_spent',
        'status',
        'delivery_code',
        'delivery_details',
        'delivered_at',
        'claimed_at',
        'expires_at',
        'is_expired',
        'transaction_id',
        'approved_by',
        'approved_at',
        'admin_notes',
        'metadata',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'reward_id' => 'integer',
        'purchased_at' => 'datetime',
        'points_spent' => 'integer',
        'delivered_at' => 'datetime',
        'claimed_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_expired' => 'boolean',
        'transaction_id' => 'integer',
        'approved_by' => 'integer',
        'approved_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the user who purchased the reward
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reward from catalog
     */
    public function reward()
    {
        return $this->belongsTo(RewardCatalog::class, 'reward_id');
    }

    /**
     * Get the points transaction
     */
    public function transaction()
    {
        return $this->belongsTo(PointsTransaction::class, 'transaction_id');
    }

    /**
     * Get the admin who approved the reward
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope for pending rewards
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for processing rewards
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope for delivered rewards
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Scope for claimed rewards
     */
    public function scopeClaimed($query)
    {
        return $query->where('status', 'claimed');
    }

    /**
     * Scope for expired rewards
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope for cancelled rewards
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope for rewards expiring soon
     */
    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays($days))
            ->where('expires_at', '>=', now())
            ->whereIn('status', ['pending', 'processing', 'delivered']);
    }

    /**
     * Scope for active rewards (not expired or cancelled)
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['expired', 'cancelled']);
    }

    /**
     * Scope for rewards needing approval
     */
    public function scopeNeedsApproval($query)
    {
        return $query->where('status', 'pending')
            ->whereNull('approved_at');
    }

    /**
     * Scope for approved rewards
     */
    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    /**
     * Scope for unclaimed rewards
     */
    public function scopeUnclaimed($query)
    {
        return $query->where('status', 'delivered')
            ->whereNull('claimed_at');
    }
}
