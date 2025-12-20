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
        Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('achievement_id')->constrained('achievements')->onDelete('cascade');

            // Progress Tracking
            $table->integer('current_value')->unsigned()->default(0)->comment('القيمة الحالية');
            $table->integer('target_value')->unsigned()->nullable()->comment('القيمة المستهدفة');
            $table->decimal('progress_percentage', 5, 2)->default(0)->comment('نسبة التقدم');

            // Status
            $table->enum('status', ['in_progress', 'completed', 'claimed'])->default('in_progress')->comment('الحالة');
            $table->timestamp('started_at')->nullable()->comment('تاريخ البدء');
            $table->timestamp('completed_at')->nullable()->comment('تاريخ الإكمال');
            $table->timestamp('claimed_at')->nullable()->comment('تاريخ استلام المكافأة');

            // Polymorphic Relation
            $table->morphs('related');

            // Progress Data
            $table->json('progress_data')->nullable()->comment('بيانات التقدم التفصيلية');

            // Rewards Claimed
            $table->integer('points_claimed')->unsigned()->default(0)->comment('النقاط المستلمة');
            $table->integer('xp_claimed')->unsigned()->default(0)->comment('الخبرة المستلمة');

            // Notification
            $table->boolean('is_notified')->default(false)->comment('تم الإشعار؟');

            $table->timestamps();

            // Indexes
            $table->unique(['user_id', 'achievement_id'], 'user_achievement_unique');
            $table->index('status');
            $table->index('completed_at');
            // morphs() already creates index for related_type & related_id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
    }
};
