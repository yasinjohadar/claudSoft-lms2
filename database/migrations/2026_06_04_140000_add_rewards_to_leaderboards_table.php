<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leaderboards', function (Blueprint $table) {
            if (! Schema::hasColumn('leaderboards', 'rewards')) {
                $table->json('rewards')->nullable()->after('division_thresholds')->comment('مكافآت المراكز');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leaderboards', function (Blueprint $table) {
            if (Schema::hasColumn('leaderboards', 'rewards')) {
                $table->dropColumn('rewards');
            }
        });
    }
};
