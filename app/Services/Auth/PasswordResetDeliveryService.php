<?php

namespace App\Services\Auth;

use App\Models\EmailSetting;
use App\Models\User;
use App\Support\UserPhoneCountryValidator;
use App\Support\WapiPhoneNormalizer;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use InvalidArgumentException;

class PasswordResetDeliveryService
{
    public function __construct(
        private PasswordCredentialDeliveryService $credentialDelivery,
    ) {}

    public function isWhatsAppAvailable(): bool
    {
        return $this->credentialDelivery->isWhatsAppAvailable();
    }

    /**
     * @return array{status: string, delivery: array{email_sent: bool, whatsapp_sent: bool, whatsapp_recipient: ?string}}
     */
    public function sendViaEmail(string $email): array
    {
        $this->applyActiveMailSettings();

        $user = User::query()->where('email', $email)->first();
        if (! $user) {
            return [
                'status' => Password::INVALID_USER,
                'delivery' => $this->emptyDelivery(),
            ];
        }

        return $this->assignPasswordAndDeliver($user);
    }

    /**
     * @return array{status: string, delivery: array{email_sent: bool, whatsapp_sent: bool, whatsapp_recipient: ?string}}
     */
    public function sendViaWhatsAppParts(string $countryCode, string $localPhone): array
    {
        return $this->sendViaWhatsAppDigits(
            self::buildFullPhoneDigits($countryCode, $localPhone),
            self::formatPhoneForDisplay($countryCode, $localPhone)
        );
    }

    public static function buildFullPhoneDigits(string $countryCode, string $localPhone): string
    {
        $codeDigits = preg_replace('/\D+/', '', $countryCode) ?? '';
        $localDigits = preg_replace('/\D+/', '', $localPhone) ?? '';
        $localDigits = ltrim($localDigits, '0');

        if ($codeDigits === '' || $localDigits === '') {
            throw new InvalidArgumentException('رمز الدولة ورقم الجوال مطلوبان.');
        }

        return $codeDigits.$localDigits;
    }

    public function formatPhoneForDisplay(string $countryCode, string $localPhone): string
    {
        $digits = self::buildFullPhoneDigits($countryCode, $localPhone);

        return '+'.$digits;
    }

    public function sendViaWhatsApp(string $phoneInput): array
    {
        $digits = WapiPhoneNormalizer::normalize($phoneInput);

        return $this->sendViaWhatsAppDigits($digits, '+'.$digits);
    }

    /**
     * @return array{status: string, delivery: array{email_sent: bool, whatsapp_sent: bool, whatsapp_recipient: ?string}}
     */
    private function sendViaWhatsAppDigits(string $digits, string $displayRecipient): array
    {
        if ($digits === '' || ! WapiPhoneNormalizer::isValidE164Digits($digits)) {
            throw new InvalidArgumentException('رقم الجوال غير صالح.');
        }

        if (! $this->isWhatsAppAvailable()) {
            throw new InvalidArgumentException('خدمة الواتساب غير مفعّلة حالياً.');
        }

        $user = $this->findUserByPhone($digits);
        if (! $user) {
            return [
                'status' => Password::INVALID_USER,
                'delivery' => $this->emptyDelivery(),
            ];
        }

        if (! UserPhoneCountryValidator::isConsistent($user)) {
            throw new InvalidArgumentException('رقم هاتف الحساب غير مكتمل أو غير صالح. تواصل مع الدعم.');
        }

        return $this->assignPasswordAndDeliver($user, $displayRecipient);
    }

    /**
     * @return array{status: string, delivery: array{email_sent: bool, whatsapp_sent: bool, whatsapp_recipient: ?string}}
     */
    private function assignPasswordAndDeliver(User $user, ?string $whatsappRecipientOverride = null): array
    {
        /** @var PasswordBroker $broker */
        $broker = Password::broker();

        if ($broker->getRepository()->recentlyCreatedToken($user)) {
            return [
                'status' => Password::RESET_THROTTLED,
                'delivery' => $this->emptyDelivery(),
            ];
        }

        $plainPassword = $this->credentialDelivery->generateSecurePassword();

        $delivery = $this->credentialDelivery->deliver(
            $user,
            $plainPassword,
            PasswordCredentialDeliveryService::CONTEXT_FORGOT_AUTO,
            $whatsappRecipientOverride
        );

        if (! $delivery['email_sent'] && ! $delivery['whatsapp_sent']) {
            throw new InvalidArgumentException('تعذّر إرسال بيانات الدخول عبر البريد والواتساب. تحقق من إعدادات الإرسال أو تواصل مع الدعم.');
        }

        $user->forceFill([
            'password' => Hash::make($plainPassword),
        ])->save();

        $broker->createToken($user);

        return [
            'status' => Password::RESET_LINK_SENT,
            'delivery' => $delivery,
        ];
    }

    public function findUserByPhone(string $phoneInput): ?User
    {
        $digits = WapiPhoneNormalizer::normalize($phoneInput);
        if ($digits === '') {
            return null;
        }

        $users = User::query()
            ->where(function ($q) {
                $q->whereNotNull('full_phone')->where('full_phone', '!=', '')
                    ->orWhere(function ($q2) {
                        $q2->whereNotNull('phone')->where('phone', '!=', '');
                    });
            })
            ->get();

        $requiresExactMatch = strlen($digits) >= 10;
        $matches = [];

        foreach ($users as $user) {
            $userDigits = WapiPhoneNormalizer::normalize(
                $user->full_phone
                ?? trim(($user->country_code ?? '').($user->phone ?? ''))
                ?: ($user->phone ?? '')
            );

            if ($userDigits === '' || strlen($userDigits) < 8) {
                continue;
            }

            if ($userDigits === $digits) {
                return $user;
            }

            if (! $requiresExactMatch
                && strlen($digits) >= 9
                && strlen($userDigits) >= 9
                && substr($userDigits, -9) === substr($digits, -9)) {
                $matches[] = $user;
            }
        }

        return count($matches) === 1 ? $matches[0] : null;
    }

    public function buildResetUrl(User $user, string $token): string
    {
        return url(route('password.reset', [
            'token' => $token,
            'email' => $user->getEmailForPasswordReset(),
        ], false));
    }

    public function applyActiveMailSettings(): void
    {
        $setting = EmailSetting::getActive();

        if ($setting) {
            $setting->applyToConfig();
        }
    }

    public function resolveWhatsAppRecipient(User $user): ?string
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

    public static function buildSuccessMessage(array $delivery): string
    {
        $emailSent = (bool) ($delivery['email_sent'] ?? false);
        $whatsappSent = (bool) ($delivery['whatsapp_sent'] ?? false);
        $whatsappRecipient = (string) ($delivery['whatsapp_recipient'] ?? '');

        if ($emailSent && $whatsappSent) {
            $message = 'تم إرسال بيانات الدخول الجديدة إلى بريدك الإلكتروني وواتسابك';
            if ($whatsappRecipient !== '') {
                $message .= ' ('.$whatsappRecipient.')';
            }

            return $message.'.';
        }

        if ($emailSent) {
            return 'تم إرسال بيانات الدخول إلى بريدك الإلكتروني. تعذّر الإرسال عبر الواتساب — تحقق من رقم الواتساب المسجّل في حسابك.';
        }

        if ($whatsappSent) {
            $message = 'تم إرسال بيانات الدخول عبر الواتساب';
            if ($whatsappRecipient !== '') {
                $message .= ' إلى '.$whatsappRecipient;
            }

            return $message.'. تعذّر الإرسال عبر البريد الإلكتروني.';
        }

        return 'تعذّر إرسال بيانات الدخول.';
    }

    /**
     * @return array{email_sent: bool, whatsapp_sent: bool, whatsapp_recipient: ?string}
     */
    private function emptyDelivery(): array
    {
        return [
            'email_sent' => false,
            'whatsapp_sent' => false,
            'whatsapp_recipient' => null,
        ];
    }
}
