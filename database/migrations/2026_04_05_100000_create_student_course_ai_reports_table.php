<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_course_ai_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('course_group_id')->nullable()->constrained('course_groups')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('facts');
            $table->longText('narrative');
            $table->foreignId('laravel_ai_model_id')->nullable()->constrained('laravel_ai_models')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'course_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_course_ai_reports');
    }
};
