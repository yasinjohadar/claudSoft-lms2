<?php

namespace App\Rules;

use App\Support\UserPhoneCountryValidator;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Cross-field validation: phone must match country_code (libphonenumber).
 * Empty phone + empty country_code is allowed.
 */
class PhoneMatchesCountryCode implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $msg = UserPhoneCountryValidator::validatePair(
            request()->input('country_code'),
            $value !== null ? (string) $value : ''
        );
        if ($msg !== null) {
            $fail($msg);
        }
    }
}
