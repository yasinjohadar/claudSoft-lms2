<?php

use App\Models\GamificationNotification;
use App\Models\StudentGift;
use App\Models\StudentGiftRecipient;
use App\Models\User;
use App\Services\StudentGifts\StudentGiftGrantService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

function giftTestAdmin(): User
{
    $role = Role::findOrCreate('admin', 'web');
    $admin = User::factory()->create();
    $admin->assignRole($role);

    return $admin;
}

function giftTestStudent(array $overrides = []): User
{
    $role = Role::findOrCreate('student', 'web');
    $student = User::factory()->create($overrides);
    $student->assignRole($role);

    return $student;
}

function giftTestGift(array $overrides = []): StudentGift
{
    return StudentGift::create(array_merge([
        'name' => 'Test Gift',
        'content_mode' => StudentGift::CONTENT_EXTERNAL,
        'download_url' => 'https://example.com/file.pdf',
        'target_type' => 'single',
        'target_payload' => ['user_id' => null],
        'status' => StudentGift::STATUS_DRAFT,
        'created_by' => giftTestAdmin()->id,
    ], $overrides));
}

beforeEach(function () {
    $this->grantService = app(StudentGiftGrantService::class);
});

test('grant creates recipients for single student target', function () {
    $student = giftTestStudent();
    $gift = giftTestGift([
        'target_payload' => ['user_id' => $student->id],
    ]);

    $result = $this->grantService->grant($gift, giftTestAdmin()->id);

    expect($result['granted'])->toBe(1)
        ->and($result['skipped'])->toBe(0);

    $gift->refresh();
    expect($gift->isGranted())->toBeTrue();

    expect(StudentGiftRecipient::query()->where('student_gift_id', $gift->id)->count())->toBe(1);
    expect(GamificationNotification::query()->where('user_id', $student->id)->where('type', 'gift_received')->count())->toBe(1);
});

test('regrant adds only new recipients', function () {
    $student1 = giftTestStudent();
    $student2 = giftTestStudent();
    $gift = giftTestGift([
        'target_type' => 'multiple',
        'target_payload' => ['user_ids' => [$student1->id, $student2->id]],
    ]);

    $gift->update([
        'target_payload' => ['user_ids' => [$student1->id]],
    ]);
    $this->grantService->grant($gift, giftTestAdmin()->id);

    $gift->update([
        'target_payload' => ['user_ids' => [$student1->id, $student2->id]],
    ]);

    $result = $this->grantService->regrant($gift, giftTestAdmin()->id);

    expect($result['granted'])->toBe(1)
        ->and($result['skipped'])->toBe(1);

    expect(StudentGiftRecipient::query()->where('student_gift_id', $gift->id)->count())->toBe(2);
    expect(GamificationNotification::query()->where('user_id', $student2->id)->count())->toBe(1);
    expect(GamificationNotification::query()->where('user_id', $student1->id)->count())->toBe(1);
});

test('revoke marks gift as revoked', function () {
    $student = giftTestStudent();
    $gift = giftTestGift([
        'target_payload' => ['user_id' => $student->id],
    ]);

    $this->grantService->grant($gift);
    $this->grantService->revoke($gift);

    $gift->refresh();
    expect($gift->isRevoked())->toBeTrue();
});

test('regrant after revoke restores access for existing recipients', function () {
    $student = giftTestStudent();
    $gift = giftTestGift([
        'target_payload' => ['user_id' => $student->id],
    ]);

    $this->grantService->grant($gift);
    $this->grantService->revoke($gift);

    $result = $this->grantService->regrant($gift, giftTestAdmin()->id);

    $gift->refresh();
    expect($gift->isGranted())->toBeTrue()
        ->and($result['restored'])->toBe(1)
        ->and($result['granted'])->toBe(0);
});

test('course target resolves enrolled students', function () {
    $student = giftTestStudent();
    $other = giftTestStudent();

    $courseCategoryId = DB::table('course_categories')->insertGetId([
        'name' => 'Cat '.uniqid(),
        'slug' => 'cat-'.uniqid(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $courseId = DB::table('courses')->insertGetId([
        'course_category_id' => $courseCategoryId,
        'title' => 'Course '.uniqid(),
        'slug' => 'course-'.uniqid(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('course_enrollments')->insert([
        'course_id' => $courseId,
        'student_id' => $student->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $gift = giftTestGift([
        'target_type' => 'course',
        'target_payload' => ['course_id' => $courseId],
    ]);

    $result = $this->grantService->grant($gift);

    expect($result['granted'])->toBe(1);
    expect(StudentGiftRecipient::query()->where('student_id', $other->id)->exists())->toBeFalse();
});
