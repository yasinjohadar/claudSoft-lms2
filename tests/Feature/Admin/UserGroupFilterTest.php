<?php

namespace Tests\Feature\Admin;

use App\Models\CourseGroup;
use App\Models\CourseGroupMember;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserGroupFilterTest extends TestCase
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

    public function test_admin_can_filter_users_by_any_selected_course_group(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));

        $studentRole = Role::findOrCreate('student', 'web');
        $suffix = uniqid();
        $studentInFirstGroup = User::factory()->create(['name' => "Filtered Student One {$suffix}"]);
        $studentInSecondGroup = User::factory()->create(['name' => "Filtered Student Two {$suffix}"]);
        $studentOutsideGroups = User::factory()->create(['name' => "Outside Student {$suffix}"]);
        $studentInFirstGroup->assignRole($studentRole);
        $studentInSecondGroup->assignRole($studentRole);
        $studentOutsideGroups->assignRole($studentRole);

        $firstGroup = CourseGroup::create(['name' => "فلتر مجموعة أولى {$suffix}", 'is_active' => true]);
        $secondGroup = CourseGroup::create(['name' => "فلتر مجموعة ثانية {$suffix}", 'is_active' => true]);
        $unusedGroup = CourseGroup::create(['name' => "فلتر مجموعة غير مختارة {$suffix}", 'is_active' => true]);

        CourseGroupMember::create([
            'group_id' => $firstGroup->id,
            'student_id' => $studentInFirstGroup->id,
            'role' => 'member',
            'joined_at' => now(),
        ]);
        CourseGroupMember::create([
            'group_id' => $secondGroup->id,
            'student_id' => $studentInSecondGroup->id,
            'role' => 'member',
            'joined_at' => now(),
        ]);
        CourseGroupMember::create([
            'group_id' => $unusedGroup->id,
            'student_id' => $studentOutsideGroups->id,
            'role' => 'member',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('users.index', [
            'group_ids' => [$firstGroup->id, $secondGroup->id],
        ]));

        $response->assertOk();
        $response->assertSee('المجموعات');
        $response->assertSee("Filtered Student One {$suffix}");
        $response->assertSee("Filtered Student Two {$suffix}");
        $response->assertDontSee("Outside Student {$suffix}");
    }
}
