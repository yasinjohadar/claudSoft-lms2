<?php

namespace App\Services\Auth;

use App\Mail\AccountCreatedCredentialsMail;
use App\Models\EmailSetting;
use App\Models\EvolutionInstance;
use App\Models\User;
use App\Support\InternationalPhoneDigits;
use App\Services\WhatsApp\Evolution\EvolutionInstanceRotator;
use App\Services\WhatsApp\Evolution\EvolutionWhatsAppNumberResolver;
use App\Services\WhatsApp\SendWhatsAppMessage;
use App\Services\WhatsApp\WhatsAppDeliveryAcceptance;
use App\Services\WhatsApp\WhatsAppSettingsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AccountCreatedCredentialDeliveryService
{
    public const CONTEXT_ACCOUNT_CREATED = 'account_created';

    public const CONTEXT_ACCOUNT_CREATED_RESEND = 'account_created_resend';

    public const CONTEXT_ADMIN_CREATE = 'admin_create';

    private const WHATSAPP_MAX_ATTEMPTS = 3;

    public function __construct(
        private AccountCreatedMessageRenderer $renderer,
        private WhatsAppSettingsService $whatsappSettings,
        private SendWhatsAppMessage $whatsAppSender,
        private EvolutionInstanceRotator $evolutionRotator,
        private EvolutionWhatsAppNumberResolver $numberResolver,
    ) {}

    public function generateSecurePassword(): string
    {
        // Avoid < > & " ' so passwords never get truncated by HTML strip_tags / email markup.
        $alphabet = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%*_+-=';
        $length = 16;
        $password = '';
        $max = strlen($alphabet) - 1;

        for ($i = 0; $i < $length; $i++) {
            $password .= $alphabet[random_int(0, $max)];
        }

        return $password;
    }

    /**
     * @return array{
     *     email_sent: bool,
     *     whatsapp_sent: bool,
     *     whatsapp_recipient: ?string,
     *     email_error: ?string,
     *     whatsapp_error: ?string
     * }
     */
    public function deliver(
        User $user,
        #[\SensitiveParameter] string $plainPassword,
        string $context = self::CONTEXT_ACCOUNT_CREATED,
        bool $sendEmail = true,
        bool $sendWhatsApp = true,
        ?string $whatsappRecipientOverride = null,
    ): array {
        $emailSent = false;
        $whatsappSent = false;
        $whatsappRecipient = null;
        $emailError = null;
        $whatsappError = null;

        if ($sendEmail) {
            try {
                $this->sendEmail($user, $plainPassword);
                $emailSent = true;
            } catch (\Throwable $e) {
                $emailError = $e->getMessage();
                Log::error('Account created credential email failed', [
                    'user_id' => $user->id,
                    'context' => $context,
                    'error' => $e->getMessage(),
                    'exception' => $e::class,
                ]);
            }
        }

        if ($sendWhatsApp) {
            try {
                $result = $this->sendWhatsAppWithRetries($user, $plainPassword, $whatsappRecipientOverride);
                $whatsappSent = $result['sent'];
                $whatsappRecipient = $result['recipient'];
                $whatsappError = $result['error'];
            } catch (\Throwable $e) {
                $whatsappError = $e->getMessage();
                Log::error('Account created credential WhatsApp failed', [
                    'user_id' => $user->id,
                    'context' => $context,
                    'recipient' => $whatsappRecipient,
                    'error' => $e->getMessage(),
                    'exception' => $e::class,
                ]);
            }
        }

        Log::info('Account created credentials delivered', [
            'user_id' => $user->id,
            'context' => $context,
            'email_sent' => $emailSent,
            'whatsapp_sent' => $whatsappSent,
            'whatsapp_recipient' => $whatsappRecipient,
            'email_error' => $emailError,
            'whatsapp_error' => $whatsappError,
        ]);

        return [
            'email_sent' => $emailSent,
            'whatsapp_sent' => $whatsappSent,
            'whatsapp_recipient' => $whatsappRecipient,
            'email_error' => $emailError,
            'whatsapp_error' => $whatsappError,
        ];
    }

    /**
     * إعادة تعيين كلمة المرور وإرسال بيانات الدخول (لإعادة الإرسال من الأدمن).
     *
     * @return array{
     *     email_sent: bool,
     *     whatsapp_sent: bool,
     *     whatsapp_recipient: ?string,
     *     email_error: ?string,
     *     whatsapp_error: ?string,
     *     plain_password: string
     * }
     */
    public function resetAndDeliver(
        User $user,
        bool $sendEmail = true,
        bool $sendWhatsApp = true,
        ?string $whatsappRecipientOverride = null,
    ): array {
        $plainPassword = $this->generateSecurePassword();
        $user->update(['password' => $plainPassword]);

        $result = $this->deliver(
            $user,
            $plainPassword,
            self::CONTEXT_ACCOUNT_CREATED_RESEND,
            $sendEmail,
            $sendWhatsApp,
            $whatsappRecipientOverride,
        );

        $result['plain_password'] = $plainPassword;

        return $result;
    }

    /**
     * @return array{sent: bool, recipient: ?string, error: ?string}
     */
    private function sendWhatsAppWithRetries(
        User $user,
        #[\SensitiveParameter] string $plainPassword,
        ?string $whatsappRecipientOverride = null,
    ): array {
        $recipient = $whatsappRecipientOverride ?? $this->resolveWhatsAppRecipient($user);

        if ($recipient === null) {
            return [
                'sent' => false,
                'recipient' => null,
                'error' => 'لا يوجد رقم واتساب مسجّل لهذا الحساب.',
            ];
        }

        if (! $this->isWhatsAppAvailable()) {
            return [
                'sent' => false,
                'recipient' => $recipient,
                'error' => 'خدمة الواتساب غير مفعّلة حالياً.',
            ];
        }

        $provider = $this->whatsappSettings->getSettings()['whatsapp_provider'] ?? '';
        $sendTo = $recipient;

        if ($provider === 'evolution') {
            $resolved = $this->numberResolver->resolve($recipient);

            if ($resolved['checked'] && $resolved['exists'] === false) {
                return [
                    'sent' => false,
                    'recipient' => $recipient,
                    'error' => 'الرقم غير مسجّل على واتساب: '.$recipient,
                ];
            }

            if ($resolved['digits'] !== '') {
                $sendTo = InternationalPhoneDigits::toDisplay($resolved['digits']);
                $recipient = $sendTo;
            }
        }

        $messageBody = $this->renderer->renderCredentialWhatsApp($user, $plainPassword);
        $lastError = null;

        for ($attempt = 1; $attempt <= self::WHATSAPP_MAX_ATTEMPTS; $attempt++) {
            try {
                $sentMessage = $this->whatsAppSender->sendTextSync(
                    $sendTo,
                    $messageBody,
                    previewUrl: false,
                    applySendDelay: false,
                );

                if (WhatsAppDeliveryAcceptance::isAccepted($sentMessage)) {
                    $this->sendPasswordOnlyFollowUp($sendTo, $plainPassword, $user->id);

                    return [
                        'sent' => true,
                        'recipient' => $recipient,
                        'error' => null,
                    ];
                }

                $lastError = WhatsAppDeliveryAcceptance::rejectionReason($sentMessage);
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
            }

            Log::warning('Account created credential WhatsApp attempt failed', [
                'user_id' => $user->id,
                'recipient' => $recipient,
                'attempt' => $attempt,
                'error' => $lastError,
            ]);

            if ($attempt < self::WHATSAPP_MAX_ATTEMPTS) {
                usleep(400_000 * $attempt);
            }
        }

        return [
            'sent' => false,
            'recipient' => $recipient,
            'error' => $lastError ?: 'فشل إرسال رسالة الواتساب بعد عدة محاولات.',
        ];
    }

    /**
     * رسالة ثانية بكلمة المرور فقط لتسهيل النسخ.
     */
    private function sendPasswordOnlyFollowUp(
        string $sendTo,
        #[\SensitiveParameter] string $plainPassword,
        int $userId,
    ): void {
        try {
            usleep(350_000);

            $this->whatsAppSender->sendTextSync(
                $sendTo,
                $plainPassword,
                previewUrl: false,
                applySendDelay: false,
            );
        } catch (\Throwable $e) {
            Log::warning('Account created password-only WhatsApp follow-up failed', [
                'user_id' => $userId,
                'recipient' => $sendTo,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendEmail(User $user, #[\SensitiveParameter] string $plainPassword): void
    {
        if (empty($user->email)) {
            throw new \InvalidArgumentException('لا يوجد بريد إلكتروني مسجّل لهذا الحساب.');
        }

        $this->applyActiveMailSettings();

        Mail::to($user->email)->send(new AccountCreatedCredentialsMail($user, $plainPassword));
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

        if (! ($settings['whatsapp_enabled'] ?? false)) {
            return false;
        }

        $provider = $settings['whatsapp_provider'] ?? '';

        if (! in_array($provider, ['evolution', 'whatsapp_web', 'custom_api'], true)) {
            return false;
        }

        if ($provider === 'evolution') {
            return $this->isEvolutionReady($settings);
        }

        if ($provider === 'whatsapp_web') {
            return trim((string) ($settings['whatsapp_web_service_url'] ?? '')) !== '';
        }

        return trim((string) ($settings['custom_api_url'] ?? '')) !== '';
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    private function isEvolutionReady(array $settings): bool
    {
        $baseUrl = trim((string) ($settings['evolution_base_url'] ?? ''));
        $apiKey = trim((string) ($settings['evolution_api_key'] ?? ''));

        if ($baseUrl === '' || $apiKey === '') {
            return false;
        }

        if ($this->evolutionRotator->poolCount() > 0) {
            return true;
        }

        $configuredInstance = trim((string) ($settings['evolution_instance_name'] ?? ''));
        if ($configuredInstance !== '') {
            return true;
        }

        return EvolutionInstance::defaultInstance() !== null;
    }

    private function resolveWhatsAppRecipient(User $user): ?string
    {
        $digits = InternationalPhoneDigits::forUser($user);

        return $digits !== null ? InternationalPhoneDigits::toDisplay($digits) : null;
    }
}
