<?php

namespace Tests\Feature\Admin;

use App\Mail\PasswordCredentialsMail;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\WhatsApp\SendWhatsAppMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserUpdatePasswordTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $role = Role::findOrCreate('admin', 'web');
        Permission::findOrCreate('user-edit', 'web');
        $role->givePermissionTo('user-edit');

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function studentUser(): User
    {
        $role = Role::findOrCreate('student', 'web');
        $user = User::factory()->create([
            'country_code' => '+963',
            'phone' => '991234567',
            'full_phone' => '+963991234567',
        ]);
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

    public function test_admin_password_update_sends_credentials_when_enabled(): void
    {
        Mail::fake();
        $this->enableWhatsAppEvolution();

        $mock = Mockery::mock(SendWhatsAppMessage::class);
        $mock->shouldReceive('sendTextSync')
            ->once()
            ->andReturn(new \App\Models\WhatsAppMessage());
        $this->app->instance(SendWhatsAppMessage::class, $mock);

        $admin = $this->adminUser();
        $student = $this->studentUser();

        $response = $this->actingAs($admin)->putJson(route('users.update-password', $student), [
            'password' => 'AdminPass123!@#',
            'password_confirmation' => 'AdminPass123!@#',
            'send_credentials' => '1',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'تم تحديث كلمة المرور بنجاح');

        Mail::assertSent(PasswordCredentialsMail::class, function (PasswordCredentialsMail $mail) use ($student) {
            return $mail->hasTo($student->email);
        });
    }

    public function test_admin_password_update_skips_credentials_when_disabled(): void
    {
        Mail::fake();
        $this->enableWhatsAppEvolution();

        $mock = Mockery::mock(SendWhatsAppMessage::class);
        $mock->shouldNotReceive('sendTextSync');
        $this->app->instance(SendWhatsAppMessage::class, $mock);

        $admin = $this->adminUser();
        $student = $this->studentUser();

        $this->actingAs($admin)->putJson(route('users.update-password', $student), [
            'password' => 'AdminPass123!@#',
            'password_confirmation' => 'AdminPass123!@#',
            'send_credentials' => '0',
        ])->assertOk();

        Mail::assertNothingSent();
    }
}
