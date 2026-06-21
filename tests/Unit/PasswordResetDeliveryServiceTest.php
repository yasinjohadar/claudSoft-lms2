<?php

use App\Support\WapiPhoneNormalizer;

test('buildFullPhoneDigits combines country code and local number', function () {
    expect(App\Services\Auth\PasswordResetDeliveryService::buildFullPhoneDigits('+966', '501234567'))
        ->toBe('966501234567');
    expect(App\Services\Auth\PasswordResetDeliveryService::buildFullPhoneDigits('+90', '05519665883'))
        ->toBe('905519665883');
});

test('WapiPhoneNormalizer strips non digits from phone input', function () {
    expect(WapiPhoneNormalizer::normalize('+966 50 123 4567'))->toBe('966501234567');
});

test('WapiPhoneNormalizer validates e164 digit length', function () {
    expect(WapiPhoneNormalizer::isValidE164Digits('966501234567'))->toBeTrue();
    expect(WapiPhoneNormalizer::isValidE164Digits('123'))->toBeFalse();
});
