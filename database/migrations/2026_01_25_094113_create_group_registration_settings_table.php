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
        Schema::create('group_registration_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->unique()->constrained('course_groups')->onDelete('cascade');
            $table->boolean('is_registration_enabled')->default(true);
            $table->boolean('auto_create_user')->default(true);
            $table->boolean('send_welcome_email')->default(true);
            $table->boolean('send_welcome_whatsapp')->default(false);
            $table->foreignId('email_template_id')->nullable()->constrained('email_templates')->onDelete('set null');
            $table->text('whatsapp_template')->nullable(); // قالب واتساب
            $table->boolean('require_email_verification')->default(false);
            $table->timestamps();
            
            // Indexes
            $table->index('is_registration_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_registration_settings');
    }
};
