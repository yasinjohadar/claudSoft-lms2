<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gamification_user_achievements', function (Blueprint $table) {
            $table->string('status')->default('locked')->after('achievement_id');
            $table->timestamp('completed_at')->nullable()->after('unlocked_at');
            $table->integer('progress_percentage')->default(0)->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('gamification_user_achievements', function (Blueprint $table) {
            $table->dropColumn(['status', 'completed_at', 'progress_percentage']);
        });
    }
};
