<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_registration_settings', function (Blueprint $table) {
            $table->foreignId('whatsapp_template_id')
                ->nullable()
                ->after('email_template_id')
                ->constrained('whatsapp_message_templates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('group_registration_settings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('whatsapp_template_id');
        });
    }
};
