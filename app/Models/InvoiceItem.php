<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'itemable_type',
        'itemable_id',
        'description',
        'quantity',
        'unit_price',
        'total_price',
        'camp_enrollment_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }

    public function campEnrollment()
    {
        return $this->belongsTo(CampEnrollment::class);
    }

    /**
     * Display name for linked training camp or camp-style course group.
     */
    public function relatedCampOrGroupName(): ?string
    {
        $campName = $this->campEnrollment?->camp?->name;
        if ($campName) {
            return $campName;
        }

        if ($this->itemable_type === CourseGroupMember::class) {
            $member = $this->relationLoaded('itemable')
                ? $this->itemable
                : $this->itemable()->with('group')->first();

            return $member?->group?->name;
        }

        return null;
    }

    // Methods
    public function calculateTotal()
    {
        $this->total_price = $this->quantity * $this->unit_price;
        $this->save();

        return $this;
    }

    // Attributes
    public function getFormattedUnitPriceAttribute()
    {
        return '$' . number_format($this->unit_price, 2);
    }

    public function getFormattedTotalPriceAttribute()
    {
        return '$' . number_format($this->total_price, 2);
    }
}
