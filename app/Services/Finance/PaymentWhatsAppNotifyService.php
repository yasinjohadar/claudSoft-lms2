<?php

namespace App\Services\Finance;

use App\Models\Payment;
use App\Models\User;
use App\Models\WhatsAppMessageTemplate;
use App\Services\WhatsApp\SendWhatsAppMessage;
use App\Support\WhatsAppSendErrorMessage;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class PaymentWhatsAppNotifyService
{
    public function __construct(
        private PaymentWhatsAppMessageSettingsService $settingsService,
        private PaymentWhatsAppMessageRenderer $renderer,
        private SendWhatsAppMessage $sendWhatsAppMessage
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
}
