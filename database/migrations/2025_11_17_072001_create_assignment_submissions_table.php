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
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('assignment_id')->constrained('assignments')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');

            // Submission Content
            $table->text('submission_text')->nullable()->comment('Optional text/notes from student');
            $table->json('submitted_links')->nullable()->comment('Array of URLs submitted by student');
            $table->json('submitted_files')->nullable()->comment('Array of file paths uploaded by student');

            // Submission Status
            $table->enum('status', ['draft', 'submitted', 'graded', 'returned'])->default('draft');
            $table->dateTime('submitted_at')->nullable();
            $table->boolean('is_late')->default(false);

            // Grading
            $table->decimal('grade', 5, 2)->nullable()->comment('Grade received (0.00 to max_grade)');
            $table->text('feedback')->nullable()->comment('Instructor feedback');
            $table->foreignId('graded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('graded_at')->nullable();

            // Attempt Tracking
            $table->integer('attempt_number')->default(1);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('assignment_id');
            $table->index('student_id');
            $table->index('status');
            $table->index(['assignment_id', 'student_id']);
            $table->index('submitted_at');
            $table->index('is_late');

            // Unique constraint: one active submission per student per assignment
            $table->unique(['assignment_id', 'student_id', 'attempt_number'], 'assignment_submission_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};
