<?php

namespace Tests\Feature\Api;

use App\Mail\PasswordCredentialsMail;
use App\Models\EmailSetting;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\WhatsApp\SendWhatsAppMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\Support\PasswordCredentialTestDoubles;
use Tests\TestCase;

class StudentForgotPasswordApiTest extends TestCase
{
    use RefreshDatabase;

    private function studentUser(array $overrides = []): User
    {
        $role = Role::findOrCreate('student', 'web');
        $user = User::factory()->create($overrides);
        $user->assignRole($role);

        return $user;
    }

    private function enableWhatsAppEvolution(): void
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

    private function mockWhatsAppSender(): void
    {
        PasswordCredentialTestDoubles::mockNumberResolverPassThrough();
        PasswordCredentialTestDoubles::mockAcceptedWhatsAppSender();
    }

    public function test_forgot_password_requires_valid_email(): void
    {
        $this->postJson('/api/student/forgot-password', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_forgot_password_options_returns_channels_and_country_codes(): void
    {
        $this->enableWhatsAppEvolution();

        $response = $this->getJson('/api/student/forgot-password/options');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.whatsapp_available', true)
            ->assertJsonPath('data.default_channel', 'whatsapp');

        $this->assertNotEmpty($response->json('data.country_codes'));
    }

    public function test_forgot_password_sends_credentials_using_active_smtp_settings(): void
    {
        Mail::fake();
        $this->mockWhatsAppSender();

        EmailSetting::query()->create([
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.test.example',
            'mail_port' => 587,
            'mail_username' => 'user@test.example',
            'mail_password' => 'secret',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@test.example',
            'mail_from_name' => 'Test Academy',
            'is_active' => true,
            'provider' => 'custom',
        ]);

        $student = $this->studentUser();
        $oldHash = $student->password;

        $response = $this->postJson('/api/student/forgot-password', [
            'channel' => 'email',
            'email' => $student->email,
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('data.channel', 'email');

        Mail::assertSent(PasswordCredentialsMail::class, function (PasswordCredentialsMail $mail) use ($student) {
            return $mail->hasTo($student->email);
        });

        $student->refresh();
        $this->assertNotSame($oldHash, $student->password);

        $this->assertSame('smtp.test.example', config('mail.mailers.smtp.host'));
        $this->assertSame('noreply@test.example', config('mail.from.address'));
    }

    public function test_forgot_password_sends_whatsapp_credentials_via_evolution_provider(): void
    {
        Mail::fake();
        $this->enableWhatsAppEvolution();
        PasswordCredentialTestDoubles::mockNumberResolverPassThrough();

        $student = $this->studentUser([
            'country_code' => '+963',
            'phone' => '991234567',
            'full_phone' => '+963991234567',
        ]);

        PasswordCredentialTestDoubles::mockAcceptedWhatsAppSender(function (string $to, string $text) {
            return $to === '+963991234567' && str_contains($text, 'student@');
        });

        $response = $this->postJson('/api/student/forgot-password', [
            'channel' => 'whatsapp',
            'country_code' => '+963',
            'phone' => '991234567',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.channel', 'whatsapp')
            ->assertJsonPath('data.contact', '+963991234567');

        Mail::assertSent(PasswordCredentialsMail::class);
    }

    public function test_reset_password_sends_credentials_after_manual_reset(): void
    {
        Mail::fake();
        $this->mockWhatsAppSender();

        $student = $this->studentUser();
        $token = Password::broker()->createToken($student);

        $response = $this->postJson('/api/student/reset-password', [
            'token' => $token,
            'email' => $student->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true);

        Mail::assertSent(PasswordCredentialsMail::class);
    }

    public function test_forgot_password_returns_error_for_unknown_email(): void
    {
        Mail::fake();

        $this->postJson('/api/student/forgot-password', [
            'channel' => 'email',
            'email' => 'missing@example.com',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        Mail::assertNothingSent();
    }
}
