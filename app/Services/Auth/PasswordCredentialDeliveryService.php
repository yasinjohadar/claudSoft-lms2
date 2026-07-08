<?php

namespace App\Services\Auth;

use App\Mail\PasswordCredentialsMail;
use App\Models\EmailSetting;
use App\Models\User;
use App\Services\WhatsApp\SendWhatsAppMessage;
use App\Services\WhatsApp\WhatsAppSettingsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordCredentialDeliveryService
{
    public const CONTEXT_FORGOT_AUTO = 'forgot_auto';

    public const CONTEXT_FORGOT_MANUAL = 'forgot_manual';

    public const CONTEXT_ADMIN_RESET = 'admin_reset';

    public function __construct(
        private PasswordResetMessageRenderer $renderer,
        private WhatsAppSettingsService $whatsappSettings,
        private SendWhatsAppMessage $whatsAppSender,
    ) {}

    public function generateSecurePassword(): string
    {
        return Str::password(16, symbols: true);
    }

    /**
     * @return array{email_sent: bool, whatsapp_sent: bool, whatsapp_recipient: ?string}
     */
    public function deliver(
        User $user,
        #[\SensitiveParameter] string $plainPassword,
        string $context,
        ?string $whatsappRecipientOverride = null,
    ): array {
        $emailSent = false;
        $whatsappSent = false;
        $whatsappRecipient = null;

        try {
            $this->sendEmail($user, $plainPassword);
            $emailSent = true;
        } catch (\Throwable $e) {
            Log::warning('Password credential email failed', [
                'user_id' => $user->id,
                'context' => $context,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $recipient = $whatsappRecipientOverride ?? $this->resolveWhatsAppRecipient($user);
            $whatsappRecipient = $recipient;
            if ($recipient !== null && $this->isWhatsAppAvailable()) {
                $message = $this->renderer->renderCredentialWhatsApp($user, $plainPassword);
                $waSettings = $this->whatsappSettings->getSettings();
                $instance = trim((string) (
                    $waSettings['auto_reply_evolution_instance']
                    ?? $waSettings['evolution_instance_name']
                    ?? ''
                ));

                $sentMessage = $this->whatsAppSender->sendTextSync(
                    $recipient,
                    $message,
                    previewUrl: false,
                    applySendDelay: false,
                    evolutionInstanceName: $instance !== '' ? $instance : null,
                );
                if ($sentMessage->status === \App\Models\WhatsAppMessage::STATUS_FAILED) {
                    throw new \RuntimeException('فشل إرسال رسالة الواتساب.');
                }
                $whatsappSent = true;
            }
        } catch (\Throwable $e) {
            Log::warning('Password credential WhatsApp failed', [
                'user_id' => $user->id,
                'context' => $context,
                'recipient' => $whatsappRecipient,
                'error' => $e->getMessage(),
            ]);
        }

        Log::info('Password credentials delivered', [
            'user_id' => $user->id,
            'context' => $context,
            'email_sent' => $emailSent,
            'whatsapp_sent' => $whatsappSent,
            'whatsapp_recipient' => $whatsappRecipient,
        ]);

        return [
            'email_sent' => $emailSent,
            'whatsapp_sent' => $whatsappSent,
            'whatsapp_recipient' => $whatsappRecipient,
        ];
    }

    private function sendEmail(User $user, #[\SensitiveParameter] string $plainPassword): void
    {
        if (empty($user->email)) {
            throw new \InvalidArgumentException('لا يوجد بريد إلكتروني مسجّل لهذا الحساب.');
        }

        $this->applyActiveMailSettings();

        Mail::to($user->email)->send(new PasswordCredentialsMail($user, $plainPassword));
    }

    private function applyActiveMailSettings(): void
    {
        $setting = EmailSetting::getActive();

        if ($setting) {
            $setting->applyToConfig();
        }
    }

    public function isWhatsAppAvailable(): bool
    {
        $settings = $this->whatsappSettings->getSettings();

        return ($settings['whatsapp_enabled'] ?? false)
            && in_array($settings['whatsapp_provider'] ?? '', ['evolution', 'whatsapp_web', 'custom_api'], true);
    }

    private function resolveWhatsAppRecipient(User $user): ?string
    {
        $phone = $user->full_phone
            ?? trim(($user->country_code ?? '').($user->phone ?? ''))
            ?: $user->phone;

        $phone = preg_replace('/\s+/', '', (string) $phone);
        if ($phone === '') {
            return null;
        }

        if (! str_starts_with($phone, '+')) {
            $phone = '+'.ltrim($phone, '0');
        }

        return $phone;
    }
}
