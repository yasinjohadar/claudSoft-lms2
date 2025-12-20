<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'name_en',
        'description',
        'is_active',
        'requires_transaction_id',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_transaction_id' => 'boolean',
        'order' => 'integer',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }

    // Relationships
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
