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
        Schema::create('gamification_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type', 50)->index(); // نوع الإشعار
            $table->string('title', 255); // العنوان
            $table->text('message'); // الرسالة
            $table->string('icon', 100)->nullable(); // الأيقونة (emoji أو رابط)
            $table->string('action_url', 500)->nullable(); // رابط الإجراء
            $table->string('related_type', 255)->nullable(); // نوع الكيان المرتبط
            $table->unsignedBigInteger('related_id')->nullable(); // معرف الكيان المرتبط
            $table->json('metadata')->nullable(); // بيانات إضافية
            $table->boolean('is_read')->default(false)->index(); // حالة القراءة
            $table->timestamp('read_at')->nullable(); // وقت القراءة
            $table->timestamps();

            // Indexes للأداء
            $table->index(['user_id', 'is_read']); // للاستعلامات السريعة
            $table->index('created_at'); // للترتيب الزمني
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gamification_notifications');
    }
};
