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
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('name')->comment('اسم الإنجاز');
            $table->string('slug')->unique()->comment('معرف فريد');
            $table->text('description')->nullable()->comment('وصف الإنجاز');
            $table->string('icon')->nullable()->comment('أيقونة الإنجاز');

            // Categorization
            $table->enum('type', ['course', 'quiz', 'assignment', 'streak', 'social', 'special', 'general'])
                  ->default('general')
                  ->comment('نوع الإنجاز');
            $table->string('category', 100)->nullable()->comment('تصنيف فرعي');

            // Tier System (مستويات الإنجاز)
            $table->enum('tier', ['bronze', 'silver', 'gold', 'platinum', 'diamond'])
                  ->default('bronze')
                  ->comment('مستوى الإنجاز');

            // Requirements & Criteria
            $table->json('criteria')->nullable()->comment('معايير تحقيق الإنجاز');
            $table->integer('target_value')->unsigned()->nullable()->comment('القيمة المستهدفة');
            $table->string('metric')->nullable()->comment('المقياس المستخدم');

            // Rewards
            $table->integer('points_reward')->unsigned()->default(0)->comment('مكافأة النقاط');
            $table->integer('xp_reward')->unsigned()->default(0)->comment('مكافأة الخبرة');
            $table->foreignId('badge_id')->nullable()->constrained('badges')->onDelete('set null');

            // Status & Visibility
            $table->boolean('is_active')->default(true)->comment('مفعل؟');
            $table->boolean('is_secret')->default(false)->comment('إنجاز سري');
            $table->boolean('is_repeatable')->default(false)->comment('قابل للتكرار');

            // Order & Display
            $table->integer('sort_order')->default(0)->comment('ترتيب العرض');
            $table->string('color_code', 7)->nullable()->comment('لون الإنجاز');

            // Statistics
            $table->integer('completed_count')->unsigned()->default(0)->comment('عدد من حققه');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('type');
            $table->index('tier');
            $table->index('is_active');
            $table->index('is_repeatable');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
