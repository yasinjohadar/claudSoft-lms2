<?php

namespace App\Services;

use App\Models\CampEnrollment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\TrainingCamp;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TrainingCampEnrollmentService
{
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
     * Update camp enrollment status and/or payment status.
     */
    public function updateEnrollment(
        CampEnrollment $enrollment,
        ?string $status = null,
        ?string $paymentStatus = null
    ): CampEnrollment {
        return DB::transaction(function () use ($enrollment, $status, $paymentStatus) {
            $camp = $enrollment->camp;

            if ($status !== null && $status !== $enrollment->status) {
                $oldStatus = $enrollment->status;
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
}
