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
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->foreignId('question_id')->nullable()->constrained('question_bank')->onDelete('cascade')->comment('Null if using pool');
            $table->foreignId('question_pool_id')->nullable()->constrained('question_pools')->onDelete('cascade')->comment('For random selection');

            // Pool Configuration
            $table->integer('questions_to_select')->default(1)->comment('Number of questions to select from pool');

            // Order and Grading
            $table->integer('question_order')->default(0);
            $table->decimal('question_grade', 8, 2)->nullable()->comment('Override default grade');

            // Settings
            $table->boolean('is_required')->default(true);

            $table->timestamps();

            // Indexes
            $table->index(['quiz_id', 'question_order']);
            $table->index('question_id');
            $table->index('question_pool_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
