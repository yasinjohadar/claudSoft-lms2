<?php

namespace Tests\Feature\Api\Mobile\V1;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class MobileFinanceApiTest extends MobileApiMysqlTestCase
{
    private function invoice(User $student, array $attrs): Invoice
    {
        $invoice = Invoice::create(array_merge([
            'invoice_number' => 'TST-'.Str::upper(Str::random(10)),
            'student_id' => $student->id,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'issue_date' => now()->subDays(10),
            'due_date' => now()->addDays(10),
        ], $attrs));

        $invoice->items()->create([
            'description' => 'Course fee',
            'quantity' => 1,
            'unit_price' => $attrs['total_amount'],
            'total_price' => $attrs['total_amount'],
        ]);

        return $invoice;
    }

    private function payment(User $student, Invoice $invoice, float $amount, string $status): Payment
    {
        return Payment::create([
            'payment_number' => 'PTST-'.Str::upper(Str::random(10)),
            'invoice_id' => $invoice->id,
            'student_id' => $student->id,
            'amount' => $amount,
            'payment_method_id' => \App\Models\PaymentMethod::query()->value('id') ?? $this->markTestSkipped('No payment methods.'),
            'payment_date' => now(),
            'status' => $status,
        ]);
    }

    public function test_summary_totals_exclude_cancelled_invoices(): void
    {
        $student = $this->completeStudent();
        $this->invoice($student, ['total_amount' => 100, 'paid_amount' => 100, 'remaining_amount' => 0, 'status' => 'paid']);
        $partial = $this->invoice($student, ['total_amount' => 200, 'paid_amount' => 50, 'remaining_amount' => 150, 'status' => 'partial']);
        $this->invoice($student, ['total_amount' => 80, 'paid_amount' => 0, 'remaining_amount' => 80, 'status' => 'issued', 'due_date' => now()->subDay()]);
        $this->invoice($student, ['total_amount' => 999, 'paid_amount' => 0, 'remaining_amount' => 999, 'status' => 'cancelled']);
        $this->payment($student, $partial, 25, 'pending');
        Sanctum::actingAs($student);

        $this->getJson('/api/mobile/v1/finance/summary')
            ->assertOk()
            ->assertJsonPath('data.total_amount', 380)
            ->assertJsonPath('data.paid_amount', 150)
            ->assertJsonPath('data.remaining_amount', 230)
            ->assertJsonPath('data.invoices.total', 3)
            ->assertJsonPath('data.invoices.unpaid', 2)
            ->assertJsonPath('data.invoices.overdue', 1)
            ->assertJsonPath('data.pending_payments.count', 1)
            ->assertJsonPath('data.next_due.is_overdue', true)
            ->assertJsonPath('data.currency.symbol', '$');
    }

    public function test_invoice_list_filters_and_detail(): void
    {
        $student = $this->completeStudent();
        $paid = $this->invoice($student, ['total_amount' => 100, 'paid_amount' => 100, 'remaining_amount' => 0, 'status' => 'paid']);
        $this->invoice($student, ['total_amount' => 60, 'paid_amount' => 0, 'remaining_amount' => 60, 'status' => 'issued']);
        $this->payment($student, $paid, 100, 'completed');
        Sanctum::actingAs($student);

        $this->getJson('/api/mobile/v1/invoices')->assertOk()->assertJsonPath('meta.total', 2);
        $this->getJson('/api/mobile/v1/invoices?status=unpaid')->assertOk()->assertJsonPath('meta.total', 1);
        $this->getJson('/api/mobile/v1/invoices?status=paid')
            ->assertOk()
            ->assertJsonPath('data.0.id', $paid->id)
            ->assertJsonPath('data.0.paid_percentage', 100);

        $this->getJson("/api/mobile/v1/invoices/{$paid->id}")
            ->assertOk()
            ->assertJsonPath('data.items.0.description', 'Course fee')
            ->assertJsonPath('data.payments.0.amount', 100);

        $this->getJson('/api/mobile/v1/payments?status=completed')->assertOk()->assertJsonPath('meta.total', 1);
    }

    public function test_student_cannot_see_another_students_invoice(): void
    {
        $owner = $this->completeStudent();
        $other = $this->completeStudent();
        $invoice = $this->invoice($owner, ['total_amount' => 10, 'paid_amount' => 0, 'remaining_amount' => 10, 'status' => 'issued']);
        Sanctum::actingAs($other);

        $this->getJson("/api/mobile/v1/invoices/{$invoice->id}")->assertNotFound();
        $this->getJson('/api/mobile/v1/invoices')->assertOk()->assertJsonPath('meta.total', 0);
    }
}
