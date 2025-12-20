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
        Schema::create('user_stats', function (Blueprint $table) {
            $table->id();

            // User Relationship (One-to-One)
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');

            // Points & Level
            $table->integer('total_points')->unsigned()->default(0)->comment('مجموع النقاط');
            $table->integer('available_points')->unsigned()->default(0)->comment('النقاط المتاحة');
            $table->integer('spent_points')->unsigned()->default(0)->comment('النقاط المصروفة');

            // Experience & Level
            $table->integer('total_xp')->unsigned()->default(0)->comment('مجموع نقاط الخبرة');
            $table->integer('current_level')->unsigned()->default(1)->comment('المستوى الحالي');
            $table->integer('xp_to_next_level')->unsigned()->default(100)->comment('XP المطلوب للمستوى التالي');
            $table->decimal('level_progress', 5, 2)->default(0)->comment('نسبة التقدم للمستوى التالي');

            // Badges & Achievements
            $table->integer('total_badges')->unsigned()->default(0)->comment('عدد الأوسمة');
            $table->integer('total_achievements')->unsigned()->default(0)->comment('عدد الإنجازات');

            // Streaks
            $table->integer('current_streak')->unsigned()->default(0)->comment('سلسلة الحضور الحالية');
            $table->integer('longest_streak')->unsigned()->default(0)->comment('أطول سلسلة حضور');
            $table->date('last_login_date')->nullable()->comment('آخر تسجيل دخول');

            // Activity Stats
            $table->integer('courses_completed')->unsigned()->default(0)->comment('الكورسات المكتملة');
            $table->integer('courses_enrolled')->unsigned()->default(0)->comment('الكورسات المسجلة');
            $table->integer('quizzes_completed')->unsigned()->default(0)->comment('الاختبارات المكتملة');
            $table->integer('assignments_submitted')->unsigned()->default(0)->comment('الواجبات المسلمة');

            // Performance Stats
            $table->decimal('average_quiz_score', 5, 2)->default(0)->comment('متوسط درجات الاختبارات');
            $table->decimal('average_assignment_score', 5, 2)->default(0)->comment('متوسط درجات الواجبات');
            $table->integer('perfect_scores')->unsigned()->default(0)->comment('عدد الدرجات الكاملة');

            // Leaderboard Ranks
            $table->integer('global_rank')->unsigned()->nullable()->comment('الترتيب العالمي');
            $table->integer('course_rank')->unsigned()->nullable()->comment('الترتيب في الكورس');
            $table->enum('division', ['bronze', 'silver', 'gold', 'platinum', 'diamond'])->default('bronze')->comment('الفئة');

            // Social Stats
            $table->integer('comments_count')->unsigned()->default(0)->comment('عدد التعليقات');
            $table->integer('discussions_count')->unsigned()->default(0)->comment('عدد المناقشات');
            $table->integer('helpful_count')->unsigned()->default(0)->comment('عدد المساعدات');

            // Time Stats
            $table->integer('total_study_time')->unsigned()->default(0)->comment('إجمالي وقت الدراسة بالدقائق');
            $table->timestamp('last_activity_at')->nullable()->comment('آخر نشاط');

            // Metadata
            $table->json('additional_stats')->nullable()->comment('إحصائيات إضافية');

            $table->timestamps();

            // Indexes
            $table->index('total_points');
            $table->index('current_level');
            $table->index('global_rank');
            $table->index('division');
            $table->index('current_streak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_stats');
    }
};
