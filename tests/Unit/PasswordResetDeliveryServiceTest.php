<?php

use App\Models\User;
use App\Services\Auth\PasswordResetDeliveryService;
use App\Support\InternationalPhoneDigits;
use App\Support\WapiPhoneNormalizer;

uses(Tests\TestCase::class);

test('buildFullPhoneDigits combines country code and local number', function () {
    expect(PasswordResetDeliveryService::buildFullPhoneDigits('+966', '501234567'))
        ->toBe('966501234567');
    expect(PasswordResetDeliveryService::buildFullPhoneDigits('+90', '05519665883'))
        ->toBe('905519665883');
    expect(PasswordResetDeliveryService::buildFullPhoneDigits('+963', '0991234567'))
        ->toBe('963991234567');
    expect(PasswordResetDeliveryService::buildFullPhoneDigits('+961', '076123456'))
        ->toBe('96176123456');
});

test('InternationalPhoneDigits repairs stored syrian number with trunk zero', function () {
    $user = new User([
        'country_code' => '+963',
        'phone' => '0991234567',
        'full_phone' => '+9630991234567',
    ]);

    expect(InternationalPhoneDigits::forUser($user))->toBe('963991234567');
});

test('InternationalPhoneDigits repairs stored lebanese number with trunk zero', function () {
    $user = new User([
        'country_code' => '+961',
        'phone' => '076123456',
        'full_phone' => '+961076123456',
    ]);

    expect(InternationalPhoneDigits::forUser($user))->toBe('96176123456');
});

test('forgot password input matches repaired stored syrian digits', function () {
    $input = PasswordResetDeliveryService::buildFullPhoneDigits('+963', '991234567');
    $stored = InternationalPhoneDigits::forUser(new User([
        'country_code' => '+963',
        'phone' => '0991234567',
        'full_phone' => '+9630991234567',
    ]));

    expect($input)->toBe($stored);
});

test('WapiPhoneNormalizer strips non digits from phone input', function () {
    expect(WapiPhoneNormalizer::normalize('+966 50 123 4567'))->toBe('966501234567');
});

test('WapiPhoneNormalizer validates e164 digit length', function () {
    expect(WapiPhoneNormalizer::isValidE164Digits('966501234567'))->toBeTrue();
    expect(WapiPhoneNormalizer::isValidE164Digits('123'))->toBeFalse();
});
