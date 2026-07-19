<?php

namespace App\Services;

use App\Models\CourseGroup;
use App\Models\CourseGroupMember;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;

class CourseGroupBillingService
{
    /**
     * Create an invoice for a member joining a camp-style paid group.
     * No-op when the group is not a camp, or an invoice already exists for this membership.
     */
    public function createInvoiceForMember(CourseGroup $group, CourseGroupMember $member): ?Invoice
    {
        if (! $group->isCamp()) {
            return null;
        }

        $alreadyBilled = InvoiceItem::query()
            ->where('itemable_type', CourseGroupMember::class)
            ->where('itemable_id', $member->id)
            ->exists();

        if ($alreadyBilled) {
            return null;
        }

        return DB::transaction(function () use ($group, $member) {
            $fee = (float) ($group->price ?? 0);

            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'student_id' => $member->student_id,
                'total_amount' => $fee,
                'paid_amount' => 0,
                'remaining_amount' => $fee,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'status' => 'issued',
                'issue_date' => now(),
                'due_date' => $group->start_date ?? now(),
                'notes' => 'فاتورة التسجيل في مجموعة (معسكر): '.$group->name,
                'created_by' => auth()->id(),
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'itemable_type' => CourseGroupMember::class,
                'itemable_id' => $member->id,
                'description' => 'رسوم التسجيل في مجموعة (معسكر): '.$group->name,
                'quantity' => 1,
                'unit_price' => $fee,
                'total_price' => $fee,
            ]);

            if ($member->payment_status !== 'unpaid') {
                $member->update(['payment_status' => 'unpaid']);
            }

            return $invoice;
        });
    }
}
