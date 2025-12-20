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
        Schema::create('daily_streaks', function (Blueprint $table) {
            $table->id();

            // User Relationship
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Streak Info
            $table->integer('current_streak')->unsigned()->default(0)->comment('السلسلة الحالية');
            $table->integer('longest_streak')->unsigned()->default(0)->comment('أطول سلسلة');

            // Dates
            $table->date('streak_start_date')->nullable()->comment('تاريخ بداية السلسلة الحالية');
            $table->date('last_login_date')->nullable()->comment('آخر تسجيل دخول');
            $table->date('last_streak_date')->nullable()->comment('آخر يوم في السلسلة');

            // Status
            $table->boolean('is_active')->default(true)->comment('السلسلة نشطة؟');
            $table->boolean('freeze_available')->default(false)->comment('تجميد متاح؟');
            $table->integer('freeze_count')->unsigned()->default(0)->comment('عدد التجميدات المستخدمة');

            // Milestones Achieved
            $table->json('milestones')->nullable()->comment('الإنجازات المحققة (7, 14, 30, 60, 100 يوم)');

            // Rewards
            $table->integer('total_points_earned')->unsigned()->default(0)->comment('إجمالي النقاط المكتسبة من السلاسل');
            $table->integer('total_badges_earned')->unsigned()->default(0)->comment('إجمالي الأوسمة');

            // Metadata
            $table->json('streak_history')->nullable()->comment('تاريخ السلاسل السابقة');

            $table->timestamps();

            // Indexes
            $table->unique('user_id');
            $table->index('current_streak');
            $table->index('longest_streak');
            $table->index('last_login_date');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_streaks');
    }
};
