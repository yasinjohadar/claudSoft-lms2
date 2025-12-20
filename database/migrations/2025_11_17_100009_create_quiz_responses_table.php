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
        Schema::create('quiz_responses', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('attempt_id')->constrained('quiz_attempts')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('question_bank')->onDelete('cascade');
            $table->foreignId('question_type_id')->constrained('question_types')->onDelete('restrict');

            // Response Content
            $table->text('response_text')->nullable()->comment('For text-based answers');
            $table->json('response_data')->nullable()->comment('Complex answers: MCQ selections, matching pairs, ordering, etc.');
            $table->json('selected_option_ids')->nullable()->comment('Quick access for MCQ');

            // Grading
            $table->boolean('is_correct')->nullable();
            $table->decimal('score_obtained', 8, 2)->nullable();
            $table->decimal('max_score', 8, 2)->default(1.00);

            // Timing
            $table->integer('time_spent')->nullable()->comment('Seconds spent on this question');

            // Review
            $table->boolean('marked_for_review')->default(false);
            $table->integer('answer_order')->default(0)->comment('Sequence of answering');

            // Manual Grading
            $table->text('feedback')->nullable()->comment('Instructor feedback for manual grading');
            $table->boolean('auto_graded')->default(false);
            $table->timestamp('graded_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['attempt_id', 'question_id']);
            $table->index('question_type_id');
            $table->index('is_correct');
            $table->index('auto_graded');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_responses');
    }
};
