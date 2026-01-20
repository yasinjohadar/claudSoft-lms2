<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('course_groups', function (Blueprint $table) {
            $table->boolean('allow_membership_requests')->default(false)->after('is_active');
            $table->boolean('is_visible_for_students')->default(true)->after('allow_membership_requests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_groups', function (Blueprint $table) {
            $table->dropColumn(['allow_membership_requests', 'is_visible_for_students']);
        });
    }
};
