<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ActivityLogPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'activity-log-view', 'guard_name' => 'web']);

        $admin = Role::where('name', 'admin')->first();
        if ($admin && ! $admin->hasPermissionTo('activity-log-view')) {
            $admin->givePermissionTo('activity-log-view');
        }
    }
}
