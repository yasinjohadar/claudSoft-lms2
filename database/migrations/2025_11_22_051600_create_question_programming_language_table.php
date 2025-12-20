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
        Schema::create('question_programming_language', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('question_bank')->onDelete('cascade');
            $table->foreignId('programming_language_id')->constrained('programming_languages')->onDelete('cascade');
            $table->timestamps();

            // Indexes
            $table->index('question_id');
            $table->index('programming_language_id');
            $table->unique(['question_id', 'programming_language_id'], 'question_lang_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_programming_language');
    }
};
