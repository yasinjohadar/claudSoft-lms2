<?php

namespace App\Services\WhatsApp;

use App\Models\EvolutionInstance;
use App\Models\WhatsAppContact;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppMessageTemplate;
use App\Support\WhatsAppRecipientNormalizer;
use InvalidArgumentException;

class WhatsAppTemplateTestSendService
{
    public function __construct(
        private WhatsAppSettingsService $settingsService,
        private WhatsAppOutboundSendService $outboundSendService
    ) {}

    /**
     * @return array<string, string>
     */
    public function sampleReplacements(): array
    {
        return [
            'student_name' => 'طالب تجريبي',
            'student_email' => 'test@example.com',
            'email' => 'test@example.com',
            'course_name' => 'دورة تجريبية',
            'group_name' => 'مجموعة تجريبية',
            'app_name' => config('app.name', 'ClaudSoft'),
            'payment_amount' => '100.00',
            'payment_number' => 'PAY-TEST-001',
            'receipt_number' => 'REC-TEST-001',
            'invoice_number' => 'INV-TEST-001',
            'payment_for' => 'معسكر تجريبي',
            'camp_name' => 'معسكر تجريبي',
            'payment_method' => 'نقداً',
            'payment_date' => now()->format('Y-m-d'),
            'remaining_amount' => '50.00',
            'paid_total' => '100.00',
            'invoice_total' => '150.00',
            'payment_notes' => 'دفعة تجريبية',
            'reset_url' => url('/password/reset/test'),
            'expire_at' => now()->addHour()->format('Y-m-d H:i'),
            'expire_minutes' => '60',
        ];
    }

    public function renderForTest(WhatsAppMessageTemplate $template): string
    {
        if ($template->type === WhatsAppMessageTemplate::TYPE_TEMPLATE) {
            $metaName = $template->meta_template_name ?: $template->name;

            return 'قالب Meta المعتمد: '.$metaName."\n".'اللغة: '.$template->language;
        }

        return $template->render($this->sampleReplacements());
    }

    public function sendTest(
        WhatsAppMessageTemplate $template,
        string $phone,
        ?string $evolutionInstanceName = null
    ): void {
        $phone = $this->normalizePhone($phone);

        if ($template->type === WhatsAppMessageTemplate::TYPE_TEMPLATE) {
            $this->sendMetaTemplateTest($template, $phone, $evolutionInstanceName);

            return;
        }

        $this->sendTextSync($phone, $this->renderForTest($template), $evolutionInstanceName);
    }

    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);

        if ($phone === '') {
            throw new InvalidArgumentException('يرجى إدخال رقم الواتساب.');
        }

        if (! str_starts_with($phone, '+')) {
            $phone = '+'.ltrim($phone, '0');
        }

        return $phone;
    }

    private function sendTextSync(string $to, string $text, ?string $evolutionInstanceName): void
    {
        if ($evolutionInstanceName) {
            $instance = EvolutionInstance::where('instance_name', $evolutionInstanceName)->first();
            if (! $instance) {
                throw new InvalidArgumentException('Instance Evolution غير موجود.');
            }
        }

        $settings = $this->settingsService->getSettings();
        $provider = $settings['whatsapp_provider'] ?? 'meta';
        $normalizedRecipient = WhatsAppRecipientNormalizer::normalize($provider, $to);
        $contact = WhatsAppContact::findOrCreateByWaId($normalizedRecipient);
        $message = WhatsAppMessage::create([
            'direction' => WhatsAppMessage::DIRECTION_OUTBOUND,
            'contact_id' => $contact->id,
            'type' => WhatsAppMessage::TYPE_TEXT,
            'body' => $text,
            'status' => WhatsAppMessage::STATUS_QUEUED,
            'payload' => array_filter([
                'evolution_instance_name' => $evolutionInstanceName,
                'test_send' => true,
            ]),
        ]);

        $this->outboundSendService->send($message, [
            'type' => 'text',
            'text' => $text,
            'preview_url' => false,
            'evolution_instance_name' => $evolutionInstanceName,
        ]);
    }

    private function sendMetaTemplateTest(
        WhatsAppMessageTemplate $template,
        string $phone,
        ?string $evolutionInstanceName
    ): void {
        $metaName = trim((string) ($template->meta_template_name ?? ''));
        if ($metaName === '') {
            throw new InvalidArgumentException('هذا القالب لا يحتوي على اسم Meta المعتمد.');
        }

        if ($evolutionInstanceName) {
            $instance = EvolutionInstance::where('instance_name', $evolutionInstanceName)->first();
            if (! $instance) {
                throw new InvalidArgumentException('Instance Evolution غير موجود.');
            }
        }

        $settings = $this->settingsService->getSettings();
        $provider = $settings['whatsapp_provider'] ?? 'meta';
        $normalizedRecipient = WhatsAppRecipientNormalizer::normalize($provider, $phone);
        $contact = WhatsAppContact::findOrCreateByWaId($normalizedRecipient);
        $message = WhatsAppMessage::create([
            'direction' => WhatsAppMessage::DIRECTION_OUTBOUND,
            'contact_id' => $contact->id,
            'type' => WhatsAppMessage::TYPE_TEMPLATE,
            'body' => $metaName,
            'status' => WhatsAppMessage::STATUS_QUEUED,
            'payload' => array_filter([
                'template_name' => $metaName,
                'language' => $template->language,
                'components' => [],
                'test_send' => true,
                'evolution_instance_name' => $evolutionInstanceName,
            ]),
        ]);

        $this->outboundSendService->send($message, [
            'type' => 'template',
            'template_name' => $metaName,
            'language' => $template->language,
            'components' => [],
            'evolution_instance_name' => $evolutionInstanceName,
        ]);
    }
}
