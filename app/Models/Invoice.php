<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'student_id',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'tax_amount',
        'discount_amount',
        'status',
        'issue_date',
        'due_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function pendingPayment()
    {
        return $this->hasOne(Payment::class)->where('status', 'pending')->latestOfMany();
    }

    public function hasPendingPayment(): bool
    {
        return $this->payments()->where('status', 'pending')->exists();
    }

    public function canAcceptStudentPayment(): bool
    {
        return $this->remaining_amount > 0
            && in_array($this->status, ['issued', 'partial'], true)
            && ! $this->hasPendingPayment();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($invoice) {
            $invoice->revertCampEnrollmentPaymentStatus();
        });
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereIn('status', ['issued', 'partial']);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['issued', 'partial']);
    }

    // Methods
    public static function generateInvoiceNumber()
    {
        $year = date('Y');
        $lastInvoice = self::whereYear('created_at', $year)
                          ->orderBy('id', 'desc')
                          ->first();

        $number = $lastInvoice ? intval(substr($lastInvoice->invoice_number, -5)) + 1 : 1;

        return 'INV-' . $year . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    public function calculateTotals()
    {
        $subtotal = $this->items->sum('total_price');

        $this->total_amount = $subtotal + $this->tax_amount - $this->discount_amount;
        $this->remaining_amount = $this->total_amount - $this->paid_amount;

        $this->save();

        return $this;
    }

    public function recordPayment($amount, $paymentData = [])
    {
        $this->paid_amount += $amount;
        $this->remaining_amount = $this->total_amount - $this->paid_amount;

        // Update status based on payment
        if ($this->remaining_amount <= 0) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        }

        $this->save();

        // Update related camp enrollments
        $this->updateRelatedCampEnrollments();

        // Create payment record
        $payment = $this->payments()->create(array_merge([
            'payment_number' => Payment::generatePaymentNumber(),
            'student_id' => $this->student_id,
            'amount' => $amount,
            'payment_date' => now(),
            'status' => 'completed',
        ], $paymentData));

        return $payment;
    }

    public function markAsPaid()
    {
        $this->status = 'paid';
        $this->paid_amount = $this->total_amount;
        $this->remaining_amount = 0;
        $this->save();

        // Update related camp enrollments
        $this->updateRelatedCampEnrollments();

        return $this;
    }

    public function markAsPartial()
    {
        if ($this->paid_amount > 0 && $this->remaining_amount > 0) {
            $this->status = 'partial';
            $this->save();
        }

        return $this;
    }

    public function markAsIssued()
    {
        $this->status = 'issued';
        $this->save();

        return $this;
    }

    public function cancel($reason = null)
    {
        $this->status = 'cancelled';
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n\n" : '') . "سبب الإلغاء: " . $reason;
        }
        $this->save();

        return $this;
    }

    /**
     * Update payment status of related camp enrollments based on invoice status
     */
    public function updateRelatedCampEnrollments()
    {
        // Get all invoice items with camp_enrollment_id
        $invoiceItems = $this->items()->whereNotNull('camp_enrollment_id')->get();
        
        foreach ($invoiceItems as $item) {
            if ($item->camp_enrollment_id) {
                $enrollment = CampEnrollment::find($item->camp_enrollment_id);
                if ($enrollment) {
                    // Update payment status based on invoice status
                    if ($this->status === 'paid' && $this->remaining_amount <= 0) {
                        $enrollment->payment_status = 'paid';
                    } elseif ($this->status === 'issued' && $this->paid_amount == 0) {
                        $enrollment->payment_status = 'unpaid';
                    } elseif ($this->status === 'partial') {
                        // For partial payments, keep as unpaid
                        // You can change this logic if needed
                        $enrollment->payment_status = 'unpaid';
                    }
                    $enrollment->save();
                }
            }
        }
    }

    /**
     * Revert payment status of related camp enrollments to unpaid when invoice is deleted
     */
    public function revertCampEnrollmentPaymentStatus()
    {
        // Get all invoice items with camp_enrollment_id
        $invoiceItems = $this->items()->whereNotNull('camp_enrollment_id')->get();
        
        foreach ($invoiceItems as $item) {
            if ($item->camp_enrollment_id) {
                $enrollment = CampEnrollment::find($item->camp_enrollment_id);
                if ($enrollment) {
                    // Revert to unpaid when invoice is deleted
                    $enrollment->payment_status = 'unpaid';
                    $enrollment->save();
                }
            }
        }
    }

    // Attributes
    public function getIsOverdueAttribute()
    {
        return $this->due_date
               && $this->due_date->isPast()
               && in_array($this->status, ['issued', 'partial']);
    }

    public function getIsPaidAttribute()
    {
        return $this->status === 'paid';
    }

    public function getIsPartialAttribute()
    {
        return $this->status === 'partial';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => '<span class="badge bg-secondary">مسودة</span>',
            'issued' => '<span class="badge bg-info">صادرة</span>',
            'partial' => '<span class="badge bg-warning">مدفوعة جزئياً</span>',
            'paid' => '<span class="badge bg-success">مدفوعة</span>',
            'cancelled' => '<span class="badge bg-danger">ملغاة</span>',
            'refunded' => '<span class="badge bg-dark">مستردة</span>',
        ];

        return $badges[$this->status] ?? '';
    }
}
