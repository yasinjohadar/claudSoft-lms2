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
        Schema::create('user_challenges', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('challenge_id')->constrained('challenges')->onDelete('cascade');

            // Progress
            $table->integer('current_value')->unsigned()->default(0)->comment('القيمة الحالية');
            $table->integer('target_value')->unsigned()->nullable()->comment('القيمة المستهدفة');
            $table->decimal('progress_percentage', 5, 2)->default(0)->comment('نسبة التقدم');

            // Status
            $table->enum('status', ['not_started', 'in_progress', 'completed', 'failed', 'expired'])
                  ->default('not_started')
                  ->comment('الحالة');

            // Dates
            $table->timestamp('joined_at')->nullable()->comment('تاريخ الانضمام');
            $table->timestamp('started_at')->nullable()->comment('تاريخ البدء');
            $table->timestamp('completed_at')->nullable()->comment('تاريخ الإكمال');
            $table->timestamp('expires_at')->nullable()->comment('تاريخ انتهاء الصلاحية');

            // Rewards
            $table->integer('points_earned')->unsigned()->default(0)->comment('النقاط المكتسبة');
            $table->integer('xp_earned')->unsigned()->default(0)->comment('الخبرة المكتسبة');
            $table->boolean('rewards_claimed')->default(false)->comment('تم استلام المكافأة؟');

            // Progress Data
            $table->json('progress_data')->nullable()->comment('بيانات التقدم التفصيلية');

            // Team Challenge
            $table->string('team_id')->nullable()->comment('معرف الفريق');
            $table->boolean('is_team_leader')->default(false)->comment('قائد الفريق؟');

            // Notification
            $table->boolean('is_notified')->default(false)->comment('تم الإشعار؟');

            // Attempts
            $table->integer('attempts_count')->unsigned()->default(1)->comment('عدد المحاولات');

            $table->timestamps();

            // Indexes
            $table->unique(['user_id', 'challenge_id', 'attempts_count'], 'user_challenge_unique');
            $table->index('status');
            $table->index('completed_at');
            $table->index('team_id');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_challenges');
    }
};
