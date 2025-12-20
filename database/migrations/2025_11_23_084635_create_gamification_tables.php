<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // جدول المستويات
        Schema::create('gamification_levels', function (Blueprint $table) {
            $table->id();
            $table->integer('level')->unique();
            $table->string('name');
            $table->integer('xp_required')->default(0);
            $table->integer('points_reward')->default(0);
            $table->integer('gems_reward')->default(0);
            $table->timestamps();
        });

        // جدول الشارات
        Schema::create('gamification_badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('type')->default('achievement');
            $table->string('requirement_type')->nullable();
            $table->integer('requirement_value')->default(0);
            $table->integer('points_reward')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // جدول شارات المستخدمين
        Schema::create('gamification_user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('badge_id')->constrained('gamification_badges')->onDelete('cascade');
            $table->timestamp('earned_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'badge_id']);
        });

        // جدول الإنجازات
        Schema::create('gamification_achievements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('tier')->default('bronze');
            $table->string('requirement_type')->nullable();
            $table->integer('requirement_value')->default(0);
            $table->integer('points_reward')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // جدول إنجازات المستخدمين
        Schema::create('gamification_user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('achievement_id')->constrained('gamification_achievements')->onDelete('cascade');
            $table->timestamp('unlocked_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'achievement_id']);
        });

        // جدول التحديات
        Schema::create('gamification_challenges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('type')->default('daily');
            $table->string('target_type')->nullable();
            $table->integer('target_value')->default(1);
            $table->integer('points_reward')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // جدول لوحات المتصدرين
        Schema::create('gamification_leaderboards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('points');
            $table->string('period')->default('all_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // جدول فئات المتجر
        Schema::create('gamification_shop_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // جدول منتجات المتجر
        Schema::create('gamification_shop_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('gamification_shop_categories')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('price_points')->default(0);
            $table->integer('price_gems')->default(0);
            $table->integer('stock')->nullable();
            $table->integer('required_level')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // جدول معاملات النقاط
        Schema::create('gamification_point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('points');
            $table->string('type')->default('bonus');
            $table->string('reason');
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->timestamps();
        });

        // جدول إحصائيات المستخدم للتلعيب
        Schema::create('gamification_user_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('total_points')->default(0);
            $table->integer('total_xp')->default(0);
            $table->integer('current_level')->default(1);
            $table->integer('gems')->default(0);
            $table->integer('current_streak')->default(0);
            $table->integer('longest_streak')->default(0);
            $table->date('last_activity_date')->nullable();
            $table->timestamps();
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gamification_user_stats');
        Schema::dropIfExists('gamification_point_transactions');
        Schema::dropIfExists('gamification_shop_items');
        Schema::dropIfExists('gamification_shop_categories');
        Schema::dropIfExists('gamification_leaderboards');
        Schema::dropIfExists('gamification_challenges');
        Schema::dropIfExists('gamification_user_achievements');
        Schema::dropIfExists('gamification_achievements');
        Schema::dropIfExists('gamification_user_badges');
        Schema::dropIfExists('gamification_badges');
        Schema::dropIfExists('gamification_levels');
    }
};
