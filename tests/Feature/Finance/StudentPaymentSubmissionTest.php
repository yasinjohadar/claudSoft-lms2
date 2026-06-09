<?php

namespace Tests\Feature\Finance;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\Finance\StudentPaymentSubmissionService;
use App\Services\Storage\StorageHelperService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentPaymentSubmissionTest extends TestCase
{
    use DatabaseTransactions;

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql.database', 'claudsoft_platform');
    }

    private function mockReceiptUpload(string $path = 'payments/receipts/2026/1/receipt.jpg'): void
    {
        $this->mock(StorageHelperService::class, function ($mock) use ($path) {
            $mock->shouldReceive('storeUploadedFile')
                ->andReturn($path);
        });
    }

    private function studentUser(): User
    {
        $role = Role::findOrCreate('student', 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function adminUser(): User
    {
        $role = Role::findOrCreate('admin', 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function paymentMethod(): PaymentMethod
    {
        return PaymentMethod::query()->create([
            'name' => 'تحويل بنكي',
            'name_en' => 'Bank Transfer',
            'description' => null,
            'is_active' => true,
            'order' => 1,
        ]);
    }

    private function invoiceFor(User $student, float $total = 100, float $paid = 0): Invoice
    {
        return Invoice::query()->create([
            'invoice_number' => 'INV-TEST-' . uniqid(),
            'student_id' => $student->id,
            'total_amount' => $total,
            'paid_amount' => $paid,
            'remaining_amount' => $total - $paid,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'status' => $paid > 0 ? 'partial' : 'issued',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'notes' => null,
            'created_by' => null,
        ]);
    }

    public function test_student_can_submit_valid_payment_without_updating_invoice(): void
    {
        $this->mockReceiptUpload();

        $student = $this->studentUser();
        $method = $this->paymentMethod();
        $invoice = $this->invoiceFor($student);

        $response = $this->actingAs($student)->post(route('student.invoices.pay', $invoice->id), [
            'amount' => 50,
            'payment_date' => now()->toDateString(),
            'payment_method_id' => $method->id,
            'receipt' => UploadedFile::fake()->image('receipt.jpg'),
            'notes' => 'تحويل بنكي',
        ]);

        $response->assertRedirect(route('student.invoices.show', $invoice->id));
        $response->assertSessionHas('success');

        $invoice->refresh();
        $this->assertEquals(0, (float) $invoice->paid_amount);
        $this->assertEquals(100, (float) $invoice->remaining_amount);

        $payment = Payment::query()->where('invoice_id', $invoice->id)->first();
        $this->assertNotNull($payment);
        $this->assertSame('pending', $payment->status);
        $this->assertNotNull($payment->receipt_path);
        $this->assertSame('public', $payment->receipt_disk);
    }

    public function test_student_cannot_submit_when_pending_payment_exists(): void
    {
        $this->mockReceiptUpload('payments/receipts/2026/1/receipt2.jpg');

        $student = $this->studentUser();
        $method = $this->paymentMethod();
        $invoice = $this->invoiceFor($student);

        Payment::query()->create([
            'payment_number' => 'PAY-PENDING-001',
            'invoice_id' => $invoice->id,
            'student_id' => $student->id,
            'amount' => 30,
            'payment_method_id' => $method->id,
            'payment_date' => now(),
            'status' => 'pending',
            'receipt_path' => 'payments/receipts/test.jpg',
            'receipt_disk' => 'public',
        ]);

        $response = $this->actingAs($student)->post(route('student.invoices.pay', $invoice->id), [
            'amount' => 20,
            'payment_date' => now()->toDateString(),
            'payment_method_id' => $method->id,
            'receipt' => UploadedFile::fake()->image('receipt2.jpg'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertSame(1, Payment::query()->where('invoice_id', $invoice->id)->count());
    }

    public function test_student_cannot_submit_amount_greater_than_remaining(): void
    {
        $this->mockReceiptUpload();

        $student = $this->studentUser();
        $method = $this->paymentMethod();
        $invoice = $this->invoiceFor($student);

        $response = $this->actingAs($student)->post(route('student.invoices.pay', $invoice->id), [
            'amount' => 150,
            'payment_date' => now()->toDateString(),
            'payment_method_id' => $method->id,
            'receipt' => UploadedFile::fake()->image('receipt.jpg'),
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertSame(0, Payment::query()->count());
    }

    public function test_admin_can_approve_pending_payment_and_update_invoice(): void
    {
        $student = $this->studentUser();
        $admin = $this->adminUser();
        $method = $this->paymentMethod();
        $invoice = $this->invoiceFor($student);

        $payment = Payment::query()->create([
            'payment_number' => 'PAY-PENDING-002',
            'invoice_id' => $invoice->id,
            'student_id' => $student->id,
            'amount' => 40,
            'payment_method_id' => $method->id,
            'payment_date' => now(),
            'status' => 'pending',
            'receipt_path' => 'payments/receipts/test.jpg',
            'receipt_disk' => 'public',
        ]);

        $response = $this->actingAs($admin)->post(route('payments.approve', $payment->id));

        $response->assertRedirect(route('payments.show', $payment->id));
        $response->assertSessionHas('success');

        $payment->refresh();
        $invoice->refresh();

        $this->assertSame('completed', $payment->status);
        $this->assertNotNull($payment->receipt_number);
        $this->assertSame($admin->id, $payment->reviewed_by);
        $this->assertEquals(40, (float) $invoice->paid_amount);
        $this->assertEquals(60, (float) $invoice->remaining_amount);
        $this->assertSame('partial', $invoice->status);
    }

    public function test_admin_can_reject_pending_payment_without_updating_invoice(): void
    {
        $student = $this->studentUser();
        $admin = $this->adminUser();
        $method = $this->paymentMethod();
        $invoice = $this->invoiceFor($student);

        $payment = Payment::query()->create([
            'payment_number' => 'PAY-PENDING-003',
            'invoice_id' => $invoice->id,
            'student_id' => $student->id,
            'amount' => 25,
            'payment_method_id' => $method->id,
            'payment_date' => now(),
            'status' => 'pending',
            'receipt_path' => 'payments/receipts/test.jpg',
            'receipt_disk' => 'public',
        ]);

        $response = $this->actingAs($admin)->post(route('payments.reject', $payment->id), [
            'rejection_reason' => 'الإيصال غير واضح',
        ]);

        $response->assertRedirect(route('payments.show', $payment->id));
        $response->assertSessionHas('success');

        $payment->refresh();
        $invoice->refresh();

        $this->assertSame('failed', $payment->status);
        $this->assertSame('الإيصال غير واضح', $payment->rejection_reason);
        $this->assertEquals(0, (float) $invoice->paid_amount);
    }

    public function test_student_cannot_download_another_students_receipt(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('payments/receipts/test.jpg', 'fake-content');

        $owner = $this->studentUser();
        $other = $this->studentUser();
        $method = $this->paymentMethod();
        $invoice = $this->invoiceFor($owner);

        $payment = Payment::query()->create([
            'payment_number' => 'PAY-PRIVATE-001',
            'invoice_id' => $invoice->id,
            'student_id' => $owner->id,
            'amount' => 10,
            'payment_method_id' => $method->id,
            'payment_date' => now(),
            'status' => 'pending',
            'receipt_path' => 'payments/receipts/test.jpg',
            'receipt_disk' => 'public',
        ]);

        $this->actingAs($other)
            ->get(route('student.payments.receipt', $payment->id))
            ->assertNotFound();
    }

    public function test_submit_succeeds_when_student_id_is_string_from_database(): void
    {
        $this->mockReceiptUpload();

        $student = $this->studentUser();
        $method = $this->paymentMethod();
        $invoice = $this->invoiceFor($student);
        $invoice->setRawAttributes(array_merge($invoice->getAttributes(), [
            'student_id' => (string) $student->id,
        ]));

        $payment = app(StudentPaymentSubmissionService::class)->submit(
            $invoice,
            $student,
            [
                'amount' => 50,
                'payment_date' => now()->toDateString(),
                'payment_method_id' => $method->id,
                'notes' => null,
            ],
            UploadedFile::fake()->image('receipt.jpg')
        );

        $this->assertSame('pending', $payment->status);
        $this->assertSame($invoice->id, $payment->invoice_id);
    }

    public function test_service_approve_throws_for_non_pending_payment(): void
    {
        $student = $this->studentUser();
        $admin = $this->adminUser();
        $method = $this->paymentMethod();
        $invoice = $this->invoiceFor($student);

        $payment = Payment::query()->create([
            'payment_number' => 'PAY-COMPLETED-001',
            'invoice_id' => $invoice->id,
            'student_id' => $student->id,
            'amount' => 10,
            'payment_method_id' => $method->id,
            'payment_date' => now(),
            'status' => 'completed',
        ]);

        $this->expectException(\InvalidArgumentException::class);

        app(StudentPaymentSubmissionService::class)->approve($payment, $admin);
    }
}
