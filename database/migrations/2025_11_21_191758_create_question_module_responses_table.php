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
        Schema::create('question_module_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('question_module_attempts')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('question_bank')->onDelete('cascade');
            $table->json('student_answer')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('score_obtained', 8, 2)->nullable();
            $table->decimal('max_score', 8, 2);
            $table->text('feedback')->nullable();
            $table->integer('time_spent')->nullable()->comment('Time spent on this question in seconds');
            $table->timestamps();

            $table->unique(['attempt_id', 'question_id']);
            $table->index('attempt_id');
            $table->index(['question_id', 'is_correct']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_module_responses');
    }
};
