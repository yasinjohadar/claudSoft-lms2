<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Training Camps Management
            'view training camps',
            'create training camps',
            'edit training camps',
            'delete training camps',
            'enroll in training camps',

            // Student Works Management
            'view student works',
            'create student works',
            'edit student works',
            'delete student works',

            // Payment Management
            'view payments',
            'create payments',
            'edit payments',
            'delete payments',

            // Invoice Management
            'view invoices',
            'create invoices',
            'edit invoices',
            'delete invoices',

            // Reports
            'view reports',
            'generate reports',

            // Settings
            'manage settings',
            'view logs',
        ];

        $createdCount = 0;
        foreach ($permissions as $permission) {
            if (!Permission::where('name', $permission)->exists()) {
                Permission::create(['name' => $permission]);
                $createdCount++;
            }
        }

        $this->command->info('✅ تم إنشاء ' . $createdCount . ' صلاحية جديدة (من أصل ' . count($permissions) . ')');

        // Create Roles and assign permissions

        // Admin Role - has all permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        if (!$adminRole->hasAllPermissions(Permission::all())) {
            $adminRole->syncPermissions(Permission::all());
        }
        $this->command->info('✅ تم تحديث دور Admin مع جميع الصلاحيات');

        // Student Role - limited permissions
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $studentPermissions = [
            'view training camps',
            'enroll in training camps',
            'view student works',
            'create student works',
            'edit student works',
            'view payments',
            'view invoices',
        ];
        $studentRole->syncPermissions($studentPermissions);
        $this->command->info('✅ تم تحديث دور Student مع الصلاحيات المحدودة');

        // Instructor Role (optional - for future use)
        $instructorRole = Role::firstOrCreate(['name' => 'instructor']);
        $instructorPermissions = [
            'view training camps',
            'view student works',
            'view users',
            'view payments',
            'view invoices',
            'view reports',
        ];
        $instructorRole->syncPermissions($instructorPermissions);
        $this->command->info('✅ تم تحديث دور Instructor');

        $this->command->info('✅ تم إنشاء الأدوار والصلاحيات بنجاح!');
    }
}
