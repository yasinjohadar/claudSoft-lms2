<?php

namespace Tests\Feature\Admin;

use App\Models\CourseGroup;
use App\Models\CourseGroupMember;
use App\Models\TrainingCamp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentProfileEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $role = Role::findOrCreate('admin', 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function studentUser(): User
    {
        $role = Role::findOrCreate('student', 'web');
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function createGroup(string $name = 'مجموعة اختبار'): CourseGroup
    {
        return CourseGroup::create([
            'name' => $name,
            'is_active' => true,
        ]);
    }

    private function createCamp(string $name = 'معسكر اختبار'): TrainingCamp
    {
        $suffix = uniqid();

        return TrainingCamp::create([
            'name' => $name.' '.$suffix,
            'slug' => 'test-camp-'.$suffix,
            'start_date' => now()->addDays(7)->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'price' => 250,
            'is_active' => true,
        ]);
    }

    public function test_ajax_add_to_group_returns_row_and_creates_membership(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();
        $group = $this->createGroup();

        $response = $this->actingAs($admin)->postJson(route('users.add-to-group', $student->id), [
            'group_id' => $group->id,
            'role' => 'member',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['message', 'row_html', 'stats' => ['total'], 'group_id']);

        $this->assertDatabaseHas('course_group_members', [
            'group_id' => $group->id,
            'student_id' => $student->id,
            'role' => 'member',
        ]);
    }

    public function test_ajax_add_duplicate_group_returns_422(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();
        $group = $this->createGroup();

        $group->addMember($student, 'member');

        $response = $this->actingAs($admin)->postJson(route('users.add-to-group', $student->id), [
            'group_id' => $group->id,
            'role' => 'member',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'الطالب موجود بالفعل في هذه المجموعة');
    }

    public function test_ajax_add_to_camp_returns_row_and_creates_enrollment_with_invoice(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();
        $camp = $this->createCamp();

        $response = $this->actingAs($admin)->postJson(route('users.add-to-camp', $student->id), [
            'camp_id' => $camp->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'message',
                'row_html',
                'camp_stats' => ['total', 'approved', 'pending'],
                'camp_id',
            ]);

        $this->assertDatabaseHas('camp_enrollments', [
            'camp_id' => $camp->id,
            'student_id' => $student->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $this->assertDatabaseHas('invoices', [
            'student_id' => $student->id,
        ]);
    }

    public function test_ajax_add_duplicate_camp_returns_422(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();
        $camp = $this->createCamp();

        $this->actingAs($admin)->postJson(route('users.add-to-camp', $student->id), [
            'camp_id' => $camp->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($admin)->postJson(route('users.add-to-camp', $student->id), [
            'camp_id' => $camp->id,
            'status' => 'approved',
            'payment_status' => 'paid',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'الطالب مسجل بالفعل في هذا المعسكر');
    }

    public function test_non_ajax_add_to_group_still_redirects(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();
        $group = $this->createGroup();

        $response = $this->actingAs($admin)->post(route('users.add-to-group', $student->id), [
            'group_id' => $group->id,
            'role' => 'member',
        ]);

        $response->assertRedirect();

        $this->assertTrue(
            CourseGroupMember::where('group_id', $group->id)
                ->where('student_id', $student->id)
                ->exists()
        );
    }
}
