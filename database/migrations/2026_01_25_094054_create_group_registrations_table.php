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
        Schema::create('group_registrations', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('group_id')->constrained('course_groups')->onDelete('cascade');
            
            // User Data (مطابق لجدول users)
            $table->string('name'); // الاسم الكامل بالإنجليزية
            $table->string('name_ar'); // الاسم الكامل بالعربية
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('country_code')->nullable();
            $table->string('full_phone')->nullable();
            $table->foreignId('nationality_id')->nullable()->constrained('nationalities')->onDelete('set null');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            
            // Custom Fields
            $table->text('notes')->nullable();
            $table->text('additional_info')->nullable();
            $table->text('special_requirements')->nullable();
            
            // Email & WhatsApp (إجبارية من الإدارة - لا تظهر للطالب)
            $table->boolean('email_sent')->default(false);
            $table->boolean('whatsapp_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('whatsapp_sent_at')->nullable();
            
            // Processing
            $table->boolean('user_created')->default(false);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            
            // Admin
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index('group_id');
            $table->index('email');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_registrations');
    }
};
