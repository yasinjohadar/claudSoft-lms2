<?php

use App\Services\SmtpConnectionTestService;
use Tests\TestCase;

uses(TestCase::class);

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
