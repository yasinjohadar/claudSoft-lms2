<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_weekly_reports', function (Blueprint $table) {
            $table->foreignId('target_course_id')
                ->nullable()
                ->after('created_by_admin_id')
                ->constrained('courses')
                ->nullOnDelete();

            $table->foreignId('target_group_id')
                ->nullable()
                ->after('target_course_id')
                ->constrained('course_groups')
                ->nullOnDelete();

            $table->index(['target_group_id', 'status'], 'weekly_reports_group_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('student_weekly_reports', function (Blueprint $table) {
            $table->dropIndex('weekly_reports_group_status_idx');
            $table->dropConstrainedForeignId('target_group_id');
            $table->dropConstrainedForeignId('target_course_id');
        });
    }
};
