<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RewardCatalog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rewards_catalog';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'category',
        'type',
        'points_cost',
        'level_required',
        'is_available',
        'is_limited',
        'stock_quantity',
        'purchased_count',
        'max_per_user',
        'available_from',
        'available_until',
        'validity_days',
        'rarity',
        'actual_value',
        'delivery_type',
        'delivery_instructions',
        'is_featured',
        'sort_order',
        'badge_color',
        'metadata',
    ];

    protected $casts = [
        'points_cost' => 'integer',
        'level_required' => 'integer',
        'is_available' => 'boolean',
        'is_limited' => 'boolean',
        'stock_quantity' => 'integer',
        'purchased_count' => 'integer',
        'max_per_user' => 'integer',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
        'validity_days' => 'integer',
        'actual_value' => 'integer',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get all user rewards for this catalog item
     */
    public function userRewards()
    {
        return $this->hasMany(UserReward::class, 'reward_id');
    }

    /**
     * Get users who purchased this reward
     */
    public function purchasers()
    {
        return $this->belongsToMany(User::class, 'user_rewards', 'reward_id', 'user_id')
            ->withPivot([
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
                'metadata'
            ])
            ->withTimestamps();
    }

    /**
     * Scope for available rewards
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope for featured rewards
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope by category
     */
    public function scopeOfCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope by rarity
     */
    public function scopeOfRarity($query, $rarity)
    {
        return $query->where('rarity', $rarity);
    }

    /**
     * Scope for in stock rewards
     */
    public function scopeInStock($query)
    {
        return $query->where(function($q) {
            $q->where('is_limited', false)
              ->orWhere(function($q2) {
                  $q2->where('is_limited', true)
                     ->whereColumn('purchased_count', '<', 'stock_quantity');
              });
        });
    }

    /**
     * Scope by maximum points cost
     */
    public function scopeMaxCost($query, $maxCost)
    {
        return $query->where('points_cost', '<=', $maxCost);
    }

    /**
     * Scope by minimum level
     */
    public function scopeForLevel($query, $level)
    {
        return $query->where('level_required', '<=', $level);
    }

    /**
     * Scope for currently available (within date range)
     */
    public function scopeCurrentlyAvailable($query)
    {
        return $query->where(function($q) {
            $q->whereNull('available_from')
              ->orWhere('available_from', '<=', now());
        })
        ->where(function($q) {
            $q->whereNull('available_until')
              ->orWhere('available_until', '>=', now());
        });
    }

    /**
     * Scope for limited rewards
     */
    public function scopeLimited($query)
    {
        return $query->where('is_limited', true);
    }

    /**
     * Scope for unlimited rewards
     */
    public function scopeUnlimited($query)
    {
        return $query->where('is_limited', false);
    }

    /**
     * Scope by delivery type
     */
    public function scopeByDeliveryType($query, $deliveryType)
    {
        return $query->where('delivery_type', $deliveryType);
    }
}
