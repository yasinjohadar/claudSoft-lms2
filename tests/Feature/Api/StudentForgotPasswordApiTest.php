<?php

namespace Tests\Feature\Api;

use App\Models\EmailSetting;
use App\Models\SystemSetting;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Services\WhatsApp\SendWhatsAppMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Spatie\Permission\Models\Role;
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

    public function test_forgot_password_sends_reset_notification_using_active_smtp_settings(): void
    {
        Notification::fake();

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

        $response = $this->postJson('/api/student/forgot-password', [
            'channel' => 'email',
            'email' => $student->email,
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('message', 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.')
            ->assertJsonPath('data.channel', 'email');

        Notification::assertSentTo($student, ResetPasswordNotification::class);

        $this->assertSame('smtp.test.example', config('mail.mailers.smtp.host'));
        $this->assertSame('noreply@test.example', config('mail.from.address'));
    }

    public function test_forgot_password_sends_whatsapp_reset_link_via_evolution_provider(): void
    {
        $this->enableWhatsAppEvolution();

        $student = $this->studentUser([
            'country_code' => '+963',
            'phone' => '991234567',
            'full_phone' => '+963991234567',
        ]);

        $mock = Mockery::mock(SendWhatsAppMessage::class);
        $mock->shouldReceive('sendTextSync')
            ->once()
            ->withArgs(function (string $to, string $text) {
                return $to === '+963991234567' && str_contains($text, 'http');
            })
            ->andReturn(new \App\Models\WhatsAppMessage());

        $this->app->instance(SendWhatsAppMessage::class, $mock);

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
    }

    public function test_forgot_password_returns_error_for_unknown_email(): void
    {
        Notification::fake();

        $this->postJson('/api/student/forgot-password', [
            'channel' => 'email',
            'email' => 'missing@example.com',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        Notification::assertNothingSent();
    }
}
