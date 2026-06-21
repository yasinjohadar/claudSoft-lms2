<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\WhatsApp\SendWhatsAppMessage;
use App\Services\WhatsApp\WhatsAppSettingsService;
use App\Support\UserPhoneCountryValidator;
use App\Support\WapiPhoneNormalizer;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\Password;
use InvalidArgumentException;

class PasswordResetDeliveryService
{
    public function __construct(
        private WhatsAppSettingsService $whatsappSettings
    ) {}

    public function isWhatsAppAvailable(): bool
    {
        $settings = $this->whatsappSettings->getSettings();

        return ($settings['whatsapp_enabled'] ?? false)
            && in_array($settings['whatsapp_provider'] ?? '', ['evolution', 'whatsapp_web', 'custom_api'], true);
    }

    public function sendViaEmail(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    public function sendViaWhatsApp(string $phoneInput): string
    {
        return $this->sendViaWhatsAppDigits(WapiPhoneNormalizer::normalize($phoneInput));
    }

    /**
     * Send reset link via WhatsApp using country code + local phone parts.
     */
    public function sendViaWhatsAppParts(string $countryCode, string $localPhone): string
    {
        return $this->sendViaWhatsAppDigits(self::buildFullPhoneDigits($countryCode, $localPhone));
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

    private function sendViaWhatsAppDigits(string $digits): string
    {
        if ($digits === '' || ! WapiPhoneNormalizer::isValidE164Digits($digits)) {
            throw new InvalidArgumentException('رقم الجوال غير صالح.');
        }

        if (! $this->isWhatsAppAvailable()) {
            throw new InvalidArgumentException('خدمة الواتساب غير مفعّلة حالياً.');
        }

        $user = $this->findUserByPhone($digits);
        if (! $user) {
            return Password::INVALID_USER;
        }

        if (! UserPhoneCountryValidator::isConsistent($user)) {
            throw new InvalidArgumentException('رقم هاتف الحساب غير مكتمل أو غير صالح. تواصل مع الدعم.');
        }

        $recipient = $this->resolveWhatsAppRecipient($user);
        if ($recipient === null) {
            throw new InvalidArgumentException('لا يوجد رقم واتساب مسجّل لهذا الحساب.');
        }

        /** @var PasswordBroker $broker */
        $broker = Password::broker();

        if ($broker->getRepository()->recentlyCreatedToken($user)) {
            return Password::RESET_THROTTLED;
        }

        $token = $broker->createToken($user);
        $url = $this->buildResetUrl($user, $token);
        $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);
        $message = app(PasswordResetMessageRenderer::class)->renderWhatsApp($user, $url, $expireMinutes);

        app(SendWhatsAppMessage::class)->sendTextSync($recipient, $message);

        return Password::RESET_LINK_SENT;
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

            if (strlen($digits) >= 9 && strlen($userDigits) >= 9
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
