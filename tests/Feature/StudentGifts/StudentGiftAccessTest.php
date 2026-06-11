<?php

use App\Models\StudentGift;
use App\Models\StudentGiftRecipient;
use App\Models\User;
use Spatie\Permission\Models\Role;

function giftAccessStudent(): User
{
    $role = Role::findOrCreate('student', 'web');
    $student = User::factory()->create();
    $student->assignRole($role);

    return $student;
}

function giftAccessOtherStudent(): User
{
    return giftAccessStudent();
}

function giftAccessGrantedGift(User $student): StudentGiftRecipient
{
    $gift = StudentGift::create([
        'name' => 'Access Gift',
        'content_mode' => StudentGift::CONTENT_EXTERNAL,
        'preview_url' => 'https://example.com/preview',
        'download_url' => 'https://example.com/download',
        'target_type' => 'single',
        'target_payload' => ['user_id' => $student->id],
        'status' => StudentGift::STATUS_GRANTED,
        'granted_at' => now(),
    ]);

    return StudentGiftRecipient::create([
        'student_gift_id' => $gift->id,
        'student_id' => $student->id,
        'granted_at' => now(),
    ]);
}

test('authorized student can view gifts index', function () {
    $student = giftAccessStudent();
    giftAccessGrantedGift($student);

    $this->actingAs($student)
        ->get(route('student.gifts.index'))
        ->assertOk()
        ->assertSee('Access Gift');
});

test('student cannot preview another students gift', function () {
    $student = giftAccessStudent();
    $other = giftAccessOtherStudent();
    $recipient = giftAccessGrantedGift($other);

    $this->actingAs($student)
        ->get(route('student.gifts.preview', $recipient))
        ->assertForbidden();
});

test('student can preview own external gift', function () {
    $student = giftAccessStudent();
    $recipient = giftAccessGrantedGift($student);

    $this->actingAs($student)
        ->get(route('student.gifts.preview', $recipient))
        ->assertRedirect('https://example.com/preview');
});

test('revoked gift returns forbidden', function () {
    $student = giftAccessStudent();
    $recipient = giftAccessGrantedGift($student);
    $recipient->gift->update(['status' => StudentGift::STATUS_REVOKED]);

    $this->actingAs($student)
        ->get(route('student.gifts.download', $recipient))
        ->assertForbidden();
});
