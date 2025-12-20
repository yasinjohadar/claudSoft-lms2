<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payment_number',
        'invoice_id',
        'student_id',
        'amount',
        'payment_method_id',
        'payment_date',
        'transaction_id',
        'receipt_number',
        'status',
        'notes',
        'received_by',
        'reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    // Methods
    public static function generatePaymentNumber()
    {
        $year = date('Y');
        $lastPayment = self::whereYear('created_at', $year)
                          ->orderBy('id', 'desc')
                          ->first();

        $number = $lastPayment ? intval(substr($lastPayment->payment_number, -5)) + 1 : 1;

        return 'PAY-' . $year . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    public static function generateReceiptNumber()
    {
        $year = date('Y');
        $lastReceipt = self::whereYear('created_at', $year)
                          ->whereNotNull('receipt_number')
                          ->orderBy('id', 'desc')
                          ->first();

        $number = $lastReceipt ? intval(substr($lastReceipt->receipt_number, -5)) + 1 : 1;

        return 'RCP-' . $year . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->save();

        return $this;
    }

    public function markAsFailed($reason = null)
    {
        $this->status = 'failed';
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n\n" : '') . "سبب الفشل: " . $reason;
        }
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

        // Update invoice amounts
        if ($this->invoice) {
            $this->invoice->paid_amount -= $this->amount;
            $this->invoice->remaining_amount = $this->invoice->total_amount - $this->invoice->paid_amount;

            if ($this->invoice->paid_amount <= 0) {
                $this->invoice->status = 'issued';
            } elseif ($this->invoice->remaining_amount > 0) {
                $this->invoice->status = 'partial';
            }

            $this->invoice->save();
        }

        return $this;
    }

    public function refund($reason = null)
    {
        $this->status = 'refunded';
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n\n" : '') . "سبب الاسترداد: " . $reason;
        }
        $this->save();

        // Update invoice amounts
        if ($this->invoice) {
            $this->invoice->paid_amount -= $this->amount;
            $this->invoice->remaining_amount = $this->invoice->total_amount - $this->invoice->paid_amount;

            if ($this->invoice->paid_amount <= 0) {
                $this->invoice->status = 'issued';
            } elseif ($this->invoice->remaining_amount > 0) {
                $this->invoice->status = 'partial';
            }

            $this->invoice->save();
        }

        return $this;
    }

    // Attributes
    public function getIsCompletedAttribute()
    {
        return $this->status === 'completed';
    }

    public function getIsPendingAttribute()
    {
        return $this->status === 'pending';
    }

    public function getIsFailedAttribute()
    {
        return $this->status === 'failed';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge bg-warning">قيد الانتظار</span>',
            'completed' => '<span class="badge bg-success">مكتملة</span>',
            'failed' => '<span class="badge bg-danger">فاشلة</span>',
            'cancelled' => '<span class="badge bg-secondary">ملغاة</span>',
            'refunded' => '<span class="badge bg-info">مستردة</span>',
        ];

        return $badges[$this->status] ?? '';
    }

    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }
}
