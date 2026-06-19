<?php

namespace Tests\Feature\Admin;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserSendEmailTest extends TestCase
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
        ], $overrides));
        $user->assignRole($role);

        return $user;
    }

    private function createTemplate(): EmailTemplate
    {
        return EmailTemplate::create([
            'name' => 'Welcome Template',
            'name_ar' => 'قالب ترحيب',
            'subject' => 'مرحباً {{student_name}}',
            'body' => '<p>أهلاً {{student_name}}، بريدك: {{email}}</p>',
            'type' => EmailTemplate::TYPE_CUSTOM,
            'is_active' => true,
        ]);
    }

    public function test_preview_returns_rendered_subject_and_body(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();
        $template = $this->createTemplate();

        $response = $this->actingAs($admin)->postJson(
            route('users.send-email.preview', $student),
            ['email_template_id' => $template->id]
        );

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('subject', 'مرحباً طالب اختبار')
            ->assertJsonFragment(['body' => '<p>أهلاً طالب اختبار، بريدك: '.$student->email.'</p>']);
    }

    public function test_admin_can_send_email_to_user_with_template(): void
    {
        Mail::fake();

        $admin = $this->adminUser();
        $student = $this->studentUser();
        $template = $this->createTemplate();

        $response = $this->actingAs($admin)->postJson(
            route('users.send-email.send', $student),
            ['email_template_id' => $template->id]
        );

        $response->assertOk()
            ->assertJsonPath('success', true);

        Mail::assertSentCount(1);
    }

    public function test_send_returns_422_when_user_has_no_email(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser(['email' => null]);
        $template = $this->createTemplate();

        $response = $this->actingAs($admin)->postJson(
            route('users.send-email.send', $student),
            ['email_template_id' => $template->id]
        );

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_send_requires_email_template_id(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();

        $response = $this->actingAs($admin)->postJson(
            route('users.send-email.send', $student),
            []
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email_template_id']);
    }
}
