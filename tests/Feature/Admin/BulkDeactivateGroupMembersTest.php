<?php

namespace Tests\Feature\Admin;

use App\Models\CourseGroup;
use App\Models\CourseGroupMember;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BulkDeactivateGroupMembersTest extends TestCase
{
    use DatabaseTransactions;

    public function createApplication()
    {
        $app = parent::createApplication();

        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql.database', 'cloudsoft_platform');
        $app['config']->set('activitylog.enabled', false);

        return $app;
    }

    public function test_admin_can_deactivate_selected_group_members_with_shared_note(): void
    {
        $adminRole = Role::findOrCreate('admin', 'web');
        $studentRole = Role::findOrCreate('student', 'web');

        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $students = User::factory()->count(2)->create(['is_active' => true]);
        $students->each->assignRole($studentRole);

        $group = CourseGroup::create([
            'name' => 'مجموعة الإيقاف الجماعي',
            'is_active' => true,
        ]);

        foreach ($students as $student) {
            CourseGroupMember::create([
                'group_id' => $group->id,
                'student_id' => $student->id,
                'role' => 'member',
                'joined_at' => now(),
            ]);
        }

        $response = $this->actingAs($admin)->post(
            route('groups.bulk-deactivate-members', $group),
            [
                'member_ids' => $students->pluck('id')->all(),
                'admin_note_body' => 'إيقاف مؤقت بسبب عدم الالتزام.',
                'occurred_on' => now()->toDateString(),
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        foreach ($students as $student) {
            $this->assertDatabaseHas('users', [
                'id' => $student->id,
                'is_active' => false,
            ]);
            $this->assertDatabaseHas('user_admin_notes', [
                'user_id' => $student->id,
                'created_by' => $admin->id,
                'body' => 'إيقاف مؤقت بسبب عدم الالتزام.',
                'source' => 'bulk_group_deactivation',
            ]);
            $this->assertDatabaseHas('course_group_members', [
                'group_id' => $group->id,
                'student_id' => $student->id,
            ]);
        }
    }

    public function test_bulk_deactivation_rejects_users_outside_the_group(): void
    {
        $adminRole = Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $outsider = User::factory()->create(['is_active' => true]);
        $group = CourseGroup::create([
            'name' => 'مجموعة الحماية',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(
            route('groups.bulk-deactivate-members', $group),
            [
                'member_ids' => [$outsider->id],
                'admin_note_body' => 'يجب ألا تُطبّق.',
                'occurred_on' => now()->toDateString(),
            ]
        )->assertSessionHasErrors('member_ids.0');

        $this->assertTrue($outsider->fresh()->is_active);
        $this->assertDatabaseMissing('user_admin_notes', [
            'user_id' => $outsider->id,
            'source' => 'bulk_group_deactivation',
        ]);
    }

    public function test_admin_can_reactivate_selected_inactive_group_members_with_shared_note(): void
    {
        $adminRole = Role::findOrCreate('admin', 'web');
        $studentRole = Role::findOrCreate('student', 'web');

        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $students = User::factory()->count(2)->create(['is_active' => false]);
        $students->each->assignRole($studentRole);

        $group = CourseGroup::create([
            'name' => 'مجموعة التشغيل الجماعي',
            'is_active' => true,
        ]);

        foreach ($students as $student) {
            CourseGroupMember::create([
                'group_id' => $group->id,
                'student_id' => $student->id,
                'role' => 'member',
                'joined_at' => now(),
            ]);
        }

        $response = $this->actingAs($admin)->post(
            route('groups.bulk-reactivate-members', $group),
            [
                'member_ids' => $students->pluck('id')->all(),
                'admin_note_body' => 'إعادة التشغيل بعد معالجة سبب الإيقاف.',
                'occurred_on' => now()->toDateString(),
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        foreach ($students as $student) {
            $this->assertDatabaseHas('users', [
                'id' => $student->id,
                'is_active' => true,
            ]);
            $this->assertDatabaseHas('user_admin_notes', [
                'user_id' => $student->id,
                'created_by' => $admin->id,
                'body' => 'إعادة التشغيل بعد معالجة سبب الإيقاف.',
                'source' => 'bulk_group_reactivation',
            ]);
        }
    }
}
