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
        Schema::create('experience_levels', function (Blueprint $table) {
            $table->id();

            // Level Info
            $table->integer('level')->unsigned()->unique()->comment('رقم المستوى');
            $table->string('name')->comment('اسم المستوى');
            $table->text('description')->nullable()->comment('الوصف');
            $table->string('title')->nullable()->comment('اللقب');

            // XP Requirements
            $table->integer('xp_required')->unsigned()->default(0)->comment('XP المطلوب للوصول لهذا المستوى');
            $table->integer('xp_to_next')->unsigned()->default(0)->comment('XP المطلوب للمستوى التالي');

            // Rewards
            $table->integer('points_reward')->unsigned()->default(0)->comment('مكافأة النقاط');
            $table->foreignId('badge_id')->nullable()->constrained('badges')->onDelete('set null');

            // Unlocks
            $table->json('unlocked_features')->nullable()->comment('الميزات المفتوحة');
            $table->json('unlocked_rewards')->nullable()->comment('المكافآت المفتوحة');

            // Display
            $table->string('icon')->nullable()->comment('الأيقونة');
            $table->string('color_code', 7)->nullable()->comment('اللون');
            $table->integer('sort_order')->default(0)->comment('ترتيب العرض');

            // Stats
            $table->integer('users_count')->unsigned()->default(0)->comment('عدد المستخدمين في هذا المستوى');

            // Status
            $table->boolean('is_active')->default(true)->comment('مفعل؟');

            $table->timestamps();

            // Indexes
            $table->index('level');
            $table->index('xp_required');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experience_levels');
    }
};
