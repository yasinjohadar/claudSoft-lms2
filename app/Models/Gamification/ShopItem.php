<?php

namespace App\Models\Gamification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopItem extends Model
{
    use HasFactory;

    protected $table = 'gamification_shop_items';

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'icon',
        'price_points',
        'price_gems',
        'discount_percentage',
        'discount_expires_at',
        'stock_quantity',
        'required_level',
        'purchase_limit',
        'required_badge_id',
        'sort_order',
        'is_active',
        'in_stock',
        'is_featured',
        'total_purchases',
        'last_purchased_at',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'price_points' => 'integer',
        'price_gems' => 'integer',
        'discount_percentage' => 'integer',
        'discount_expires_at' => 'datetime',
        'stock_quantity' => 'integer',
        'required_level' => 'integer',
        'purchase_limit' => 'integer',
        'required_badge_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'in_stock' => 'boolean',
        'is_featured' => 'boolean',
        'total_purchases' => 'integer',
        'last_purchased_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(ShopCategory::class, 'category_id');
    }
}
