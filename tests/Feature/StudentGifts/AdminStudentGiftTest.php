<?php

use App\Models\StudentGift;
use App\Models\User;
use Spatie\Permission\Models\Role;

function giftAdminUser(): User
{
    $role = Role::findOrCreate('admin', 'web');
    $admin = User::factory()->create();
    $admin->assignRole($role);

    return $admin;
}

function giftAdminStudent(): User
{
    $role = Role::findOrCreate('student', 'web');
    $student = User::factory()->create();
    $student->assignRole($role);

    return $student;
}

test('admin can access gifts index', function () {
    $this->actingAs(giftAdminUser())
        ->get(route('admin.gifts.index'))
        ->assertOk();
});

test('admin can create external gift draft', function () {
    $admin = giftAdminUser();
    $student = giftAdminStudent();

    $response = $this->actingAs($admin)->post(route('admin.gifts.store'), [
        'name' => 'New Gift',
        'description' => 'Desc',
        'content_mode' => 'external',
        'download_url' => 'https://example.com/file.zip',
        'preview_url' => 'https://example.com/preview',
        'target_type' => 'single',
        'user_id' => $student->id,
    ]);

    $gift = StudentGift::query()->where('name', 'New Gift')->first();
    expect($gift)->not->toBeNull()
        ->and($gift->isDraft())->toBeTrue()
        ->and($gift->target_payload['user_id'])->toBe($student->id);

    $response->assertRedirect(route('admin.gifts.show', $gift));
});

test('student search returns empty without search term or ids', function () {
    giftAdminStudent();

    $this->actingAs(giftAdminUser())
        ->getJson(route('admin.gifts.search-students'))
        ->assertOk()
        ->assertJson(['results' => []]);
});

test('student search returns results by ids', function () {
    $student = giftAdminStudent();

    $this->actingAs(giftAdminUser())
        ->getJson(route('admin.gifts.search-students', ['ids' => $student->id]))
        ->assertOk()
        ->assertJsonPath('results.0.id', $student->id);
});

test('admin can regrant after revoke', function () {
    $admin = giftAdminUser();
    $student = giftAdminStudent();

    $gift = StudentGift::create([
        'name' => 'Revoked Gift',
        'content_mode' => StudentGift::CONTENT_EXTERNAL,
        'download_url' => 'https://example.com/d',
        'target_type' => 'single',
        'target_payload' => ['user_id' => $student->id],
        'status' => StudentGift::STATUS_REVOKED,
        'granted_at' => now(),
        'created_by' => $admin->id,
    ]);

    $gift->recipients()->create([
        'student_id' => $student->id,
        'granted_at' => now(),
    ]);

    $this->actingAs($admin)
        ->post(route('admin.gifts.regrant', $gift))
        ->assertRedirect(route('admin.gifts.show', $gift));

    $gift->refresh();
    expect($gift->isGranted())->toBeTrue();
});

test('admin can grant gift from show flow', function () {
    $admin = giftAdminUser();
    $student = giftAdminStudent();

    $gift = StudentGift::create([
        'name' => 'Grant Me',
        'content_mode' => StudentGift::CONTENT_EXTERNAL,
        'download_url' => 'https://example.com/d',
        'target_type' => 'single',
        'target_payload' => ['user_id' => $student->id],
        'status' => StudentGift::STATUS_DRAFT,
        'created_by' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.gifts.grant', $gift))
        ->assertRedirect(route('admin.gifts.show', $gift));

    $gift->refresh();
    expect($gift->isGranted())->toBeTrue();
    expect($gift->recipients()->where('student_id', $student->id)->exists())->toBeTrue();
});
