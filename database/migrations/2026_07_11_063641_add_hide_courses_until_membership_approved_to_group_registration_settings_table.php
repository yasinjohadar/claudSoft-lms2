<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_registration_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('group_registration_settings', 'hide_courses_until_membership_approved')) {
                $table->boolean('hide_courses_until_membership_approved')
                    ->default(false)
                    ->after('auto_approve_membership');
            }
        });
    }

    public function down(): void
    {
        Schema::table('group_registration_settings', function (Blueprint $table) {
            if (Schema::hasColumn('group_registration_settings', 'hide_courses_until_membership_approved')) {
                $table->dropColumn('hide_courses_until_membership_approved');
            }
        });
    }
};
