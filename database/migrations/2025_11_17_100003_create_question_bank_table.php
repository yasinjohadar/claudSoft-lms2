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
        Schema::create('question_bank', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('cascade')->comment('Null = global question');
            $table->foreignId('question_type_id')->constrained('question_types')->onDelete('restrict');

            // Question Content
            $table->longText('question_text');
            $table->string('question_image')->nullable();
            $table->text('explanation')->nullable()->comment('Shown after answering');

            // Grading
            $table->decimal('default_grade', 8, 2)->default(1.00);
            $table->enum('difficulty_level', ['easy', 'medium', 'hard', 'expert'])->default('medium');

            // Metadata (question-type specific settings)
            $table->json('metadata')->nullable()->comment('Type-specific settings: tolerance, keywords, formulas, etc.');

            // Organization
            $table->json('tags')->nullable()->comment('Tags for categorization');

            // Statistics
            $table->integer('times_used')->default(0)->comment('How many times used in quizzes');
            $table->decimal('average_score', 5, 2)->nullable()->comment('Average student score on this question');

            // Status
            $table->boolean('is_active')->default(true);

            // Auditing
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['course_id', 'question_type_id']);
            $table->index('difficulty_level');
            $table->index('created_by');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_bank');
    }
};
