<?php

use App\Services\DeviceSecuritySettingsService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'device_lock_mode')) {
                $table->string('device_lock_mode', 20)->default('inherit')->after('is_active');
            }
        });

        app(DeviceSecuritySettingsService::class)->seedDefaults();
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'device_lock_mode')) {
                $table->dropColumn('device_lock_mode');
            }
        });
    }
};
