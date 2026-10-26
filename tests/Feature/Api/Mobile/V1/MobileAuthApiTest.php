<?php

namespace Tests\Feature\Api\Mobile\V1;

use App\Models\User;
use Spatie\Permission\Models\Role;

class MobileAuthApiTest extends MobileApiMysqlTestCase
{
    public function test_student_can_login_and_receives_token(): void
    {
        $student = $this->student(['password' => 'Secret#123']);

        $this->postJson('/api/mobile/v1/auth/login', [
            'email' => $student->email,
            'password' => 'Secret#123',
            'device_name' => 'pixel',
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.id', $student->id)
            ->assertJsonStructure(['data' => ['token', 'token_type', 'user' => ['avatar_url', 'profile_completion']]]);

        $this->assertDatabaseHas('personal_access_tokens', ['tokenable_id' => $student->id, 'name' => 'mobile:pixel']);
    }

    public function test_wrong_password_returns_validation_error(): void
    {
        $student = $this->student(['password' => 'Secret#123']);

        $this->postJson('/api/mobile/v1/auth/login', ['email' => $student->email, 'password' => 'wrong'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_non_student_cannot_login(): void
    {
        $admin = User::factory()->create(['password' => 'Secret#123', 'is_active' => true]);
        $admin->assignRole(Role::findOrCreate('admin', 'web'));

        $this->postJson('/api/mobile/v1/auth/login', ['email' => $admin->email, 'password' => 'Secret#123'])
            ->assertForbidden()
            ->assertJsonPath('message', __('mobile.students_only'));

        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $admin->id]);
    }

    public function test_inactive_student_cannot_login(): void
    {
        $student = $this->student(['password' => 'Secret#123', 'is_active' => false]);

        $this->postJson('/api/mobile/v1/auth/login', ['email' => $student->email, 'password' => 'Secret#123'])
            ->assertForbidden();
    }

    public function test_non_student_token_is_rejected_on_protected_routes(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $token = $admin->createToken('x')->plainTextToken;

        $this->withToken($token)->getJson('/api/mobile/v1/auth/me')->assertForbidden();
    }

    public function test_me_and_logout(): void
    {
        $student = $this->student();
        $token = $student->createToken('mobile:test')->plainTextToken;

        $this->withToken($token)->getJson('/api/mobile/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $student->email);

        $this->withToken($token)->postJson('/api/mobile/v1/auth/logout')->assertOk();

        $this->assertSame(0, $student->tokens()->count());
    }

    public function test_guest_gets_401(): void
    {
        $this->getJson('/api/mobile/v1/auth/me')->assertUnauthorized();
    }
}
