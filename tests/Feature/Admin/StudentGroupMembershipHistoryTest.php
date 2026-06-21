<?php

namespace Tests\Feature\Admin;

use App\Models\CourseGroup;
use App\Models\CourseGroupMembershipHistory;
use App\Models\CourseGroupMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentGroupMembershipHistoryTest extends TestCase
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

    public function test_add_to_group_creates_active_history_record(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();
        $group = $this->createGroup();

        $this->actingAs($admin)->postJson(route('users.add-to-group', $student->id), [
            'group_id' => $group->id,
            'role' => 'member',
            'reason' => 'اختبار إضافة',
        ])->assertOk();

        $this->assertDatabaseHas('course_group_membership_histories', [
            'student_id' => $student->id,
            'group_id' => $group->id,
            'join_reason' => 'اختبار إضافة',
            'left_at' => null,
            'source' => CourseGroupMembershipHistory::SOURCE_PROFILE,
            'joined_by' => $admin->id,
        ]);
    }

    public function test_remove_from_group_closes_history_record(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();
        $group = $this->createGroup();

        $group->addMember($student, 'member', [
            'source' => CourseGroupMembershipHistory::SOURCE_PROFILE,
            'performed_by' => $admin->id,
        ]);

        $this->actingAs($admin)->postJson(route('users.remove-from-group', $student->id), [
            'group_id' => $group->id,
            'reason' => 'اختبار إزالة',
        ])->assertOk();

        $this->assertDatabaseMissing('course_group_members', [
            'student_id' => $student->id,
            'group_id' => $group->id,
        ]);

        $this->assertDatabaseHas('course_group_membership_histories', [
            'student_id' => $student->id,
            'group_id' => $group->id,
            'leave_reason' => 'اختبار إزالة',
            'removed_by' => $admin->id,
        ]);

        $history = CourseGroupMembershipHistory::where('student_id', $student->id)
            ->where('group_id', $group->id)
            ->first();

        $this->assertNotNull($history->left_at);
    }

    public function test_rejoin_creates_new_history_row(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();
        $group = $this->createGroup();

        $group->addMember($student, 'member', ['source' => CourseGroupMembershipHistory::SOURCE_PROFILE]);
        $group->removeMember($student, ['source' => CourseGroupMembershipHistory::SOURCE_PROFILE]);
        $group->addMember($student, 'member', ['source' => CourseGroupMembershipHistory::SOURCE_PROFILE]);

        $this->assertSame(2, CourseGroupMembershipHistory::where('student_id', $student->id)->count());
        $this->assertSame(1, CourseGroupMembershipHistory::where('student_id', $student->id)->active()->count());
    }

    public function test_profile_page_shows_membership_history_section(): void
    {
        $admin = $this->adminUser();
        $student = $this->studentUser();
        $group = $this->createGroup('مجموعة السجل');

        $group->addMember($student, 'member', [
            'source' => CourseGroupMembershipHistory::SOURCE_PROFILE,
            'reason' => 'انضمام أول',
        ]);

        $response = $this->actingAs($admin)->get(route('users.show', $student->id));

        $response->assertOk()
            ->assertSee('سجل التنقل بين المجموعات')
            ->assertSee('مجموعة السجل')
            ->assertSee('انضمام أول');
    }
}
