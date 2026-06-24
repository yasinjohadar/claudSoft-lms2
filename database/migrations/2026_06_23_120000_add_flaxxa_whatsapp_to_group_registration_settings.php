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
            if (! Schema::hasColumn('group_registration_settings', 'whatsapp_delivery_mode')) {
                $table->string('whatsapp_delivery_mode', 32)->default('evolution_text')->after('send_welcome_whatsapp');
            }
            if (! Schema::hasColumn('group_registration_settings', 'wapi_template_id')) {
                $table->foreignId('wapi_template_id')->nullable()->after('whatsapp_template_id')->constrained('wapi_templates')->nullOnDelete();
            }
            if (! Schema::hasColumn('group_registration_settings', 'wapi_template_language')) {
                $table->string('wapi_template_language', 16)->nullable()->default('ar')->after('wapi_template_id');
            }
            if (! Schema::hasColumn('group_registration_settings', 'wapi_body_variables')) {
                $table->json('wapi_body_variables')->nullable()->after('wapi_template_language');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('group_registration_settings')) {
            return;
        }

        Schema::table('group_registration_settings', function (Blueprint $table) {
            if (Schema::hasColumn('group_registration_settings', 'wapi_template_id')) {
                $table->dropConstrainedForeignId('wapi_template_id');
            }
            foreach (['whatsapp_delivery_mode', 'wapi_template_language', 'wapi_body_variables'] as $column) {
                if (Schema::hasColumn('group_registration_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
