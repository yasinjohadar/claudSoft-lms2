<?php

use App\Models\CampEnrollment;
use App\Models\SiteSetting;
use App\Models\StudentProfileCard;
use App\Models\TrainingCamp;
use App\Models\User;
use App\Services\Student\StudentProfileCardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function profileCardStudent(array $attrs = []): User
{
    Role::findOrCreate('student', 'web');

    return User::factory()->create(array_merge([
        'is_active' => true,
        'name' => 'John Student',
        'name_ar' => 'جون طالب',
        'email' => 'student-'.uniqid().'@example.com',
    ], $attrs))->tap(fn (User $user) => $user->assignRole('student'));
}

function profileCardAdmin(): User
{
    Role::findOrCreate('admin', 'web');

    return User::factory()->create([
        'is_active' => true,
        'email' => 'admin-'.uniqid().'@example.com',
    ])->tap(fn (User $user) => $user->assignRole('admin'));
}

function enableProfileCardForGold(): void
{
    SiteSetting::setValue('profile_card_enabled_gold', true);
    SiteSetting::setValue('profile_card_enabled_silver', false);
}

function makeGoldStudent(): User
{
    $student = profileCardStudent();
    $camp = TrainingCamp::create([
        'name' => 'Test Camp',
        'slug' => 'test-camp-'.uniqid(),
        'description' => 'Test',
        'price' => 0,
        'start_date' => now()->subDay(),
        'end_date' => now()->addMonth(),
        'is_active' => true,
    ]);
    CampEnrollment::create([
        'camp_id' => $camp->id,
        'student_id' => $student->id,
        'status' => 'approved',
        'enrollment_date' => now(),
    ]);

    return $student;
}

function createPublicCard(User $student, array $overrides = []): StudentProfileCard
{
    $service = app(StudentProfileCardService::class);
    $card = $service->getOrCreateForUser($student);
    $card->update(array_merge([
        'is_public' => true,
        'admin_enabled' => true,
        'job_title' => 'مطور',
        'bio' => 'نبذة تجريبية',
    ], $overrides));

    return $card->fresh();
}

test('public profile card is accessible without auth when enabled', function () {
    enableProfileCardForGold();
    $student = makeGoldStudent();
    $card = createPublicCard($student);

    $this->get(route('frontend.profile-card.show', $card->slug))
        ->assertSuccessful()
        ->assertSee('جون طالب')
        ->assertSee('مطور');
});

test('public profile card returns 404 when hidden', function () {
    enableProfileCardForGold();
    $student = makeGoldStudent();
    $card = createPublicCard($student, ['is_public' => false]);

    $this->get(route('frontend.profile-card.show', $card->slug))
        ->assertNotFound();
});

test('public profile card returns 404 when admin disabled', function () {
    enableProfileCardForGold();
    $student = makeGoldStudent();
    $card = createPublicCard($student, ['admin_enabled' => false]);

    $this->get(route('frontend.profile-card.show', $card->slug))
        ->assertNotFound();
});

test('silver student cannot access edit when feature disabled for silver', function () {
    enableProfileCardForGold();
    $student = profileCardStudent();

    $this->actingAs($student)
        ->get(route('student.profile-card.edit'))
        ->assertSuccessful()
        ->assertSee('غير متاحة');
});

test('gold student can access profile card edit page', function () {
    enableProfileCardForGold();
    $student = makeGoldStudent();

    $this->actingAs($student)
        ->get(route('student.profile-card.edit'))
        ->assertSuccessful()
        ->assertSee('بطاقتي التعريفية');
});

test('student can update profile card slug and data', function () {
    enableProfileCardForGold();
    $student = makeGoldStudent();
    app(StudentProfileCardService::class)->getOrCreateForUser($student);

    $this->actingAs($student)
        ->put(route('student.profile-card.update'), [
            'slug' => 'my-custom-slug',
            'job_title' => 'Backend Developer',
            'bio' => 'Hello world',
            'is_public' => '1',
            'qr_enabled' => '1',
            'theme' => ['preset' => 'classic', 'accent_color' => '#3b82f6'],
            'social_links' => [
                [
                    'platform' => 'github',
                    'url' => 'https://github.com/test',
                    'label' => 'GitHub',
                    'icon' => 'fab fa-github',
                    'enabled' => '1',
                    'sort_order' => 0,
                ],
            ],
        ])
        ->assertRedirect(route('student.profile-card.edit'));

    $card = $student->fresh()->profileCard;
    expect($card->slug)->toBe('my-custom-slug')
        ->and($card->job_title)->toBe('Backend Developer')
        ->and($card->is_public)->toBeTrue();
});

test('admin can toggle profile card admin_enabled', function () {
    enableProfileCardForGold();
    $admin = profileCardAdmin();
    $student = makeGoldStudent();
    $card = createPublicCard($student);

    $this->actingAs($admin)
        ->post(route('admin.student-profile-cards.toggle-admin-enabled', $card))
        ->assertJson(['success' => true, 'admin_enabled' => false]);

    expect($card->fresh()->admin_enabled)->toBeFalse();

    $this->get(route('frontend.profile-card.show', $card->slug))
        ->assertNotFound();
});

test('slug must be unique across cards', function () {
    enableProfileCardForGold();
    $student1 = makeGoldStudent();
    $student2 = makeGoldStudent();
    createPublicCard($student1, ['slug' => 'taken-slug']);

    $this->actingAs($student2)
        ->put(route('student.profile-card.update'), [
            'slug' => 'taken-slug',
            'is_public' => '0',
            'qr_enabled' => '1',
        ])
        ->assertSessionHasErrors('slug');
});
