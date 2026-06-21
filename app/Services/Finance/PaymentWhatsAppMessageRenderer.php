<?php

namespace App\Services\Finance;

use App\Models\Payment;
use App\Models\WhatsAppMessageTemplate;

class PaymentWhatsAppMessageRenderer
{
    public function __construct(
        private PaymentWhatsAppMessageSettingsService $settingsService
    ) {}

    public function render(Payment $payment): string
    {
        $settings = $this->settingsService->getSettings();
        $variables = $this->variables($payment);

        if (! empty($settings['whatsapp_template_id'])) {
            $template = WhatsAppMessageTemplate::active()
                ->byType(WhatsAppMessageTemplate::TYPE_TEXT)
                ->find($settings['whatsapp_template_id']);

            if ($template) {
                return $template->render($variables);
            }
        }

        $body = trim((string) ($settings['whatsapp_body'] ?? ''));
        if ($body === '') {
            $body = self::defaultBody();
        }

        return $this->renderTemplate($body, $variables);
    }

    /**
     * @return array<string, string>
     */
    public function variables(Payment $payment): array
    {
        $payment->loadMissing([
            'student',
            'invoice.items.campEnrollment.camp',
            'paymentMethod',
        ]);

        $student = $payment->student;
        $invoice = $payment->invoice;
        $studentName = $student?->name_ar ?? $student?->name ?? 'عزيزي الطالب';

        $campNames = collect($invoice?->items ?? [])
            ->map(fn ($item) => $item->campEnrollment?->camp?->name)
            ->filter()
            ->unique()
            ->values();

        $descriptions = collect($invoice?->items ?? [])
            ->pluck('description')
            ->filter()
            ->unique()
            ->values();

        $paymentForParts = $campNames->isNotEmpty()
            ? $campNames->all()
            : $descriptions->all();

        $paymentFor = $paymentForParts !== []
            ? implode('، ', $paymentForParts)
            : 'رسوم التسجيل';

        $paymentDate = $payment->payment_date
            ? $payment->payment_date->format('Y-m-d H:i')
            : now()->format('Y-m-d H:i');

        return [
            'student_name' => $studentName,
            'user_name' => $studentName,
            'payment_amount' => '$'.number_format((float) $payment->amount, 2),
            'amount_paid' => '$'.number_format((float) $payment->amount, 2),
            'payment_number' => (string) ($payment->payment_number ?? ''),
            'receipt_number' => (string) ($payment->receipt_number ?? '—'),
            'invoice_number' => (string) ($invoice?->invoice_number ?? '—'),
            'payment_date' => $paymentDate,
            'payment_method' => (string) ($payment->paymentMethod?->name ?? '—'),
            'payment_for' => $paymentFor,
            'camp_name' => $campNames->first() ?? $paymentFor,
            'invoice_total' => '$'.number_format((float) ($invoice?->total_amount ?? 0), 2),
            'remaining_amount' => '$'.number_format((float) ($invoice?->remaining_amount ?? 0), 2),
            'paid_total' => '$'.number_format((float) ($invoice?->paid_amount ?? 0), 2),
            'payment_notes' => trim((string) ($payment->notes ?? '')),
            'app_name' => (string) (config('app.name') ?: 'ClaudSoft'),
        ];
    }

    /**
     * @param  array<string, string>  $variables
     */
    private function renderTemplate(string $template, array $variables): string
    {
        $output = $template;
        foreach ($variables as $key => $value) {
            $output = str_replace(
                ['{{'.$key.'}}', '{'.$key.'}'],
                $value,
                $output
            );
        }

        return WhatsAppMessageTemplate::normalizeBodyForSending($output);
    }

    public static function defaultBody(): string
    {
        return <<<'TEXT'
مرحباً {student_name} 👋

تم استلام دفعتك بنجاح ✅

💰 المبلغ المدفوع: {payment_amount}
📄 رقم الدفعة: {payment_number}
🧾 رقم الفاتورة: {invoice_number}
📚 مقابل: {payment_for}
💳 طريقة الدفع: {payment_method}
📅 تاريخ الدفع: {payment_date}

💵 إجمالي المدفوع على الفاتورة: {paid_total}
📊 المتبقي: {remaining_amount}

شكراً لثقتك بنا 🙏
TEXT;
    }
}
