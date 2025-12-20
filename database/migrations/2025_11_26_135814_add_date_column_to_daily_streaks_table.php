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
        Schema::table('daily_streaks', function (Blueprint $table) {
            // Add date column for daily tracking
            $table->date('date')->nullable()->after('user_id');

            // Add activity tracking columns
            $table->integer('activities_count')->unsigned()->default(0)->after('date');
            $table->integer('points_earned')->unsigned()->default(0)->after('activities_count');
            $table->integer('xp_earned')->unsigned()->default(0)->after('points_earned');

            // Add indexes for better query performance
            $table->index(['user_id', 'date']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_streaks', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['user_id', 'date']);
            $table->dropIndex(['date']);

            // Drop columns
            $table->dropColumn(['date', 'activities_count', 'points_earned', 'xp_earned']);
        });
    }
};
