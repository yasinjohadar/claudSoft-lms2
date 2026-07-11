<?php

use App\Models\User;
use App\Services\Auth\AccountCreatedCredentialDeliveryService;
use App\Services\Auth\AccountCreatedMessageRenderer;
use App\Services\Auth\AccountCreatedMessageSettingsService;
use Tests\TestCase;

uses(TestCase::class);

test('account created renderer replaces placeholders including password and login url', function () {
    config(['app.url' => 'https://claudsoft.test']);

    $settingsService = \Mockery::mock(AccountCreatedMessageSettingsService::class);
    $settingsService->shouldReceive('getSettings')->andReturn([
        'whatsapp_template_id' => '',
        'whatsapp_body' => AccountCreatedMessageRenderer::defaultWhatsAppBody(),
        'email_subject' => 'بيانات حسابك',
        'email_body' => AccountCreatedMessageRenderer::defaultEmailBody(),
        'admin_instructions' => 'غيّر كلمة المرور فوراً',
    ]);

    $renderer = new AccountCreatedMessageRenderer($settingsService);

    $user = new User([
        'name' => 'Yasin',
        'name_ar' => 'ياسين جوخدار',
        'email' => 'new-student@example.com',
    ]);

    $message = $renderer->renderCredentialWhatsApp($user, 'TempPass#12345!');

    expect($message)
        ->toContain('ياسين جوخدار')
        ->toContain('Yasin')
        ->toContain('new-student@example.com')
        ->toContain('TempPass#12345!')
        ->toContain('غيّر كلمة المرور فوراً')
        ->toContain('/login')
        ->not->toContain('{password}')
        ->not->toContain('{login_url}');
});

test('account created credential delivery service generates secure password', function () {
    $service = app(AccountCreatedCredentialDeliveryService::class);
    $password = $service->generateSecurePassword();

    expect(strlen($password))->toBeGreaterThanOrEqual(16);
});

test('account created default templates include required credential placeholders', function () {
    expect(AccountCreatedMessageRenderer::defaultWhatsAppBody())
        ->toContain('{student_name_ar}')
        ->toContain('{student_name_en}')
        ->toContain('{email}')
        ->toContain('{password}')
        ->toContain('{login_url}')
        ->toContain('{admin_instructions}');

    expect(AccountCreatedMessageRenderer::defaultEmailBody())
        ->toContain('{password}')
        ->toContain('{login_url}')
        ->toContain('{admin_instructions}');
});

test('whatsapp render keeps full password even when it contains angle brackets', function () {
    config(['app.url' => 'https://claudsoft.test']);

    $dangerousPassword = 'Ab3<xyz>&"\'!';

    $settingsService = \Mockery::mock(AccountCreatedMessageSettingsService::class);
    $settingsService->shouldReceive('getSettings')->andReturn([
        'whatsapp_template_id' => '',
        'whatsapp_body' => '<p>كلمة المرور: {password}</p>',
        'email_subject' => 'بيانات حسابك',
        'email_body' => '<p>{password}</p>',
        'admin_instructions' => '',
    ]);

    $renderer = new AccountCreatedMessageRenderer($settingsService);
    $user = new User([
        'name' => 'Yasin',
        'name_ar' => 'ياسين',
        'email' => 'student@example.com',
    ]);

    $message = $renderer->renderCredentialWhatsApp($user, $dangerousPassword);

    expect($message)->toContain($dangerousPassword)
        ->and($message)->not->toContain('<p>');

    $emailHtml = $renderer->renderCredentialEmailBodyHtml($user, $dangerousPassword);
    expect($emailHtml)->toContain(e($dangerousPassword))
        ->and($emailHtml)->not->toContain($dangerousPassword);
});
