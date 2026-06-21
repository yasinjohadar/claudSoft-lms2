<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\WapiTemplate;
use App\Models\WhatsAppMessageTemplate;
use App\Services\Finance\PaymentWhatsAppMessageRenderer;
use App\Services\Finance\PaymentWhatsAppMessageSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentWhatsAppMessageSettingsController extends Controller
{
    public function __construct(
        private PaymentWhatsAppMessageSettingsService $settingsService,
        private PaymentWhatsAppMessageRenderer $renderer
    ) {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function edit(): View
    {
        $settings = $this->settingsService->getSettings();
        $whatsappTemplates = WhatsAppMessageTemplate::active()
            ->byType(WhatsAppMessageTemplate::TYPE_TEXT)
            ->orderBy('name')
            ->get(['id', 'name']);

        $wapiTemplates = WapiTemplate::query()->orderBy('name')->get(['id', 'name', 'language']);

        $samplePayment = new Payment([
            'payment_number' => 'PAY-2026-00001',
            'receipt_number' => 'RCP-2026-00001',
            'amount' => 150.00,
            'payment_date' => now(),
            'notes' => '',
        ]);
        $samplePayment->setRelation('student', new \App\Models\User([
            'name' => 'أحمد محمد',
            'name_ar' => 'أحمد محمد',
        ]));
        $samplePayment->setRelation('paymentMethod', new \App\Models\PaymentMethod(['name' => 'تحويل بنكي']));
        $samplePayment->setRelation('invoice', new \App\Models\Invoice([
            'invoice_number' => 'INV-2026-00001',
            'total_amount' => 500,
            'paid_amount' => 150,
            'remaining_amount' => 350,
        ]));

        $placeholders = array_keys($this->renderer->variables($samplePayment));

        return view('admin.pages.settings.payment-whatsapp-message.edit', compact(
            'settings',
            'whatsappTemplates',
            'wapiTemplates',
            'placeholders'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'whatsapp_template_id' => 'nullable|exists:whatsapp_message_templates,id',
            'whatsapp_body' => 'nullable|string|max:10000',
            'delivery_mode' => 'nullable|in:evolution_text,flaxxa_template',
            'wapi_template_id' => 'nullable|exists:wapi_templates,id',
        ]);

        $this->settingsService->updateSettings([
            'enabled' => $request->boolean('enabled'),
            'whatsapp_template_id' => $validated['whatsapp_template_id'] ?? '',
            'whatsapp_body' => $validated['whatsapp_body'] ?? '',
            'delivery_mode' => $validated['delivery_mode'] ?? 'evolution_text',
            'wapi_template_id' => $validated['wapi_template_id'] ?? '',
        ]);

        return redirect()
            ->route('admin.settings.payment-whatsapp-message.edit')
            ->with('success', 'تم حفظ إعدادات إشعار الدفع عبر واتساب.');
    }

    public function restoreDefaults(): RedirectResponse
    {
        $this->settingsService->restoreDefaults();

        return redirect()
            ->route('admin.settings.payment-whatsapp-message.edit')
            ->with('success', 'تمت استعادة الرسالة الافتراضية.');
    }
}
