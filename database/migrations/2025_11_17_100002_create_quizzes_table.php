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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->onDelete('cascade');

            // Basic Information
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('instructions')->nullable();

            // Quiz Configuration
            $table->enum('quiz_type', ['practice', 'graded', 'final_exam', 'survey'])->default('graded');
            $table->decimal('passing_grade', 5, 2)->default(60.00)->comment('Percentage required to pass');
            $table->decimal('max_score', 8, 2)->default(100.00)->comment('Maximum score - calculated from questions');

            // Time and Attempts
            $table->integer('time_limit')->nullable()->comment('Time limit in minutes');
            $table->integer('attempts_allowed')->nullable()->comment('Null = unlimited attempts');

            // Randomization
            $table->boolean('shuffle_questions')->default(false)->comment('Randomize question order');
            $table->boolean('shuffle_answers')->default(false)->comment('Randomize answer options');

            // Feedback Settings
            $table->boolean('show_correct_answers')->default(true);
            $table->enum('show_correct_answers_after', ['immediately', 'after_due', 'after_graded', 'never'])->default('after_graded');
            $table->enum('feedback_mode', ['immediate', 'after_submission', 'after_due', 'manual'])->default('after_submission');
            $table->boolean('allow_review')->default(true)->comment('Allow students to review attempts');
            $table->boolean('show_grade_immediately')->default(true);

            // Scheduling
            $table->dateTime('available_from')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->dateTime('available_until')->nullable();

            // Visibility
            $table->boolean('is_published')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->integer('sort_order')->default(0);

            // Auditing
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['course_id', 'is_published']);
            $table->index('lesson_id');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
