<?php

namespace Tests\Feature\Admin;

use App\Models\CampEnrollment;
use App\Models\CourseGroup;
use App\Models\CourseGroupMember;
use App\Models\Invoice;
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

    public function test_ajax_remove_from_camp_deletes_enrollment_and_returns_camp(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();
        $camp = $this->createCamp();

        $addResponse = $this->actingAs($admin)->postJson(route('users.add-to-camp', $student->id), [
            'camp_id' => $camp->id,
            'status' => 'approved',
            'payment_status' => 'unpaid',
        ]);

        $addResponse->assertOk();
        $enrollmentId = \App\Models\CampEnrollment::where('student_id', $student->id)
            ->where('camp_id', $camp->id)
            ->value('id');

        $this->assertNotNull($enrollmentId);

        $response = $this->actingAs($admin)->postJson(
            route('users.remove-from-camp', [$student->id, $enrollmentId])
        );

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('camp_id', $camp->id)
            ->assertJsonStructure([
                'message',
                'camp_stats' => ['total', 'approved', 'pending'],
                'camp' => ['id', 'name', 'price'],
                'billing_stats' => ['total_invoices', 'total_amount', 'total_paid', 'remaining_amount'],
                'cancelled_invoice_ids',
            ]);

        $this->assertDatabaseMissing('camp_enrollments', [
            'id' => $enrollmentId,
            'student_id' => $student->id,
            'camp_id' => $camp->id,
        ]);

        $invoice = Invoice::where('student_id', $student->id)->first();
        $this->assertNotNull($invoice);
        $this->assertSame('cancelled', $invoice->fresh()->status);
        $this->assertSame(0.0, (float) $invoice->fresh()->remaining_amount);
    }

    public function test_ajax_remove_from_camp_blocks_when_invoice_is_partially_paid(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();
        $camp = $this->createCamp();

        $this->actingAs($admin)->postJson(route('users.add-to-camp', $student->id), [
            'camp_id' => $camp->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $enrollmentId = CampEnrollment::where('student_id', $student->id)->value('id');
        $invoice = Invoice::where('student_id', $student->id)->first();
        $invoice->update([
            'status' => 'partial',
            'paid_amount' => 50,
            'remaining_amount' => 200,
        ]);

        $response = $this->actingAs($admin)->postJson(
            route('users.remove-from-camp', [$student->id, $enrollmentId])
        );

        $response->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('camp_enrollments', ['id' => $enrollmentId]);
        $this->assertSame('partial', $invoice->fresh()->status);
    }

    public function test_ajax_remove_from_camp_blocks_when_invoice_is_paid(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();
        $camp = $this->createCamp();

        $this->actingAs($admin)->postJson(route('users.add-to-camp', $student->id), [
            'camp_id' => $camp->id,
            'status' => 'approved',
            'payment_status' => 'paid',
        ]);

        $enrollmentId = CampEnrollment::where('student_id', $student->id)->value('id');
        $invoice = Invoice::where('student_id', $student->id)->first();
        $invoice->update([
            'status' => 'paid',
            'paid_amount' => 250,
            'remaining_amount' => 0,
        ]);

        $response = $this->actingAs($admin)->postJson(
            route('users.remove-from-camp', [$student->id, $enrollmentId])
        );

        $response->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('camp_enrollments', ['id' => $enrollmentId]);
    }

    public function test_update_camp_status_to_cancelled_cancels_unpaid_invoice(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();
        $camp = $this->createCamp();

        $this->actingAs($admin)->postJson(route('users.add-to-camp', $student->id), [
            'camp_id' => $camp->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        $enrollmentId = CampEnrollment::where('student_id', $student->id)->value('id');
        $invoice = Invoice::where('student_id', $student->id)->first();

        $response = $this->actingAs($admin)->postJson(
            route('users.update-camp-enrollment', [$student->id, $enrollmentId]),
            ['status' => 'cancelled']
        );

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'cancelled');

        $this->assertDatabaseHas('camp_enrollments', [
            'id' => $enrollmentId,
            'status' => 'cancelled',
        ]);
        $this->assertSame('cancelled', $invoice->fresh()->status);
        $this->assertSame(0.0, (float) $invoice->fresh()->remaining_amount);
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
