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
        Schema::table('group_registration_settings', function (Blueprint $table) {
            $table->string('diploma_name', 255)->nullable()->after('group_id');
            $table->json('extra')->nullable()->after('require_email_verification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_registration_settings', function (Blueprint $table) {
            $table->dropColumn(['diploma_name', 'extra']);
        });
    }
};
