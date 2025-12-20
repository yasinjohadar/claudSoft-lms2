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
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');

            // Attempt Info
            $table->integer('attempt_number')->default(1);
            $table->enum('status', ['in_progress', 'submitted', 'graded', 'abandoned', 'reviewing'])->default('in_progress');

            // Timing
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('submitted_at')->nullable();
            $table->integer('time_spent')->nullable()->comment('Time spent in seconds');

            // Scoring
            $table->decimal('total_score', 8, 2)->nullable();
            $table->decimal('percentage_score', 5, 2)->nullable();
            $table->decimal('max_score', 8, 2)->default(0);
            $table->boolean('passed')->nullable();

            // Grading Status
            $table->enum('grade_status', ['not_graded', 'partially_graded', 'fully_graded', 'auto_graded'])->default('not_graded');

            // Late Submission
            $table->boolean('is_late')->default(false);

            // Randomization
            $table->json('questions_order')->nullable()->comment('Store question order for this attempt');

            // Feedback
            $table->text('feedback')->nullable();

            // Grading
            $table->foreignId('graded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('graded_at')->nullable();

            // Security
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Completion Tracking - IMPORTANT FOR "COMPLETED" BUTTON
            $table->timestamp('completed_at')->nullable()->comment('When student completed/submitted');
            $table->boolean('is_completed')->default(false)->comment('Quick flag for completion');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['quiz_id', 'student_id', 'attempt_number']);
            $table->index('status');
            $table->index('is_completed');
            $table->index('graded_by');

            // Unique Constraint
            $table->unique(['quiz_id', 'student_id', 'attempt_number'], 'quiz_attempt_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
