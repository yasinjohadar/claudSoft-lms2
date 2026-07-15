<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_groups', function (Blueprint $table) {
            if (! Schema::hasColumn('course_groups', 'device_lock_enabled')) {
                $table->boolean('device_lock_enabled')->default(false)->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('course_groups', function (Blueprint $table) {
            if (Schema::hasColumn('course_groups', 'device_lock_enabled')) {
                $table->dropColumn('device_lock_enabled');
            }
        });
    }
};
