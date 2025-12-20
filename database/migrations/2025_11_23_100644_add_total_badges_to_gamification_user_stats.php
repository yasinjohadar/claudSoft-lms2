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
        Schema::table('gamification_user_stats', function (Blueprint $table) {
            $table->integer('total_badges')->default(0)->after('gems');
            $table->integer('total_achievements')->default(0)->after('total_badges');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gamification_user_stats', function (Blueprint $table) {
            $table->dropColumn(['total_badges', 'total_achievements']);
        });
    }
};
