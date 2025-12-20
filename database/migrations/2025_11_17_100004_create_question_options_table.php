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
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();

            // Relationship
            $table->foreignId('question_id')->constrained('question_bank')->onDelete('cascade');

            // Option Content
            $table->text('option_text');
            $table->string('option_image')->nullable();

            // Grading
            $table->boolean('is_correct')->default(false);
            $table->decimal('grade_percentage', 5, 2)->default(100.00)->comment('Percentage of grade for partial credit');

            // Order
            $table->integer('option_order')->default(0);

            // For Matching Questions
            $table->integer('match_pair_id')->nullable()->comment('For matching questions');

            // Feedback
            $table->text('feedback')->nullable()->comment('Shown when this option is selected');

            $table->timestamps();

            // Indexes
            $table->index(['question_id', 'option_order']);
            $table->index('is_correct');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
