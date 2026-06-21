<?php

namespace App\Services\Finance;

use App\Models\Payment;
use App\Models\User;
use App\Models\WapiTemplate;
use App\Models\WhatsAppMessageTemplate;
use App\Services\WapiOutboundDispatcher;
use App\Services\WhatsApp\SendWhatsAppMessage;
use App\Services\WhatsAppService;
use App\Support\WhatsAppSendErrorMessage;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class PaymentWhatsAppNotifyService
{
    public function __construct(
        private PaymentWhatsAppMessageSettingsService $settingsService,
        private PaymentWhatsAppMessageRenderer $renderer,
        private SendWhatsAppMessage $sendWhatsAppMessage,
        private WapiOutboundDispatcher $wapiDispatcher,
        private WhatsAppService $whatsAppService
    ) {}

    public function notify(Payment $payment): bool
    {
        $settings = $this->settingsService->getSettings();
        if (! filter_var($settings['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        if ($payment->status !== 'completed') {
            return false;
        }

        try {
            $payment->loadMissing([
                'student',
                'invoice.items.campEnrollment.camp',
                'paymentMethod',
            ]);

            $student = $payment->student;
            if (! $student instanceof User) {
                return false;
            }

            $phone = $this->resolvePhone($student);
            $deliveryMode = (string) ($settings['delivery_mode'] ?? 'evolution_text');

            if ($deliveryMode === 'flaxxa_template') {
                $this->notifyViaFlaxxaTemplate($payment, $phone, $settings);

                return true;
            }

            $body = $this->renderer->render($payment);
            $this->sendWhatsAppMessage->sendTextSync($phone, $body);

            return true;
        } catch (InvalidArgumentException $e) {
            Log::channel('whatsapp')->info('Payment WhatsApp skipped', [
                'payment_id' => $payment->id,
                'reason' => $e->getMessage(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::channel('whatsapp')->warning('Payment WhatsApp failed', [
                'payment_id' => $payment->id,
                'error' => WhatsAppSendErrorMessage::fromThrowable($e),
            ]);

            return false;
        }
    }

    private function resolvePhone(User $student): string
    {
        $phone = trim((string) ($student->full_phone ?? ''));
        if ($phone === '') {
            $phone = trim(($student->country_code ?? '').($student->phone ?? ''));
        }

        if ($phone === '') {
            throw new InvalidArgumentException('لا يوجد رقم واتساب للطالب.');
        }

        if (! str_starts_with($phone, '+')) {
            $phone = '+'.ltrim($phone, '0');
        }

        return $phone;
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    private function notifyViaFlaxxaTemplate(Payment $payment, string $phone, array $settings): void
    {
        $this->whatsAppService->assertConfigured();

        $templateId = $settings['wapi_template_id'] ?? null;
        $template = $templateId ? WapiTemplate::query()->find($templateId) : null;

        if (! $template) {
            throw new InvalidArgumentException('لم يُحدد قالب Flaxxa لإشعار الدفع.');
        }

        $body = $this->renderer->render($payment);
        $lines = array_values(array_filter(preg_split('/\r\n|\r|\n/', $body) ?: [], fn ($l) => trim($l) !== ''));

        $components = [
            [
                'type' => 'body',
                'parameters' => array_map(
                    fn ($line) => ['type' => 'text', 'text' => mb_substr(trim($line), 0, 1024)],
                    $lines !== [] ? $lines : [$body]
                ),
            ],
        ];

        $this->wapiDispatcher->queueTemplate(
            phone: $phone,
            templateName: $template->name,
            language: $template->language ?? 'ar',
            components: $components,
            attachmentStoragePath: null,
            wapiTemplateId: $template->id,
            variablesLog: ['payment_id' => $payment->id, 'delivery_mode' => 'flaxxa_template'],
        );
    }
}
