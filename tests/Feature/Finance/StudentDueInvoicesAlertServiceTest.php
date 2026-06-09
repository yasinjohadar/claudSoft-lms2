<?php

namespace Tests\Feature\Finance;

use App\Models\Invoice;
use App\Models\User;
use App\Services\Finance\StudentDueInvoicesAlertService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StudentDueInvoicesAlertServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function createApplication()
    {
        $app = parent::createApplication();

        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql.database', 'claudsoft_platform');

        return $app;
    }

    private function invoiceFor(User $student, string $status, float $total, float $paid): Invoice
    {
        return Invoice::query()->create([
            'invoice_number' => 'INV-ALERT-' . uniqid(),
            'student_id' => $student->id,
            'total_amount' => $total,
            'paid_amount' => $paid,
            'remaining_amount' => $total - $paid,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'status' => $status,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'notes' => null,
            'created_by' => null,
        ]);
    }

    public function test_returns_null_when_student_has_no_unpaid_invoices(): void
    {
        $student = User::factory()->create();
        $service = app(StudentDueInvoicesAlertService::class);

        $this->assertNull($service->forUser($student));
    }

    public function test_returns_summary_for_unpaid_invoices(): void
    {
        $student = User::factory()->create();
        $other = User::factory()->create();

        $this->invoiceFor($student, 'issued', 100, 0);
        $this->invoiceFor($student, 'partial', 200, 50);
        $this->invoiceFor($other, 'issued', 500, 0);
        $this->invoiceFor($student, 'paid', 80, 80);

        $alert = app(StudentDueInvoicesAlertService::class)->forUser($student);

        $this->assertNotNull($alert);
        $this->assertSame(2, $alert['count']);
        $this->assertSame(250.0, $alert['total_remaining']);
        $this->assertStringContainsString((string) $student->id, $alert['dismiss_key']);
    }
}
