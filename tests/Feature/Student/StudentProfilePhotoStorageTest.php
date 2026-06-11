<?php

namespace Tests\Feature\Student;

use App\Models\User;
use App\Services\Student\StudentProfilePhotoService;
use App\Services\Storage\StorageHelperService;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class StudentProfilePhotoStorageTest extends TestCase
{

    public function test_student_profile_photo_url_uses_profile_photos_proxy(): void
    {
        $url = student_profile_photo_url(null, 'profile-photos/student_1_test.jpg');

        $this->assertStringContainsString('profile-photos', $url);
    }

    public function test_student_profile_photo_url_returns_default_when_empty(): void
    {
        $url = student_profile_photo_url(null, null);

        $this->assertSame(student_default_avatar_url(), $url);
    }

    public function test_profile_photo_service_store_uses_failover_upload(): void
    {
        $user = User::factory()->make(['id' => 42]);
        $expectedPath = 'profile-photos/student_42_abc.jpg';

        $this->mock(StorageHelperService::class, function ($mock) use ($expectedPath) {
            $mock->shouldReceive('storeUploadedFileWithFailover')
                ->once()
                ->with('public', 'profile-photos', \Mockery::type(UploadedFile::class), 'profile-photo')
                ->andReturn($expectedPath);
        });

        $service = app(StudentProfilePhotoService::class);
        $file = UploadedFile::fake()->image('avatar.jpg');

        $path = $service->store($user, $file);

        $this->assertSame($expectedPath, $path);
    }

    public function test_profile_photo_service_delete_for_user_clears_both_paths(): void
    {
        $user = User::factory()->make([
            'photo' => 'profile-photos/old-photo.jpg',
            'avatar' => 'profile-photos/old-photo.jpg',
        ]);

        $service = $this->partialMock(StudentProfilePhotoService::class, function ($mock) {
            $mock->shouldReceive('delete')
                ->once()
                ->with('profile-photos/old-photo.jpg');
        });

        $service->deleteForUser($user);
    }
}
