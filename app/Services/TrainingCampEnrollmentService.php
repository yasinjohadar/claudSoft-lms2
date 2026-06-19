<?php

namespace App\Services;

use App\Models\CampEnrollment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\TrainingCamp;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TrainingCampEnrollmentService
{
    public const REMOVAL_BLOCKED_MESSAGE = 'لا يمكن إلغاء التسجيل: توجد فاتورة مدفوعة أو مدفوعة جزئياً مرتبطة بهذا المعسكر. عالج الفاتورة يدوياً أولاً.';

    /**
     * Create camp enrollment, invoice, and invoice item (same rules as TrainingCampController::storeEnrollment).
     *
     * @throws InvalidArgumentException When the student is already enrolled in this camp.
     */
    public function enrollStudent(
        TrainingCamp $camp,
        int $studentId,
        ?string $status = null,
        ?string $paymentStatus = null,
        ?string $notes = null,
        ?float $price = null
    ): CampEnrollment {
        $exists = CampEnrollment::where('camp_id', $camp->id)
            ->where('student_id', $studentId)
            ->exists();

        if ($exists) {
            throw new InvalidArgumentException('الطالب مسجل بالفعل في هذا المعسكر');
        }

        return DB::transaction(function () use ($camp, $studentId, $status, $paymentStatus, $notes, $price) {
            $fee = $price ?? (float) $camp->price;

            $enrollment = CampEnrollment::create([
                'camp_id' => $camp->id,
                'student_id' => $studentId,
                'status' => $status ?? 'pending',
                'payment_status' => $paymentStatus ?? 'unpaid',
                'notes' => $notes,
                'enrollment_date' => now(),
            ]);

            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'student_id' => $studentId,
                'total_amount' => $fee,
                'paid_amount' => 0,
                'remaining_amount' => $fee,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'status' => 'issued',
                'issue_date' => now(),
                'due_date' => $camp->start_date,
                'notes' => 'فاتورة التسجيل في معسكر: ' . $camp->name,
                'created_by' => auth()->id(),
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'رسوم التسجيل في معسكر: ' . $camp->name,
                'quantity' => 1,
                'unit_price' => $fee,
                'total_price' => $fee,
                'camp_enrollment_id' => $enrollment->id,
            ]);

            if ($enrollment->status === 'approved') {
                $camp->increment('current_participants');
            }

            return $enrollment;
        });
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function findInvoicesForEnrollment(CampEnrollment $enrollment): Collection
    {
        $invoiceIds = InvoiceItem::query()
            ->where('camp_enrollment_id', $enrollment->id)
            ->pluck('invoice_id')
            ->unique()
            ->filter();

        if ($invoiceIds->isEmpty()) {
            return collect();
        }

        return Invoice::query()->whereIn('id', $invoiceIds)->get();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function assertEnrollmentCanBeRemoved(CampEnrollment $enrollment): void
    {
        foreach ($this->findInvoicesForEnrollment($enrollment) as $invoice) {
            if ($this->invoiceBlocksCampRemoval($invoice)) {
                throw new InvalidArgumentException(self::REMOVAL_BLOCKED_MESSAGE);
            }
        }
    }

    public function invoiceBlocksCampRemoval(Invoice $invoice): bool
    {
        return (float) $invoice->paid_amount > 0
            || in_array($invoice->status, ['paid', 'partial'], true);
    }

    /**
     * @return array<int, int>
     */
    public function cancelUnpaidInvoicesForEnrollment(CampEnrollment $enrollment, string $reason): array
    {
        $cancelledIds = [];

        foreach ($this->findInvoicesForEnrollment($enrollment) as $invoice) {
            if ($this->invoiceBlocksCampRemoval($invoice)) {
                continue;
            }

            if (in_array($invoice->status, ['cancelled', 'refunded'], true)) {
                continue;
            }

            $invoice->cancel($reason);
            $cancelledIds[] = $invoice->id;
        }

        return $cancelledIds;
    }

    /**
     * Update camp enrollment status and/or payment status.
     */
    public function updateEnrollment(
        CampEnrollment $enrollment,
        ?string $status = null,
        ?string $paymentStatus = null
    ): CampEnrollment {
        return DB::transaction(function () use ($enrollment, $status, $paymentStatus) {
            $camp = $enrollment->camp;
            $oldStatus = $enrollment->status;

            if ($status !== null && $status !== $enrollment->status) {
                if (in_array($status, ['cancelled', 'rejected'], true)) {
                    $this->assertEnrollmentCanBeRemoved($enrollment);
                    $campName = $camp?->name ?? 'المعسكر';
                    $this->cancelUnpaidInvoicesForEnrollment(
                        $enrollment,
                        'إلغاء تلقائي بسبب تغيير حالة التسجيل في المعسكر: ' . $campName
                    );
                }

                $enrollment->status = $status;

                if ($camp) {
                    if ($oldStatus === 'approved' && $status !== 'approved') {
                        $camp->decrement('current_participants');
                    } elseif ($oldStatus !== 'approved' && $status === 'approved') {
                        $camp->increment('current_participants');
                    }
                }
            }

            if ($paymentStatus !== null) {
                $enrollment->payment_status = $paymentStatus;
            }

            $enrollment->save();

            return $enrollment->fresh(['camp.category', 'invoice']);
        });
    }

    /**
     * Remove a student from a training camp (delete enrollment).
     *
     * @return array{
     *     camp_id:int|null,
     *     camp:array{id:int,name:string,price:float|null}|null,
     *     cancelled_invoice_ids:array<int,int>
     * }
     *
     * @throws InvalidArgumentException
     */
    public function removeEnrollment(CampEnrollment $enrollment): array
    {
        return DB::transaction(function () use ($enrollment) {
            $this->assertEnrollmentCanBeRemoved($enrollment);

            $camp = $enrollment->camp;
            $campId = $enrollment->camp_id;
            $wasApproved = $enrollment->status === 'approved';
            $campName = $camp?->name ?? 'المعسكر';

            $cancelledInvoiceIds = $this->cancelUnpaidInvoicesForEnrollment(
                $enrollment,
                'إلغاء تلقائي بسبب إزالة الطالب من المعسكر: ' . $campName
            );

            $campData = $camp ? [
                'id' => $camp->id,
                'name' => $camp->name,
                'price' => (float) $camp->price,
            ] : null;

            $enrollment->delete();

            if ($camp && $wasApproved) {
                $camp->decrement('current_participants');
            }

            return [
                'camp_id' => $campId,
                'camp' => $campData,
                'cancelled_invoice_ids' => $cancelledInvoiceIds,
            ];
        });
    }
}
