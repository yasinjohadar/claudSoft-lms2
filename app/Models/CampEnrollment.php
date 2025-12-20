<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampEnrollment extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'camp_enrollments';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'camp_id',
        'student_id',
        'enrollment_date',
        'status',
        'payment_status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'enrollment_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the training camp that owns the enrollment.
     */
    public function camp()
    {
        return $this->belongsTo(TrainingCamp::class, 'camp_id');
    }

    /**
     * Get the student that owns the enrollment.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the invoice items for this enrollment.
     */
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the invoice for this enrollment.
     */
    public function invoice()
    {
        return $this->hasOneThrough(
            Invoice::class,
            InvoiceItem::class,
            'camp_enrollment_id',
            'id',
            'id',
            'invoice_id'
        );
    }

    /**
     * Scope a query to only include pending enrollments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved enrollments.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected enrollments.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to only include cancelled enrollments.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope a query to only include paid enrollments.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope a query to only include unpaid enrollments.
     */
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    /**
     * Get the status label in Arabic.
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'قيد الانتظار',
            'approved' => 'مقبول',
            'rejected' => 'مرفوض',
            'cancelled' => 'ملغي',
            default => 'غير معروف',
        };
    }

    /**
     * Get the payment status label in Arabic.
     */
    public function getPaymentStatusLabelAttribute()
    {
        return match($this->payment_status) {
            'unpaid' => 'غير مدفوع',
            'paid' => 'مدفوع',
            'refunded' => 'مسترجع',
            default => 'غير معروف',
        };
    }

    /**
     * Check if enrollment has an invoice.
     */
    public function hasInvoice()
    {
        return $this->invoiceItems()->exists();
    }

    /**
     * Get the total amount for this enrollment.
     */
    public function getTotalAmountAttribute()
    {
        return $this->invoiceItems()->sum('total_price');
    }

    /**
     * Get the paid amount for this enrollment.
     */
    public function getPaidAmountAttribute()
    {
        $invoiceItem = $this->invoiceItems()->with('invoice')->first();
        return $invoiceItem ? $invoiceItem->invoice->paid_amount : 0;
    }

    /**
     * Get the remaining amount for this enrollment.
     */
    public function getRemainingAmountAttribute()
    {
        $invoiceItem = $this->invoiceItems()->with('invoice')->first();
        return $invoiceItem ? $invoiceItem->invoice->remaining_amount : 0;
    }
}
