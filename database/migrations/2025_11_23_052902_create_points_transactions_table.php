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
        Schema::create('points_transactions', function (Blueprint $table) {
            $table->id();

            // User Relationship
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Transaction Type
            $table->enum('type', ['earn', 'spend', 'bonus', 'penalty', 'refund', 'adjustment'])
                  ->default('earn')
                  ->comment('نوع العملية');

            // Points
            $table->integer('points')->comment('عدد النقاط (موجب للربح، سالب للصرف)');
            $table->integer('balance_before')->unsigned()->default(0)->comment('الرصيد قبل');
            $table->integer('balance_after')->unsigned()->default(0)->comment('الرصيد بعد');

            // Source/Reason
            $table->string('source')->comment('مصدر النقاط');
            $table->text('description')->nullable()->comment('الوصف');

            // Polymorphic Relation (What generated the points)
            $table->morphs('related');
            // Example: related_type = 'App\Models\QuizAttempt', related_id = 123
            // Example: related_type = 'App\Models\UserReward', related_id = 45

            // Multiplier
            $table->decimal('multiplier', 5, 2)->default(1.00)->comment('معامل الضرب');

            // Metadata
            $table->json('metadata')->nullable()->comment('بيانات إضافية');

            // Admin Action
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null')->comment('المسؤول (للتعديلات اليدوية)');

            // Expiration (للنقاط التي لها صلاحية)
            $table->timestamp('expires_at')->nullable()->comment('تاريخ انتهاء الصلاحية');
            $table->boolean('is_expired')->default(false)->comment('منتهي؟');

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('type');
            // morphs() already creates index for related_type & related_id
            $table->index('created_at');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('points_transactions');
    }
};
