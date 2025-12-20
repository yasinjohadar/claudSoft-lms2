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
        Schema::create('n8n_webhook_endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الـ endpoint
            $table->string('event_type')->index(); // نوع الحدث (student.enrolled, course.completed, إلخ) أو '*' للكل
            $table->text('url'); // رابط n8n webhook
            $table->string('secret_key'); // مفتاح سري للتوقيع
            $table->boolean('is_active')->default(true)->index(); // تفعيل/تعطيل
            $table->unsignedTinyInteger('retry_attempts')->default(3); // عدد محاولات إعادة الإرسال
            $table->unsignedSmallInteger('timeout')->default(30); // مهلة الاتصال بالثواني
            $table->json('headers')->nullable(); // headers إضافية مخصصة
            $table->json('metadata')->nullable(); // معلومات إضافية
            $table->text('description')->nullable(); // وصف الـ endpoint
            $table->timestamps();

            // فهرس مركب للبحث السريع
            $table->index(['event_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('n8n_webhook_endpoints');
    }
};
