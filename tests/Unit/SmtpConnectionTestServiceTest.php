<?php

use App\Models\EmailSetting;
use App\Services\SmtpConnectionTestService;
use Tests\TestCase;

uses(TestCase::class);

test('port 587 always resolves to smtp scheme regardless of encryption label', function () {
    expect(EmailSetting::resolveMailScheme(587, 'ssl'))->toBe('smtp');
    expect(EmailSetting::resolveMailScheme(587, 'tls'))->toBe('smtp');
    expect(EmailSetting::usesImplicitTls(587, 'ssl'))->toBeFalse();
});

test('port 465 always resolves to smtps scheme', function () {
    expect(EmailSetting::resolveMailScheme(465, 'tls'))->toBe('smtps');
    expect(EmailSetting::usesImplicitTls(465, 'tls'))->toBeTrue();
});

test('smtp connection test fails with invalid host', function () {
    $service = new SmtpConnectionTestService();

    $result = $service->test([
        'host' => 'invalid.smtp.host.example',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'user@example.com',
        'password' => 'secret',
    ]);

    expect($result['success'])->toBeFalse();
    expect($result['message'])->not->toBeEmpty();
});

test('smtp connection test validates required fields', function () {
    $service = new SmtpConnectionTestService();

    $result = $service->test([
        'host' => '',
        'port' => 0,
        'encryption' => 'tls',
        'username' => '',
        'password' => '',
    ]);

    expect($result['success'])->toBeFalse();
});
