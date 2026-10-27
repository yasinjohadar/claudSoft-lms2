<?php

namespace Tests\Feature\Api\Mobile\V1;

use App\Services\Student\StudentProfilePhotoService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

class MobileProfileApiTest extends MobileApiMysqlTestCase
{
    public function test_show_profile(): void
    {
        $student = $this->student(['name_ar' => 'طالب']);
        Sanctum::actingAs($student);

        $this->getJson('/api/mobile/v1/profile')
            ->assertOk()
            ->assertJsonPath('data.id', $student->id)
            ->assertJsonPath('data.name_ar', 'طالب');
    }

    public function test_avatar_uses_the_photo_whose_file_exists(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('users/photos/admin-upload.png', 'png');

        // `photo` points to a deleted file, `avatar` (set from the admin panel) exists.
        $student = $this->student(['photo' => 'profile-photos/missing.jpg', 'avatar' => 'users/photos/admin-upload.png']);
        Sanctum::actingAs($student);

        $response = $this->getJson('/api/mobile/v1/profile')->assertOk()->assertJsonPath('data.has_avatar', true);
        $this->assertStringContainsString('users/photos/admin-upload.png', $response->json('data.avatar_url'));

        // Neither file exists: no broken image, the app shows the initial instead.
        $student->forceFill(['avatar' => 'users/photos/gone.png'])->save();
        $this->getJson('/api/mobile/v1/profile')->assertOk()->assertJsonPath('data.has_avatar', false);
    }

    public function test_update_profile_keeping_same_phone(): void
    {
        $student = $this->completeStudent();
        Sanctum::actingAs($student);

        $this->putJson('/api/mobile/v1/profile', [
            'name' => 'New Name',
            'name_ar' => 'اسم جديد',
            'city' => 'دمشق',
            'gender' => 'male',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.city', 'دمشق');
    }

    public function test_update_profile_validates_input(): void
    {
        Sanctum::actingAs($this->student());

        $this->putJson('/api/mobile/v1/profile', ['name' => '', 'gender' => 'x'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'gender']);
    }

    public function test_upload_and_delete_avatar(): void
    {
        $student = $this->student();
        Sanctum::actingAs($student);
        Storage::fake('public');
        Storage::disk('public')->put('profile-photos/student_test.jpg', 'jpg');

        $this->mock(StudentProfilePhotoService::class, function ($mock) {
            $mock->shouldReceive('store')->once()->andReturn('profile-photos/student_test.jpg');
            $mock->shouldReceive('deleteForUser')->once();
        });

        $this->postJson('/api/mobile/v1/profile/avatar', [
            'avatar' => UploadedFile::fake()->image('a.jpg'),
        ])->assertOk()->assertJsonPath('data.has_avatar', true);

        $this->assertSame('profile-photos/student_test.jpg', $student->fresh()->photo);

        $this->deleteJson('/api/mobile/v1/profile/avatar')
            ->assertOk()
            ->assertJsonPath('data.has_avatar', false);

        $this->assertNull($student->fresh()->photo);
    }

    public function test_change_password_revokes_other_tokens(): void
    {
        $student = $this->student();
        $student->createToken('mobile:other');
        $current = $student->createToken('mobile:current')->plainTextToken;

        $this->withToken($current)->putJson('/api/mobile/v1/profile/password', [
            'new_password' => 'NewPass#2026',
            'new_password_confirmation' => 'NewPass#2026',
        ])->assertOk();

        $this->assertTrue(Hash::check('NewPass#2026', $student->fresh()->password));
        $this->assertSame(['mobile:current'], $student->tokens()->pluck('name')->all());
    }

    public function test_change_password_rejects_weak_password(): void
    {
        Sanctum::actingAs($this->student());

        $this->putJson('/api/mobile/v1/profile/password', [
            'new_password' => '123',
            'new_password_confirmation' => '123',
        ])->assertUnprocessable()->assertJsonValidationErrors(['new_password']);
    }
}
