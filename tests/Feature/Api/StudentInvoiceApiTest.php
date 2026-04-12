<?php

namespace Tests\Feature\Api;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentInvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    private function studentUser(): User
    {
        $role = Role::findOrCreate('student', 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_guest_cannot_list_invoices(): void
    {
        $this->getJson('/api/student/invoices')->assertUnauthorized();
        $this->getJson('/api/student/payments')->assertUnauthorized();
    }

    public function test_non_student_cannot_access_invoice_endpoints(): void
    {
        $role = Role::findOrCreate('admin', 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        Sanctum::actingAs($user);

        $this->getJson('/api/student/invoices')->assertForbidden();
        $this->getJson('/api/student/payments')->assertForbidden();
    }

    public function test_student_can_list_invoices_and_payments_with_expected_shape(): void
    {
        $student = $this->studentUser();

        $method = PaymentMethod::query()->create([
            'name' => 'نقدي',
            'name_en' => 'Cash',
            'description' => null,
            'is_active' => true,
            'requires_transaction_id' => false,
            'order' => 0,
        ]);

        $invoice = Invoice::query()->create([
            'invoice_number' => 'INV-TEST-00001',
            'student_id' => $student->id,
            'total_amount' => 100,
            'paid_amount' => 50,
            'remaining_amount' => 50,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'status' => 'partial',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'notes' => null,
            'created_by' => null,
        ]);

        InvoiceItem::query()->create([
            'invoice_id' => $invoice->id,
            'itemable_type' => null,
            'itemable_id' => null,
            'description' => 'بند تجريبي',
            'quantity' => 1,
            'unit_price' => 100,
            'total_price' => 100,
            'camp_enrollment_id' => null,
        ]);

        $payment = Payment::query()->create([
            'payment_number' => 'PAY-TEST-00001',
            'invoice_id' => $invoice->id,
            'student_id' => $student->id,
            'amount' => 50,
            'payment_method_id' => $method->id,
            'payment_date' => now(),
            'status' => 'completed',
        ]);

        Sanctum::actingAs($student);

        $this->getJson('/api/student/invoices')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'stats' => [
                        'total_invoices',
                        'total_amount',
                        'paid_amount',
                        'remaining_amount',
                        'overdue_count',
                    ],
                    'invoices',
                    'pagination' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                    ],
                ],
            ])
            ->assertJsonPath('data.invoices.0.id', $invoice->id)
            ->assertJsonPath('data.invoices.0.invoice_number', 'INV-TEST-00001')
            ->assertJsonPath('data.invoices.0.items.0.description', 'بند تجريبي');

        $this->getJson('/api/student/invoices/'.$invoice->id)
            ->assertOk()
            ->assertJsonPath('data.invoice.id', $invoice->id)
            ->assertJsonPath('data.invoice.payments.0.payment_number', 'PAY-TEST-00001');

        $this->getJson('/api/student/payments')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'stats' => ['total_payments', 'total_paid'],
                    'payments',
                    'pagination',
                ],
            ])
            ->assertJsonPath('data.payments.0.id', $payment->id);

        $this->getJson('/api/student/payments/'.$payment->id)
            ->assertOk()
            ->assertJsonPath('data.payment.id', $payment->id)
            ->assertJsonPath('data.payment.invoice.invoice_number', 'INV-TEST-00001');
    }

    public function test_student_cannot_view_another_students_invoice_or_payment(): void
    {
        $studentA = $this->studentUser();
        $studentB = $this->studentUser();

        $method = PaymentMethod::query()->create([
            'name' => 'نقدي',
            'name_en' => 'Cash',
            'description' => null,
            'is_active' => true,
            'requires_transaction_id' => false,
            'order' => 0,
        ]);

        $invoice = Invoice::query()->create([
            'invoice_number' => 'INV-TEST-B-00001',
            'student_id' => $studentB->id,
            'total_amount' => 200,
            'paid_amount' => 0,
            'remaining_amount' => 200,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'status' => 'issued',
            'issue_date' => now()->toDateString(),
            'due_date' => null,
            'notes' => null,
            'created_by' => null,
        ]);

        $payment = Payment::query()->create([
            'payment_number' => 'PAY-TEST-B-00001',
            'invoice_id' => $invoice->id,
            'student_id' => $studentB->id,
            'amount' => 200,
            'payment_method_id' => $method->id,
            'payment_date' => now(),
            'status' => 'completed',
        ]);

        Sanctum::actingAs($studentA);

        $this->getJson('/api/student/invoices/'.$invoice->id)->assertNotFound();
        $this->getJson('/api/student/payments/'.$payment->id)->assertNotFound();
    }
}
