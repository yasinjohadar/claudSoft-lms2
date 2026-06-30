<?php

use App\Enums\OtpPurpose;
use App\Models\WapiMessage;
use App\Models\WapiTemplate;
use App\Services\Auth\PhoneOtpService;
use App\Services\Auth\PhoneOtpSettingsService;
use App\Services\Auth\PhoneOtpWhatsAppSender;
use App\Services\WapiOutboundDispatcher;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Auth\PhoneOtpTestCase;

uses(PhoneOtpTestCase::class);

function createOtpTemplate(array $structure = []): WapiTemplate
{
    return WapiTemplate::query()->create([
        'name' => 'otp_test_'.uniqid(),
        'language' => 'ar',
        'structure' => array_merge([
            'status' => 'APPROVED',
            'header_placeholders' => 0,
            'body_placeholders' => 1,
            'has_media_header' => false,
        ], $structure),
    ]);
}

function seedOtpSettings(WapiTemplate $template, array $overrides = []): void
{
    app(PhoneOtpSettingsService::class)->updateSettings(array_merge([
        'enabled' => true,
        'wapi_template_id' => $template->id,
        'template_language' => 'ar',
        'ttl_seconds' => 300,
        'max_attempts' => 5,
        'resend_cooldown_seconds' => 60,
        'code_length' => 6,
        'rate_limit_max_per_phone' => 3,
        'rate_limit_window_minutes' => 15,
        'register_enabled' => true,
        'login_enabled' => true,
        'reset_password_enabled' => true,
        'change_phone_enabled' => true,
    ], $overrides));
}

function mockAvailableOtpSender(): void
{
    $mock = Mockery::mock(PhoneOtpWhatsAppSender::class);
    $mock->shouldReceive('isAvailable')->andReturn(true);
    $mock->shouldReceive('send')->andReturnNull();
    app()->instance(PhoneOtpWhatsAppSender::class, $mock);
}

test('otp send stores code and dispatches whatsapp when login scenario enabled', function () {
    $template = createOtpTemplate();
    seedOtpSettings($template);
    mockAvailableOtpSender();

    $service = app(PhoneOtpService::class);
    $result = $service->send('966501234567', OtpPurpose::Login);

    expect($result)->toHaveKeys(['otp_id', 'expires_at', 'cooldown_seconds']);
    $this->assertDatabaseHas('phone_otp_codes', [
        'phone' => '966501234567',
        'purpose' => OtpPurpose::Login->value,
    ]);
});

test('otp send is blocked when register scenario is disabled', function () {
    $template = createOtpTemplate();
    seedOtpSettings($template, ['register_enabled' => false]);
    mockAvailableOtpSender();

    $service = app(PhoneOtpService::class);

    expect(fn () => $service->send('966501234567', OtpPurpose::Register))
        ->toThrow(InvalidArgumentException::class, 'خدمة OTP عبر الواتساب غير متاحة حالياً.');
});

test('otp send is blocked when otp is disabled in admin settings', function () {
    $template = createOtpTemplate();
    seedOtpSettings($template, ['enabled' => false]);

    $mock = Mockery::mock(PhoneOtpWhatsAppSender::class);
    $mock->shouldReceive('isAvailable')->andReturn(false);
    app()->instance(PhoneOtpWhatsAppSender::class, $mock);

    $service = app(PhoneOtpService::class);

    expect(fn () => $service->send('966501234567', OtpPurpose::Login))
        ->toThrow(InvalidArgumentException::class);
});

test('otp resend is blocked during cooldown', function () {
    $template = createOtpTemplate();
    seedOtpSettings($template, ['resend_cooldown_seconds' => 120]);
    mockAvailableOtpSender();

    $service = app(PhoneOtpService::class);
    $service->send('966501234567', OtpPurpose::Login);

    expect($service->getResendCooldownRemaining('966501234567', OtpPurpose::Login))->toBeGreaterThan(0);

    expect(fn () => $service->send('966501234567', OtpPurpose::Login))
        ->toThrow(InvalidArgumentException::class, 'يرجى الانتظار قبل إعادة إرسال الرمز.');
});

test('otp verify accepts correct code', function () {
    Cache::flush();
    $template = createOtpTemplate();
    seedOtpSettings($template);

    $capturedCode = null;
    $sender = Mockery::mock(PhoneOtpWhatsAppSender::class);
    $sender->shouldReceive('isAvailable')->andReturn(true);
    $sender->shouldReceive('send')->andReturnUsing(function (string $phone, string $code) use (&$capturedCode) {
        $capturedCode = $code;
    });
    app()->instance(PhoneOtpWhatsAppSender::class, $sender);

    $service = app(PhoneOtpService::class);
    $service->send('966501234567', OtpPurpose::Login);

    expect($capturedCode)->not->toBeNull();

    $verified = $service->verify('966501234567', OtpPurpose::Login, $capturedCode);

    expect($verified->verified_at)->not->toBeNull();
});

test('whatsapp sender builds body components from template structure', function () {
    $template = createOtpTemplate(['body_placeholders' => 1]);
    seedOtpSettings($template);

    $components = null;
    $language = null;

    $dispatcher = Mockery::mock(WapiOutboundDispatcher::class);
    $dispatcher->shouldReceive('queueTemplate')->once()->andReturnUsing(function (
        string $phone,
        string $templateName,
        string $lang,
        array $comps,
    ) use (&$components, &$language) {
        $components = $comps;
        $language = $lang;

        return new WapiMessage;
    });

    $whatsApp = Mockery::mock(WhatsAppService::class);
    $whatsApp->shouldReceive('assertConfigured')->andReturnNull();

    $sender = new PhoneOtpWhatsAppSender(
        app(PhoneOtpSettingsService::class),
        $dispatcher,
        $whatsApp
    );

    $sender->send('966501234567', '123456');

    expect($language)->toBe('ar');
    expect($components)->toBe([
        [
            'type' => 'body',
            'parameters' => [
                ['type' => 'text', 'text' => '123456'],
            ],
        ],
    ]);
});

test('whatsapp sender rejects templates with media header', function () {
    $template = createOtpTemplate([
        'has_media_header' => true,
        'body_placeholders' => 1,
    ]);
    seedOtpSettings($template);

    $sender = app(PhoneOtpWhatsAppSender::class);
    $issues = $sender->validateTemplateForOtp($template);

    expect($issues)->not->toBeEmpty();
});

test('verify page shows resend button', function () {
    $template = createOtpTemplate();
    seedOtpSettings($template);
    mockAvailableOtpSender();

    $service = app(PhoneOtpService::class);
    $service->send('966501234567', OtpPurpose::Login);

    $response = $this->get(route('phone-otp.verify', [
        'purpose' => OtpPurpose::Login->value,
        'phone' => '966501234567',
    ]));

    $response->assertOk();
    $response->assertSee('إعادة إرسال الرمز');
});

test('phone otp settings service reads rate limit from admin settings', function () {
    $template = createOtpTemplate();
    seedOtpSettings($template, [
        'rate_limit_max_per_phone' => 7,
        'rate_limit_window_minutes' => 30,
    ]);

    $settings = app(PhoneOtpSettingsService::class)->getSettings();

    expect($settings['rate_limit_max_per_phone'])->toBe(7);
    expect($settings['rate_limit_window_minutes'])->toBe(30);
});
