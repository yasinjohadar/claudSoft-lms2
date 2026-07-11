<?php

namespace App\Services\Auth;

use App\Mail\PasswordCredentialsMail;
use App\Models\EmailSetting;
use App\Models\EvolutionInstance;
use App\Models\User;
use App\Support\CredentialPassword;
use App\Support\InternationalPhoneDigits;
use App\Services\WhatsApp\Evolution\EvolutionInstanceRotator;
use App\Services\WhatsApp\Evolution\EvolutionRotatingSendService;
use App\Services\WhatsApp\Evolution\EvolutionWhatsAppNumberResolver;
use App\Services\WhatsApp\SendWhatsAppMessage;
use App\Services\WhatsApp\WhatsAppDeliveryAcceptance;
use App\Services\WhatsApp\WhatsAppSettingsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PasswordCredentialDeliveryService
{
    public const CONTEXT_FORGOT_AUTO = 'forgot_auto';

    public const CONTEXT_FORGOT_MANUAL = 'forgot_manual';

    public const CONTEXT_ADMIN_RESET = 'admin_reset';

    private const WHATSAPP_MAX_ATTEMPTS = 3;

    private const LAST_STICKY_CACHE_KEY = 'evolution_pwd_cred_last_instance';

    public function __construct(
        private PasswordResetMessageRenderer $renderer,
        private WhatsAppSettingsService $whatsappSettings,
        private SendWhatsAppMessage $whatsAppSender,
        private EvolutionInstanceRotator $evolutionRotator,
        private EvolutionRotatingSendService $rotatingSend,
        private EvolutionWhatsAppNumberResolver $numberResolver,
    ) {}

    public function generateSecurePassword(): string
    {
        return CredentialPassword::generate();
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
        string $context,
        ?string $whatsappRecipientOverride = null,
        bool $requireWhatsApp = false,
        bool $requireEmail = false,
    ): array {
        $emailSent = false;
        $whatsappSent = false;
        $whatsappRecipient = null;
        $emailError = null;
        $whatsappError = null;

        // Prefer the required channel first so we never email/WA a password that will not be saved.
        if ($requireWhatsApp) {
            try {
                $result = $this->sendWhatsAppWithRetries($user, $plainPassword, $whatsappRecipientOverride);
                $whatsappSent = $result['sent'];
                $whatsappRecipient = $result['recipient'];
                $whatsappError = $result['error'];
            } catch (\Throwable $e) {
                $whatsappError = $e->getMessage();
                Log::error('Password credential WhatsApp failed', [
                    'user_id' => $user->id,
                    'context' => $context,
                    'recipient' => $whatsappRecipient,
                    'error' => $e->getMessage(),
                    'exception' => $e::class,
                ]);
            }

            if (! $whatsappSent) {
                throw new \InvalidArgumentException(
                    $whatsappError ?: 'تعذّر إرسال بيانات الدخول عبر الواتساب. حاول لاحقاً أو استخدم البريد الإلكتروني.'
                );
            }

            try {
                $this->sendEmail($user, $plainPassword);
                $emailSent = true;
            } catch (\Throwable $e) {
                $emailError = $e->getMessage();
                Log::error('Password credential email failed', [
                    'user_id' => $user->id,
                    'context' => $context,
                    'error' => $e->getMessage(),
                    'exception' => $e::class,
                ]);
            }
        } elseif ($requireEmail) {
            try {
                $this->sendEmail($user, $plainPassword);
                $emailSent = true;
            } catch (\Throwable $e) {
                $emailError = $e->getMessage();
                Log::error('Password credential email failed', [
                    'user_id' => $user->id,
                    'context' => $context,
                    'error' => $e->getMessage(),
                    'exception' => $e::class,
                ]);
            }

            if (! $emailSent) {
                throw new \InvalidArgumentException(
                    $emailError ?: 'تعذّر إرسال بيانات الدخول عبر البريد الإلكتروني. تحقق من إعدادات SMTP أو حاول لاحقاً.'
                );
            }

            try {
                $result = $this->sendWhatsAppWithRetries($user, $plainPassword, $whatsappRecipientOverride);
                $whatsappSent = $result['sent'];
                $whatsappRecipient = $result['recipient'];
                $whatsappError = $result['error'];
            } catch (\Throwable $e) {
                $whatsappError = $e->getMessage();
                Log::error('Password credential WhatsApp failed', [
                    'user_id' => $user->id,
                    'context' => $context,
                    'recipient' => $whatsappRecipient,
                    'error' => $e->getMessage(),
                    'exception' => $e::class,
                ]);
            }
        } else {
            try {
                $this->sendEmail($user, $plainPassword);
                $emailSent = true;
            } catch (\Throwable $e) {
                $emailError = $e->getMessage();
                Log::error('Password credential email failed', [
                    'user_id' => $user->id,
                    'context' => $context,
                    'error' => $e->getMessage(),
                    'exception' => $e::class,
                ]);
            }

            try {
                $result = $this->sendWhatsAppWithRetries($user, $plainPassword, $whatsappRecipientOverride);
                $whatsappSent = $result['sent'];
                $whatsappRecipient = $result['recipient'];
                $whatsappError = $result['error'];
            } catch (\Throwable $e) {
                $whatsappError = $e->getMessage();
                Log::error('Password credential WhatsApp failed', [
                    'user_id' => $user->id,
                    'context' => $context,
                    'recipient' => $whatsappRecipient,
                    'error' => $e->getMessage(),
                    'exception' => $e::class,
                ]);
            }
        }

        Log::info('Password credentials delivered', [
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

        // One sticky Evolution number for credentials + password-only; rotate on next request.
        $sticky = $provider === 'evolution' ? $this->pickStickyInstance() : null;
        $stickyInstance = $sticky?->instance_name;
        $messageBody = $this->renderer->renderCredentialWhatsApp($user, $plainPassword);
        $lastError = null;

        for ($attempt = 1; $attempt <= self::WHATSAPP_MAX_ATTEMPTS; $attempt++) {
            try {
                $sentMessage = $this->whatsAppSender->sendTextSync(
                    $sendTo,
                    $messageBody,
                    previewUrl: false,
                    applySendDelay: false,
                    evolutionInstanceName: $stickyInstance,
                );

                if (WhatsAppDeliveryAcceptance::isAccepted($sentMessage)) {
                    $this->sendPasswordOnlyFollowUp($sendTo, $plainPassword, $user->id, $stickyInstance);

                    if ($stickyInstance !== null) {
                        $this->rememberStickyInstanceUsed($stickyInstance);
                    }

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

            Log::warning('Password credential WhatsApp attempt failed', [
                'user_id' => $user->id,
                'recipient' => $recipient,
                'attempt' => $attempt,
                'evolution_instance' => $stickyInstance,
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
     * رسالة ثانية بكلمة المرور فقط لتسهيل النسخ — نفس رقم المرسل.
     */
    private function sendPasswordOnlyFollowUp(
        string $sendTo,
        #[\SensitiveParameter] string $plainPassword,
        int $userId,
        ?string $evolutionInstanceName = null,
    ): void {
        try {
            usleep(350_000);

            $this->whatsAppSender->sendTextSync(
                $sendTo,
                CredentialPassword::forWhatsAppDisplay($plainPassword),
                previewUrl: false,
                applySendDelay: false,
                evolutionInstanceName: $evolutionInstanceName,
            );
        } catch (\Throwable $e) {
            Log::warning('Password-only WhatsApp follow-up failed', [
                'user_id' => $userId,
                'recipient' => $sendTo,
                'evolution_instance' => $evolutionInstanceName,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Pick one Evolution instance for this delivery.
     * Consecutive password-credential requests prefer a different number when the pool has 2+.
     */
    private function pickStickyInstance(): ?EvolutionInstance
    {
        $settings = $this->whatsappSettings->getSettings();

        if (($settings['whatsapp_provider'] ?? '') !== 'evolution') {
            return null;
        }

        if ($this->rotatingSend->isRotationActive() && $this->evolutionRotator->poolCount(true) > 0) {
            try {
                $pool = $this->evolutionRotator->orderedPoolForFailover(true);
                if ($pool->isNotEmpty()) {
                    $lastName = Cache::get(self::LAST_STICKY_CACHE_KEY);
                    $preferred = $lastName
                        ? $pool->first(fn (EvolutionInstance $instance) => $instance->instance_name !== $lastName)
                        : null;

                    $picked = $preferred ?? $pool->first();

                    Log::info('Password credential sticky Evolution picked', [
                        'instance' => $picked?->instance_name,
                        'avoided_previous' => $lastName,
                        'pool_size' => $pool->count(),
                    ]);

                    return $picked;
                }
            } catch (\Throwable $e) {
                Log::warning('Password reset sticky Evolution pick from pool failed', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $fallback = $this->rotatingSend->fallbackInstanceName();
        if ($fallback === '') {
            return null;
        }

        return EvolutionInstance::query()
            ->where('instance_name', $fallback)
            ->first()
            ?? new EvolutionInstance(['instance_name' => $fallback]);
    }

    private function rememberStickyInstanceUsed(string $instanceName): void
    {
        Cache::put(self::LAST_STICKY_CACHE_KEY, $instanceName, now()->addDays(7));

        try {
            $instance = EvolutionInstance::query()
                ->where('instance_name', $instanceName)
                ->first();

            if ($instance !== null) {
                $this->evolutionRotator->markUsed($instance);
            }
        } catch (\Throwable $e) {
            Log::warning('Password credential sticky markUsed failed', [
                'instance' => $instanceName,
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
