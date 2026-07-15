<?php

namespace Tests\Feature\Admin;

use App\Models\GroupRegistration;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GroupRegistrationReceiptDisplayTest extends TestCase
{
    public function test_receipt_route_is_protected_by_a_temporary_signature(): void
    {
        $route = app('router')->getRoutes()->getByName('admin.group-registrations.receipt');

        $this->assertNotNull($route);
        $this->assertContains('signed', $route->middleware());
    }

    public function test_membership_request_form_data_displays_an_image_receipt_preview(): void
    {
        $registration = $this->registrationWithReceipt('receipt.jpg');

        $html = view(
            'admin.course-groups.partials.membership-request-form-data',
            compact('registration')
        )->render();

        $this->assertStringContainsString('وصل الانتساب', $html);
        $this->assertStringContainsString('تنزيل الوصل', $html);
        $this->assertStringContainsString('<img', $html);
        $this->assertStringContainsString('/group-registrations/1054/receipt', $html);
    }

    public function test_membership_request_form_data_embeds_a_pdf_receipt(): void
    {
        $registration = $this->registrationWithReceipt('receipt.pdf');

        $html = view(
            'admin.course-groups.partials.membership-request-form-data',
            compact('registration')
        )->render();

        $this->assertStringContainsString('type="application/pdf"', $html);
        $this->assertStringContainsString('فتح بالحجم الكامل', $html);
    }

    public function test_membership_request_form_data_shows_empty_state_when_receipt_missing(): void
    {
        $registration = $this->registrationWithReceipt('receipt.jpg');
        $registration->membership_receipt_path = null;

        $html = view(
            'admin.course-groups.partials.membership-request-form-data',
            compact('registration')
        )->render();

        $this->assertStringContainsString('وصل الانتساب', $html);
        $this->assertStringContainsString('لم يُرفع وصل انتساب لهذا الطلب', $html);
        $this->assertStringNotContainsString('<img', $html);
    }

    private function registrationWithReceipt(string $filename): GroupRegistration
    {
        $registration = new GroupRegistration([
            'membership_receipt_path' => "group-registrations/payment-receipts/2026/37/{$filename}",
            'membership_receipt_disk' => 'payment_receipts',
            'status' => GroupRegistration::STATUS_COMPLETED,
            'user_created' => true,
        ]);
        $registration->id = 1054;
        $registration->created_at = Carbon::parse('2026-07-15 12:00:00');
        $registration->setRelation('nationality', null);

        return $registration;
    }
}
