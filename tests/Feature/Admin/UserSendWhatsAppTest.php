<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\WhatsAppMessageTemplate;
use App\Services\WhatsApp\SendWhatsAppMessage;
use App\Services\WhatsApp\UserSendWhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserSendWhatsAppTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $role = Role::findOrCreate('admin', 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function studentUser(array $overrides = []): User
    {
        $role = Role::findOrCreate('student', 'web');
        $user = User::factory()->create(array_merge([
            'email' => 'student'.uniqid().'@example.com',
            'name' => 'Test Student',
            'name_ar' => 'طالب اختبار',
            'country_code' => '+963',
            'phone' => '991234567',
            'full_phone' => '+963991234567',
        ], $overrides));
        $user->assignRole($role);

        return $user;
    }

    private function createTemplate(): WhatsAppMessageTemplate
    {
        return WhatsAppMessageTemplate::create([
            'name' => 'Welcome WhatsApp',
            'slug' => 'welcome_user',
            'body' => 'مرحباً {{student_name}}، رقمك: {{phone}}',
            'type' => WhatsAppMessageTemplate::TYPE_TEXT,
            'language' => 'ar',
            'is_active' => true,
        ]);
    }

    public function test_preview_returns_rendered_body(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();
        $template = $this->createTemplate();

        $response = $this->actingAs($admin)->postJson(
            route('users.send-whatsapp.preview', $student),
            ['whatsapp_template_id' => $template->id]
        );

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['body' => 'مرحباً طالب اختبار، رقمك: +963991234567']);
    }

    public function test_admin_can_send_whatsapp_to_user_with_template(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();
        $template = $this->createTemplate();

        $mock = Mockery::mock(SendWhatsAppMessage::class);
        $mock->shouldReceive('sendTextSync')
            ->once()
            ->with('+963991234567', 'مرحباً طالب اختبار، رقمك: +963991234567')
            ->andReturn(new \App\Models\WhatsAppMessage());

        $this->app->instance(SendWhatsAppMessage::class, $mock);

        $response = $this->actingAs($admin)->postJson(
            route('users.send-whatsapp.send', $student),
            ['whatsapp_template_id' => $template->id]
        );

        $response->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_send_returns_422_when_user_has_no_phone(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser([
            'country_code' => null,
            'phone' => null,
            'full_phone' => null,
        ]);
        $template = $this->createTemplate();

        $response = $this->actingAs($admin)->postJson(
            route('users.send-whatsapp.send', $student),
            ['whatsapp_template_id' => $template->id]
        );

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_send_requires_whatsapp_template_id(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();

        $response = $this->actingAs($admin)->postJson(
            route('users.send-whatsapp.send', $student),
            []
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['whatsapp_template_id']);
    }
}
