<?php

namespace App\Models\Gamification;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPurchase extends Model
{
    protected $table = 'gamification_user_purchases';

    protected $fillable = [
        'user_id',
        'shop_item_id',
        'payment_method',
        'original_price',
        'discount_percentage',
        'final_price',
        'purchased_at',
        'metadata',
    ];

    protected $casts = [
        'original_price' => 'integer',
        'discount_percentage' => 'integer',
        'final_price' => 'integer',
        'purchased_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shopItem(): BelongsTo
    {
        return $this->belongsTo(ShopItem::class, 'shop_item_id');
    }
}
