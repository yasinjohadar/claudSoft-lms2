<?php

namespace Tests\Feature\Admin;

use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentProfilePaymentTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $role = Role::findOrCreate('admin', 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function studentUser(): User
    {
        $role = Role::findOrCreate('student', 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function paymentMethod(): PaymentMethod
    {
        return PaymentMethod::create([
            'name' => 'نقدي',
            'is_active' => true,
            'order' => 1,
        ]);
    }

    private function issuedInvoice(User $student, float $total = 100.00): Invoice
    {
        return Invoice::create([
            'invoice_number' => 'INV-TEST-'.uniqid(),
            'student_id' => $student->id,
            'total_amount' => $total,
            'paid_amount' => 0,
            'remaining_amount' => $total,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'status' => 'issued',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
        ]);
    }

    public function test_ajax_record_payment_updates_invoice_and_creates_payment(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();
        $method = $this->paymentMethod();
        $invoice = $this->issuedInvoice($student);

        $response = $this->actingAs($admin)->postJson(route('users.record-payment', $student->id), [
            'invoice_id' => $invoice->id,
            'amount' => 50,
            'payment_method_id' => $method->id,
            'payment_date' => now()->toDateString(),
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'message',
                'billing_stats' => ['total_invoices', 'total_amount', 'total_paid', 'remaining_amount'],
                'invoice_row_html',
                'payment_row_html',
                'invoice_id',
                'payment_id',
            ]);

        $invoice->refresh();
        $this->assertEquals(50.0, (float) $invoice->paid_amount);
        $this->assertEquals(50.0, (float) $invoice->remaining_amount);
        $this->assertEquals('partial', $invoice->status);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'student_id' => $student->id,
            'amount' => 50,
            'status' => 'completed',
        ]);
    }

    public function test_ajax_record_payment_rejects_amount_greater_than_remaining(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();
        $method = $this->paymentMethod();
        $invoice = $this->issuedInvoice($student, 100);

        $response = $this->actingAs($admin)->postJson(route('users.record-payment', $student->id), [
            'invoice_id' => $invoice->id,
            'amount' => 150,
            'payment_method_id' => $method->id,
            'payment_date' => now()->toDateString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_ajax_record_payment_rejects_invoice_for_another_student(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();
        $otherStudent = $this->studentUser();
        $method = $this->paymentMethod();
        $invoice = $this->issuedInvoice($otherStudent);

        $response = $this->actingAs($admin)->postJson(route('users.record-payment', $student->id), [
            'invoice_id' => $invoice->id,
            'amount' => 50,
            'payment_method_id' => $method->id,
            'payment_date' => now()->toDateString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'الفاتورة لا تتبع هذا الطالب.');
    }

    public function test_ajax_record_payment_rejects_fully_paid_invoice(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();
        $method = $this->paymentMethod();
        $invoice = $this->issuedInvoice($student);
        $invoice->update([
            'status' => 'paid',
            'paid_amount' => 100,
            'remaining_amount' => 0,
        ]);

        $response = $this->actingAs($admin)->postJson(route('users.record-payment', $student->id), [
            'invoice_id' => $invoice->id,
            'amount' => 10,
            'payment_method_id' => $method->id,
            'payment_date' => now()->toDateString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'لا يمكن تسديد هذه الفاتورة.');
    }
}
