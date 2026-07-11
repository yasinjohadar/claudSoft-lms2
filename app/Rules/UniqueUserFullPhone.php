<?php

namespace App\Rules;

use App\Models\User;
use App\Support\InternationalPhoneDigits;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Ensure country_code + phone is not already used by another user (canonical E.164 digits).
 */
class UniqueUserFullPhone implements ValidationRule
{
    public function __construct(
        private ?int $ignoreUserId = null,
        private ?string $countryCodeField = 'country_code',
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $phone = trim((string) ($value ?? ''));
        if ($phone === '') {
            return;
        }

        $countryCode = trim((string) request()->input($this->countryCodeField, ''));
        if ($countryCode === '') {
            return;
        }

        $digits = InternationalPhoneDigits::fromCountryAndLocal($countryCode, $phone);
        if ($digits === null) {
            return;
        }

        if (User::query()->whereFullPhoneDigits($digits, $this->ignoreUserId)->exists()) {
            $fail('رقم الهاتف مستخدم بالفعل لحساب آخر.');
        }
    }
}
