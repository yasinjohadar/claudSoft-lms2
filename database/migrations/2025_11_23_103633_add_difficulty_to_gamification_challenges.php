<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gamification_challenges', function (Blueprint $table) {
            $table->string('difficulty')->default('medium')->after('type');
            $table->integer('sort_order')->default(0)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('gamification_challenges', function (Blueprint $table) {
            $table->dropColumn(['difficulty', 'sort_order']);
        });
    }
};
