<?php

namespace Tests\Feature\Auth;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

abstract class DeviceSecurityTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('user_devices');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('users');

        $tableNames = config('permission.table_names');
        if (! empty($tableNames)) {
            Schema::dropIfExists($tableNames['role_has_permissions']);
            Schema::dropIfExists($tableNames['model_has_roles']);
            Schema::dropIfExists($tableNames['model_has_permissions']);
            Schema::dropIfExists($tableNames['roles']);
            Schema::dropIfExists($tableNames['permissions']);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->string('device_lock_mode', 20)->default('inherit');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('device_fingerprint', 128);
            $table->string('device_name')->nullable();
            $table->string('device_type', 20);
            $table->string('browser', 50)->nullable();
            $table->string('browser_version', 50)->nullable();
            $table->string('platform', 50)->nullable();
            $table->string('platform_version', 50)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->unsignedInteger('total_logins')->default(0);
            $table->timestamp('first_used_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_ip_address', 45)->nullable();
            $table->boolean('is_trusted')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'device_fingerprint']);
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->string('group')->default('general');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['key', 'group']);
        });

        $this->createPermissionTables();
    }

    protected function createPermissionTables(): void
    {
        $migration = require database_path('migrations/2025_06_12_181956_create_permission_tables.php');
        $migration->up();
    }
}
