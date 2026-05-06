<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_weekly_report_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_weekly_report_id')->constrained('student_weekly_reports')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['student_weekly_report_id', 'lesson_id'], 'weekly_report_lesson_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_weekly_report_lessons');
    }
};

