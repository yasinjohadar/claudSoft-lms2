<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('group_registration_settings')) {
            return;
        }

        Schema::table('group_registration_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('group_registration_settings', 'use_fixed_registration_password')) {
                $table->boolean('use_fixed_registration_password')->default(false)->after('auto_create_user');
            }
            if (! Schema::hasColumn('group_registration_settings', 'fixed_registration_password')) {
                $table->string('fixed_registration_password', 64)->nullable()->after('use_fixed_registration_password');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('group_registration_settings')) {
            return;
        }

        Schema::table('group_registration_settings', function (Blueprint $table) {
            if (Schema::hasColumn('group_registration_settings', 'fixed_registration_password')) {
                $table->dropColumn('fixed_registration_password');
            }
            if (Schema::hasColumn('group_registration_settings', 'use_fixed_registration_password')) {
                $table->dropColumn('use_fixed_registration_password');
            }
        });
    }
};
