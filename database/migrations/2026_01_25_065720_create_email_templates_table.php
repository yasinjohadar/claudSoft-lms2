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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم القالب
            $table->string('name_ar')->nullable(); // اسم القالب بالعربية
            $table->string('subject'); // موضوع البريد
            $table->text('body'); // محتوى البريد (HTML)
            $table->enum('type', ['registration_welcome', 'enrollment_confirmation', 'custom'])->default('custom');
            $table->json('variables')->nullable(); // متغيرات القالب
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
