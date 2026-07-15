<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BulkDeleteUsersTest extends TestCase
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

    public function test_admin_can_delete_selected_users(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $users = User::factory()->count(2)->create();

        $response = $this->actingAs($admin)->delete(route('users.bulk-destroy'), [
            'user_ids' => $users->pluck('id')->all(),
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        foreach ($users as $user) {
            $this->assertDatabaseMissing('users', ['id' => $user->id]);
        }
    }

    public function test_admin_cannot_include_own_account_in_bulk_delete(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $other = User::factory()->create();

        $response = $this->actingAs($admin)->delete(route('users.bulk-destroy'), [
            'user_ids' => [$admin->id, $other->id],
        ]);

        $response->assertSessionHasErrors('user_ids');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertDatabaseHas('users', ['id' => $other->id]);
    }
}
