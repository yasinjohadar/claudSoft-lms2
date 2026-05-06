<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_weekly_report_schedules', function (Blueprint $table) {
            $table->foreignId('target_course_id')
                ->nullable()
                ->after('target_scope')
                ->constrained('courses')
                ->nullOnDelete();

            $table->foreignId('target_group_id')
                ->nullable()
                ->after('target_course_id')
                ->constrained('course_groups')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_weekly_report_schedules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('target_group_id');
            $table->dropConstrainedForeignId('target_course_id');
        });
    }
};
