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
        Schema::create('outgoing_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('endpoint_id')->constrained('n8n_webhook_endpoints')->onDelete('cascade'); // FK إلى الـ endpoint
            $table->string('event_type')->index(); // نوع الحدث
            $table->json('payload'); // البيانات المرسلة
            $table->unsignedSmallInteger('response_status')->nullable(); // HTTP status code
            $table->text('response_body')->nullable(); // رد n8n
            $table->unsignedTinyInteger('attempt_number')->default(1); // رقم المحاولة
            $table->enum('status', ['pending', 'sent', 'failed', 'retrying'])->default('pending')->index(); // حالة الإرسال
            $table->text('error_message')->nullable(); // رسالة الخطأ إن وجدت
            $table->timestamp('sent_at')->nullable(); // وقت الإرسال
            $table->timestamps();

            // فهارس للبحث السريع
            $table->index(['status', 'created_at']);
            $table->index(['endpoint_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outgoing_webhook_logs');
    }
};
