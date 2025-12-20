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
        Schema::create('n8n_incoming_webhook_handlers', function (Blueprint $table) {
            $table->id();
            $table->string('handler_type')->unique(); // نوع المعالج (user.create, course.enroll, إلخ)
            $table->string('handler_class'); // اسم الـ class المسؤول عن المعالجة
            $table->text('description')->nullable(); // وصف المعالج
            $table->json('required_fields')->nullable(); // الحقول المطلوبة
            $table->json('optional_fields')->nullable(); // الحقول الاختيارية
            $table->boolean('is_active')->default(true)->index(); // تفعيل/تعطيل
            $table->json('example_payload')->nullable(); // مثال على البيانات المطلوبة
            $table->timestamps();

            // فهرس للبحث
            $table->index(['handler_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('n8n_incoming_webhook_handlers');
    }
};
