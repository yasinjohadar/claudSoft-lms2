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
        Schema::create('question_module_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_module_id')->constrained('question_modules')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('question_bank')->onDelete('cascade');
            $table->integer('question_order')->default(0);
            $table->decimal('question_grade', 8, 2)->default(1.00);
            $table->timestamps();

            // Indexes
            $table->index('question_module_id');
            $table->index('question_id');
            $table->unique(['question_module_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_module_questions');
    }
};
