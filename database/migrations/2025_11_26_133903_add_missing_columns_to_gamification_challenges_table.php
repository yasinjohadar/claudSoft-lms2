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
        Schema::table('gamification_challenges', function (Blueprint $table) {
            // Add missing columns
            $table->string('slug')->unique()->nullable()->after('name')->comment('معرف فريد للتحدي');
            $table->integer('reward_xp')->unsigned()->default(0)->after('points_reward')->comment('مكافأة XP');
            $table->integer('reward_gems')->unsigned()->default(0)->after('reward_xp')->comment('مكافأة الجواهر');
            $table->foreignId('badge_id')->nullable()->after('reward_gems')->constrained('gamification_badges')->nullOnDelete();
            $table->boolean('auto_assign')->default(false)->after('is_active')->comment('تعيين تلقائي للمستخدمين');

            // Add index
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gamification_challenges', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropForeign(['badge_id']);
            $table->dropColumn(['slug', 'reward_xp', 'reward_gems', 'badge_id', 'auto_assign']);
        });
    }
};
