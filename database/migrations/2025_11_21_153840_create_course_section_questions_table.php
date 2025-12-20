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
        Schema::create('course_section_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_section_id')->constrained('course_sections')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('question_bank')->onDelete('cascade');
            $table->integer('question_order')->default(0);
            $table->decimal('question_grade', 8, 2)->nullable();
            $table->boolean('is_required')->default(true);
            $table->json('settings')->nullable(); // For question-specific settings within section
            $table->timestamps();

            // Indexes
            $table->index(['course_section_id', 'question_order']);
            $table->unique(['course_section_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_section_questions');
    }
};
