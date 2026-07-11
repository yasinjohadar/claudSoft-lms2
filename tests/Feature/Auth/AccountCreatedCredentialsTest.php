<?php

use App\Mail\AccountCreatedCredentialsMail;
use App\Models\CourseGroup;
use App\Models\GroupRegistrationSetting;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Auth\AccountCreatedCredentialDeliveryService;
use App\Services\Auth\AccountCreatedMessageRenderer;
use App\Services\Auth\AccountCreatedMessageSettingsService;
use App\Services\GroupRegistrationService;
use App\Services\RegistrationEmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\Support\PasswordCredentialTestDoubles;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('student', 'web');
});

function makeRegistrationGroup(array $settingOverrides = []): CourseGroup
{
    $group = CourseGroup::create([
        'name' => 'مجموعة اختبار التسجيل',
        'is_active' => true,
    ]);

    GroupRegistrationSetting::create(array_merge([
        'group_id' => $group->id,
        'is_registration_enabled' => true,
        'auto_create_user' => true,
        'auto_approve_membership' => true,
        'send_welcome_email' => true,
        'send_welcome_whatsapp' => true,
        'require_email_verification' => false,
    ], $settingOverrides));

    return $group;
}

function enableWhatsAppEvolutionForAccountCreatedTests(): void
{
    SystemSetting::create([
        'group' => 'whatsapp',
        'key' => 'whatsapp_enabled',
        'value' => '1',
    ]);
    SystemSetting::create([
        'group' => 'whatsapp',
        'key' => 'whatsapp_provider',
        'value' => 'evolution',
    ]);
    SystemSetting::create([
        'group' => 'whatsapp',
        'key' => 'evolution_base_url',
        'value' => 'https://evolution.test.example',
    ]);
    SystemSetting::create([
        'group' => 'whatsapp',
        'key' => 'evolution_api_key',
        'value' => 'test-api-key',
    ]);
    SystemSetting::create([
        'group' => 'whatsapp',
        'key' => 'evolution_instance_name',
        'value' => 'main',
    ]);
}

test('group registration creates user and delivers credentials with password', function () {
    Mail::fake();
    enableWhatsAppEvolutionForAccountCreatedTests();
    PasswordCredentialTestDoubles::mockNumberResolverPassThrough();

    $capturedPassword = null;
    PasswordCredentialTestDoubles::mockAcceptedWhatsAppSender(function (string $to, string $text) use (&$capturedPassword) {
        if (preg_match('/كلمة المرور:\s*(.+)/u', $text, $matches)) {
            $capturedPassword = trim($matches[1]);
        }

        return $to === '+963991234567'
            && str_contains($text, 'new-reg@example.com')
            && str_contains($text, 'ياسين')
            && ! str_contains($text, '{password}');
    });

    $group = makeRegistrationGroup();

    $registration = app(GroupRegistrationService::class)->createRegistration([
        'group_id' => $group->id,
        'name' => 'Yasin Jokhadar',
        'name_ar' => 'ياسين جوخدار',
        'email' => 'new-reg@example.com',
        'phone' => '991234567',
        'country_code' => '+963',
    ]);

    $registration->refresh();

    expect($registration->user_created)->toBeTrue()
        ->and($registration->user_id)->not->toBeNull()
        ->and($registration->status)->toBe('completed');

    $user = User::where('email', 'new-reg@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->name_ar)->toBe('ياسين جوخدار');

    Mail::assertSent(AccountCreatedCredentialsMail::class, function (AccountCreatedCredentialsMail $mail) use ($user) {
        return $mail->hasTo($user->email);
    });

    expect($capturedPassword)->not->toBeNull()
        ->and(Hash::check($capturedPassword, $user->fresh()->password))->toBeTrue();
});

test('group registration for existing user does not send account credentials mail', function () {
    Mail::fake();
    PasswordCredentialTestDoubles::mockNumberResolverPassThrough();
    PasswordCredentialTestDoubles::mockAcceptedWhatsAppSender();

    $existing = User::factory()->create([
        'email' => 'existing@example.com',
        'name' => 'Existing',
        'name_ar' => 'موجود',
        'country_code' => '+963',
        'phone' => '991111111',
        'full_phone' => '+963991111111',
    ]);
    $existing->assignRole('student');

    $group = makeRegistrationGroup([
        'send_welcome_whatsapp' => false,
    ]);

    $emailService = \Mockery::mock(RegistrationEmailService::class);
    $emailService->shouldReceive('sendWelcomeEmailForGroup')->once()->andReturn(true);
    app()->instance(RegistrationEmailService::class, $emailService);

    $registration = app(GroupRegistrationService::class)->createRegistration([
        'group_id' => $group->id,
        'name' => 'Existing',
        'name_ar' => 'موجود',
        'email' => 'existing@example.com',
        'phone' => '991111111',
        'country_code' => '+963',
    ]);

    $registration->refresh();

    expect($registration->user_created)->toBeFalse()
        ->and($registration->user_id)->toBe($existing->id);

    Mail::assertNotSent(AccountCreatedCredentialsMail::class);
});

test('account created settings persist admin instructions', function () {
    $service = app(AccountCreatedMessageSettingsService::class);
    $service->updateSettings([
        'admin_instructions' => 'تعليمات مخصصة للاختبار',
        'email_subject' => 'موضوع مخصص',
        'whatsapp_body' => 'مرحبا {student_name_ar} كلمة المرور {password}',
        'email_body' => '<p>{admin_instructions}</p>',
        'whatsapp_template_id' => '',
    ]);

    $settings = $service->getSettings();

    expect($settings['admin_instructions'])->toBe('تعليمات مخصصة للاختبار')
        ->and($settings['email_subject'])->toBe('موضوع مخصص');

    expect(SystemSetting::where('group', AccountCreatedMessageSettingsService::GROUP)
        ->where('key', 'admin_instructions')
        ->exists())->toBeTrue();
});
