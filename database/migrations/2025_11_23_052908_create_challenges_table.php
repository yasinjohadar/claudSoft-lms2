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
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('name')->comment('اسم التحدي');
            $table->string('slug')->unique()->comment('معرف فريد');
            $table->text('description')->nullable()->comment('وصف التحدي');
            $table->string('icon')->nullable()->comment('الأيقونة');

            // Type & Period
            $table->enum('type', ['daily', 'weekly', 'monthly', 'special', 'team', 'individual'])
                  ->default('daily')
                  ->comment('نوع التحدي');
            $table->enum('frequency', ['once', 'daily', 'weekly', 'monthly'])->default('once');

            // Difficulty
            $table->enum('difficulty', ['easy', 'medium', 'hard', 'expert'])->default('easy');

            // Requirements & Criteria
            $table->json('requirements')->nullable()->comment('متطلبات التحدي');
            $table->integer('target_value')->unsigned()->nullable()->comment('القيمة المستهدفة');
            $table->string('metric')->nullable()->comment('المقياس');

            // Rewards
            $table->integer('points_reward')->unsigned()->default(0)->comment('مكافأة النقاط');
            $table->integer('xp_reward')->unsigned()->default(0)->comment('مكافأة الخبرة');
            $table->foreignId('badge_id')->nullable()->constrained('badges')->onDelete('set null');

            // Time Period
            $table->timestamp('start_date')->nullable()->comment('تاريخ البدء');
            $table->timestamp('end_date')->nullable()->comment('تاريخ الانتهاء');
            $table->integer('duration_hours')->unsigned()->nullable()->comment('المدة بالساعات');

            // Participation
            $table->enum('participation_type', ['auto', 'opt_in'])->default('auto');
            $table->integer('max_participants')->unsigned()->nullable()->comment('أقصى عدد مشاركين');
            $table->integer('min_level')->unsigned()->default(1)->comment('الحد الأدنى للمستوى');

            // Team Challenge
            $table->boolean('is_team_challenge')->default(false)->comment('تحدي جماعي؟');
            $table->integer('team_size')->unsigned()->nullable()->comment('حجم الفريق');

            // Status
            $table->boolean('is_active')->default(true)->comment('مفعل؟');
            $table->boolean('is_visible')->default(true)->comment('مرئي؟');
            $table->boolean('is_featured')->default(false)->comment('مميز؟');

            // Stats
            $table->integer('participants_count')->unsigned()->default(0)->comment('عدد المشاركين');
            $table->integer('completed_count')->unsigned()->default(0)->comment('عدد من أكملوه');

            // Display
            $table->integer('sort_order')->default(0)->comment('ترتيب العرض');
            $table->string('color_code', 7)->nullable()->comment('اللون');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('type');
            $table->index('difficulty');
            $table->index('is_active');
            $table->index(['start_date', 'end_date']);
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
