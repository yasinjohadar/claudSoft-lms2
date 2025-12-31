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
        Schema::create('ai_question_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('course_id')->nullable();
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->onDelete('set null');
            $table->enum('source_type', ['lesson_content', 'manual_text', 'topic']);
            $table->text('source_content');
            $table->string('question_type');
            $table->integer('number_of_questions');
            $table->string('difficulty_level');
            $table->foreignId('ai_model_id')->nullable()->constrained('ai_models')->onDelete('set null');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('generated_questions')->nullable();
            $table->integer('tokens_used')->default(0);
            $table->decimal('cost', 10, 6)->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('ai_model_id');
            $table->index('status');
            $table->index('course_id');
            $table->index('lesson_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_question_generations');
    }
};
