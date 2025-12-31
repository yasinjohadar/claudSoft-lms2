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
        Schema::create('essay_grading_rubrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('question_bank')->onDelete('cascade');
            $table->json('criteria')->comment('Grading criteria (JSON structure)');
            $table->decimal('max_score', 8, 2)->default(10.00)->comment('Maximum score for this question');
            $table->boolean('ai_grading_enabled')->default(true)->comment('Enable AI auto-grading');
            $table->json('ai_prompt')->nullable()->comment('Custom AI prompt for grading');
            $table->text('instructions')->nullable()->comment('Grading instructions for AI');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index('question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('essay_grading_rubrics');
    }
};
