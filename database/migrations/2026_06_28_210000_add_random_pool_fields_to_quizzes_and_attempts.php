<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->unsignedInteger('questions_per_attempt')
                ->nullable()
                ->after('attempts_allowed')
                ->comment('For random_pool: how many questions shown per attempt');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE quizzes MODIFY quiz_type ENUM('practice', 'graded', 'final_exam', 'survey', 'random_pool') NOT NULL DEFAULT 'graded'");
        }

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->json('selection_meta')
                ->nullable()
                ->after('questions_order')
                ->comment('Random pool selection metadata');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn('selection_meta');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('questions_per_attempt');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE quizzes MODIFY quiz_type ENUM('practice', 'graded', 'final_exam', 'survey') NOT NULL DEFAULT 'graded'");
        }
    }
};
