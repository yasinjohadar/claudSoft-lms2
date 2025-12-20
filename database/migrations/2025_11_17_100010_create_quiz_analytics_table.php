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
        Schema::create('quiz_analytics', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');

            // Attempt Statistics
            $table->integer('total_attempts')->default(0);
            $table->integer('completed_attempts')->default(0);

            // Score Statistics
            $table->decimal('best_score', 8, 2)->nullable();
            $table->decimal('best_percentage', 5, 2)->nullable();
            $table->decimal('average_score', 8, 2)->nullable();
            $table->decimal('average_percentage', 5, 2)->nullable();

            // Time Statistics
            $table->integer('total_time_spent')->default(0)->comment('Total seconds');
            $table->integer('average_time_spent')->nullable()->comment('Average seconds per attempt');

            // Performance Metrics
            $table->decimal('completion_rate', 5, 2)->nullable()->comment('Percentage of completed vs total attempts');
            $table->decimal('pass_rate', 5, 2)->nullable()->comment('Percentage of passing attempts');
            $table->decimal('improvement_rate', 5, 2)->nullable()->comment('First vs last attempt comparison');

            // Detailed Analysis (JSON)
            $table->json('strengths')->nullable()->comment('Question types with high scores');
            $table->json('weaknesses')->nullable()->comment('Question types with low scores');
            $table->json('question_performance')->nullable()->comment('Detailed per-question analysis');

            // Timestamps
            $table->timestamp('first_attempt_at')->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('last_updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->timestamps();

            // Indexes
            $table->index(['student_id', 'quiz_id']);
            $table->index('course_id');

            // Unique Constraint
            $table->unique(['student_id', 'quiz_id'], 'student_quiz_analytics_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_analytics');
    }
};
