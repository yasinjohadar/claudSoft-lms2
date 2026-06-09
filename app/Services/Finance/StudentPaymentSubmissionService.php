<?php

namespace App\Services\Finance;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Services\Storage\StorageHelperService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StudentPaymentSubmissionService
{
    public const RECEIPT_DISK = 'public';

    public function __construct(
        protected StorageHelperService $storageHelper
    ) {}

    public function invoiceHasPendingPayment(Invoice $invoice): bool
    {
        return $invoice->payments()->where('status', 'pending')->exists();
    }

    public function maxPayableAmount(Invoice $invoice): float
    {
        if ($this->invoiceHasPendingPayment($invoice)) {
            return 0.0;
        }

        return (float) $invoice->remaining_amount;
    }

    public function submit(Invoice $invoice, User $student, array $data, UploadedFile $receipt): Payment
    {
        if ((int) $invoice->student_id !== (int) $student->id) {
            throw new InvalidArgumentException('هذه الفاتورة لا تخصك.');
        }

        if (! in_array($invoice->status, ['issued', 'partial'], true)) {
            throw new InvalidArgumentException('لا يمكن تسديد هذه الفاتورة في حالتها الحالية.');
        }

        if ($invoice->remaining_amount <= 0) {
            throw new InvalidArgumentException('لا يوجد مبلغ متبقٍ على هذه الفاتورة.');
        }

        if ($this->invoiceHasPendingPayment($invoice)) {
            throw new InvalidArgumentException('يوجد طلب دفع معلّق قيد المراجعة لهذه الفاتورة.');
        }

        $amount = (float) $data['amount'];

        if ($amount <= 0) {
            throw new InvalidArgumentException('يجب أن يكون المبلغ أكبر من صفر.');
        }

        if ($amount > (float) $invoice->remaining_amount) {
            throw new InvalidArgumentException('المبلغ المدخل أكبر من المبلغ المتبقي.');
        }

        $uploadPath = 'payments/receipts/' . date('Y') . '/' . $student->id;
        $storedPath = $this->storageHelper->storeUploadedFile(
            self::RECEIPT_DISK,
            $uploadPath,
            $receipt,
            'document'
        );

        if (! $storedPath) {
            throw new InvalidArgumentException('فشل رفع إيصال الدفع. يرجى المحاولة مرة أخرى.');
        }

        return Payment::create([
            'payment_number' => Payment::generatePaymentNumber(),
            'invoice_id' => $invoice->id,
            'student_id' => $student->id,
            'amount' => $amount,
            'payment_method_id' => $data['payment_method_id'],
            'payment_date' => $data['payment_date'],
            'status' => 'pending',
            'notes' => $data['notes'] ?? null,
            'receipt_path' => $storedPath,
            'receipt_disk' => self::RECEIPT_DISK,
        ]);
    }

    public function approve(Payment $payment, User $admin): Payment
    {
        if ($payment->status !== 'pending') {
            throw new InvalidArgumentException('يمكن الموافقة على الدفعات المعلقة فقط.');
        }

        $invoice = $payment->invoice;

        if (! $invoice) {
            throw new InvalidArgumentException('لا توجد فاتورة مرتبطة بهذه الدفعة.');
        }

        if ((float) $payment->amount > (float) $invoice->remaining_amount) {
            throw new InvalidArgumentException('مبلغ الدفعة أكبر من المبلغ المتبقي على الفاتورة.');
        }

        return DB::transaction(function () use ($payment, $invoice, $admin) {
            $invoice->refresh();

            if ((float) $payment->amount > (float) $invoice->remaining_amount) {
                throw new InvalidArgumentException('مبلغ الدفعة أكبر من المبلغ المتبقي على الفاتورة.');
            }

            $invoice->paid_amount += $payment->amount;
            $invoice->remaining_amount = $invoice->total_amount - $invoice->paid_amount;

            if ($invoice->remaining_amount <= 0) {
                $invoice->status = 'paid';
            } elseif ($invoice->paid_amount > 0) {
                $invoice->status = 'partial';
            }

            $invoice->save();
            $invoice->updateRelatedCampEnrollments();

            $payment->status = 'completed';
            $payment->receipt_number = Payment::generateReceiptNumber();
            $payment->received_by = $admin->id;
            $payment->reviewed_by = $admin->id;
            $payment->reviewed_at = now();
            $payment->save();

            return $payment->fresh(['invoice', 'paymentMethod', 'student']);
        });
    }

    public function reject(Payment $payment, User $admin, string $reason): Payment
    {
        if ($payment->status !== 'pending') {
            throw new InvalidArgumentException('يمكن رفض الدفعات المعلقة فقط.');
        }

        $payment->status = 'failed';
        $payment->rejection_reason = $reason;
        $payment->reviewed_by = $admin->id;
        $payment->reviewed_at = now();
        $payment->save();

        return $payment->fresh(['invoice', 'paymentMethod', 'student']);
    }
}
