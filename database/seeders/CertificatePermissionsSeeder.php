<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CertificatePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Certificate Permissions
        $certificatePermissions = [
            'certificates.view',
            'certificates.create',
            'certificates.edit',
            'certificates.delete',
        ];

        // Certificate Template Permissions
        $templatePermissions = [
            'certificate-templates.view',
            'certificate-templates.create',
            'certificate-templates.edit',
            'certificate-templates.delete',
        ];

        // Create all permissions
        $allPermissions = array_merge($certificatePermissions, $templatePermissions);

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign all permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($allPermissions);
            $this->command->info('✓ تم منح جميع صلاحيات الشهادات لدور Admin');
        }

        $this->command->info('✓ تم إنشاء ' . count($allPermissions) . ' صلاحية للشهادات');
    }
}
