<?php

use App\Models\Nationality;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function incompleteProfileStudent(): User
{
    Role::findOrCreate('student', 'web');

    return User::factory()->create([
        'is_active' => true,
        'name' => null,
        'name_ar' => null,
        'phone' => null,
        'country_code' => null,
        'date_of_birth' => null,
        'gender' => null,
        'city' => null,
        'address' => null,
        'nationality_id' => null,
    ])->tap(fn (User $user) => $user->assignRole('student'));
}

function completeProfileStudent(): User
{
    Role::findOrCreate('student', 'web');
    $nationality = Nationality::create(['name' => 'السعودية']);

    return User::factory()->create([
        'is_active' => true,
        'name' => 'John Doe',
        'name_ar' => 'جون دو',
        'email' => 'complete-'.uniqid().'@example.com',
        'phone' => '501234567',
        'country_code' => '+966',
        'date_of_birth' => '2000-01-01',
        'gender' => 'male',
        'city' => 'Riyadh',
        'address' => 'Test address',
        'nationality_id' => $nationality->id,
    ])->tap(fn (User $user) => $user->assignRole('student'));
}

function enableForcedProfileCompletion(): void
{
    SiteSetting::setValue(
        'force_student_profile_completion',
        true,
        'إجبار الطلاب على إكمال ملفهم الشخصي 100% قبل استخدام المنصة'
    );
}

test('student with incomplete profile can access dashboard when setting is disabled', function () {
    $student = incompleteProfileStudent();

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertSuccessful();
});

test('student with incomplete profile is redirected to profile edit when setting is enabled', function () {
    enableForcedProfileCompletion();
    $student = incompleteProfileStudent();

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertRedirect(route('student.profile.edit'));
});

test('student with incomplete profile can access profile edit when setting is enabled', function () {
    enableForcedProfileCompletion();
    $student = incompleteProfileStudent();

    $this->actingAs($student)
        ->get(route('student.profile.edit'))
        ->assertSuccessful();
});

test('student with complete profile can access dashboard when setting is enabled', function () {
    enableForcedProfileCompletion();
    $student = completeProfileStudent();

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertSuccessful();
});

test('api blocks incomplete student when setting is enabled', function () {
    enableForcedProfileCompletion();
    $student = incompleteProfileStudent();

    $this->actingAs($student, 'sanctum')
        ->getJson('/api/student/dashboard')
        ->assertForbidden()
        ->assertJsonPath('profile_completion_required', true);
});

test('api allows profile update route for incomplete student when setting is enabled', function () {
    enableForcedProfileCompletion();
    $student = incompleteProfileStudent();

    $this->actingAs($student, 'sanctum')
        ->getJson('/api/student/profile')
        ->assertSuccessful();
});

test('impersonation bypasses profile completion lock', function () {
    enableForcedProfileCompletion();
    $student = incompleteProfileStudent();

    $this->actingAs($student)
        ->withSession(['impersonate' => ['original_user_id' => 1]])
        ->get(route('student.dashboard'))
        ->assertSuccessful();
});
