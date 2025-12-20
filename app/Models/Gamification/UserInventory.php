<?php

namespace App\Models\Gamification;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserInventory extends Model
{
    protected $table = 'gamification_user_inventory';

    protected $fillable = [
        'user_id',
        'shop_item_id',
        'purchase_id',
        'quantity',
        'status',
        'is_active',
        'acquired_at',
        'activated_at',
        'deactivated_at',
        'consumed_at',
        'expires_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'is_active' => 'boolean',
        'acquired_at' => 'datetime',
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
        'consumed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shopItem(): BelongsTo
    {
        return $this->belongsTo(ShopItem::class, 'shop_item_id');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(UserPurchase::class, 'purchase_id');
    }
}
