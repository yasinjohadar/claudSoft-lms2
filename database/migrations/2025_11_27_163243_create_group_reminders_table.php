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
        Schema::create('group_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->string('target_type')->comment('course, group, training_camp');
            $table->unsignedBigInteger('target_id')->comment('معرف الكورس أو المجموعة');
            $table->string('title')->comment('عنوان التذكير');
            $table->text('message')->comment('محتوى الرسالة');
            $table->string('reminder_type')->default('announcement')->comment('assignment, exam, announcement, deadline');
            $table->string('priority')->default('medium')->comment('low, medium, high, urgent');
            $table->timestamp('remind_at')->nullable()->comment('تاريخ ووقت التذكير');
            $table->boolean('send_email')->default(true)->comment('إرسال بريد إلكتروني');
            $table->boolean('send_notification')->default(true)->comment('إرسال إشعار داخلي');
            $table->boolean('is_active')->default(true)->comment('نشط');
            $table->boolean('is_sent')->default(false)->comment('تم الإرسال');
            $table->timestamp('sent_at')->nullable()->comment('تاريخ الإرسال');
            $table->integer('recipients_count')->default(0)->comment('عدد المستلمين');
            $table->integer('read_count')->default(0)->comment('عدد الذين قرأوا');
            $table->timestamps();
            $table->softDeletes();

            $table->index('creator_id');
            $table->index(['target_type', 'target_id']);
            $table->index('reminder_type');
            $table->index('priority');
            $table->index('is_sent');
            $table->index('remind_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_reminders');
    }
};
