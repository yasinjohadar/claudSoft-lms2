<?php

namespace Tests\Feature\Admin;

use App\Models\CourseGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GroupMembersPerPageTest extends TestCase
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

    public function test_group_members_page_shows_per_page_selector(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));

        $group = CourseGroup::create([
            'name' => 'مجموعة حجم الصفحة '.uniqid(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('groups.show', $group->id).'?per_page=100');

        $response->assertOk();
        $response->assertSee('name="per_page"', false);
        $response->assertSee('value="25"', false);
        $response->assertSee('value="50"', false);
        $response->assertSee('value="100"', false);
        $response->assertSee('value="150"', false);
        $response->assertSee('selected', false);
    }

    public function test_group_members_ajax_accepts_allowed_per_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));

        $group = CourseGroup::create([
            'name' => 'مجموعة اجاكس حجم '.uniqid(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ])
            ->get(route('groups.show', $group->id).'?per_page=50');

        $response->assertOk();
        $response->assertJsonStructure(['table_html']);
        $this->assertNotEmpty($response->json('table_html'));
    }

    public function test_group_members_falls_back_to_default_for_invalid_per_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));

        $group = CourseGroup::create([
            'name' => 'مجموعة حجم غير صالح '.uniqid(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('groups.show', $group->id).'?per_page=999');

        $response->assertOk();
        $response->assertSee('name="per_page"', false);
        $html = $response->getContent();
        $this->assertMatchesRegularExpression('/name="per_page"[^>]*>[\s\S]*?<option[^>]*value="25"[^>]*selected/', $html);
    }
}
