<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_weekly_report_lessons', function (Blueprint $table) {
            $table->foreignId('module_id')
                ->nullable()
                ->after('lesson_id')
                ->constrained('course_modules')
                ->cascadeOnDelete();

            $table->unique(['student_weekly_report_id', 'module_id'], 'weekly_report_module_unique');
        });

        DB::statement('ALTER TABLE student_weekly_report_lessons MODIFY lesson_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        Schema::table('student_weekly_report_lessons', function (Blueprint $table) {
            $table->dropUnique('weekly_report_module_unique');
            $table->dropConstrainedForeignId('module_id');
        });

        DB::statement('ALTER TABLE student_weekly_report_lessons MODIFY lesson_id BIGINT UNSIGNED NOT NULL');
    }
};
