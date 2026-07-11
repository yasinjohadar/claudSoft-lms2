<?php

use App\Models\GroupRegistrationSetting;
use App\Services\Auth\AccountCreatedCredentialDeliveryService;
use Tests\TestCase;

uses(TestCase::class);

test('resolve new account password uses fixed password when enabled', function () {
    $settings = new GroupRegistrationSetting([
        'use_fixed_registration_password' => true,
        'fixed_registration_password' => 'ClaudSoft2026',
    ]);

    expect($settings->resolveNewAccountPassword())->toBe('ClaudSoft2026');
});

test('resolve new account password falls back to random when fixed disabled', function () {
    $settings = new GroupRegistrationSetting([
        'use_fixed_registration_password' => false,
        'fixed_registration_password' => 'ClaudSoft2026',
    ]);

    $password = $settings->resolveNewAccountPassword();

    expect($password)->not->toBe('ClaudSoft2026')
        ->and(strlen($password))->toBeGreaterThanOrEqual(12);
});

test('resolve new account password falls back to random when fixed enabled but empty', function () {
    $settings = new GroupRegistrationSetting([
        'use_fixed_registration_password' => true,
        'fixed_registration_password' => '   ',
    ]);

    $password = $settings->resolveNewAccountPassword();

    expect($password)->not->toBe('')
        ->and(strlen($password))->toBeGreaterThanOrEqual(12);
});
