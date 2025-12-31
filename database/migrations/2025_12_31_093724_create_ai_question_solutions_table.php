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
        Schema::create('ai_question_solutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('question_bank')->onDelete('cascade');
            $table->foreignId('ai_model_id')->nullable()->constrained('ai_models')->onDelete('set null');
            $table->text('solution');
            $table->text('explanation')->nullable();
            $table->decimal('confidence_score', 3, 2)->default(0.5);
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->integer('tokens_used')->default(0);
            $table->decimal('cost', 10, 6)->default(0);
            $table->timestamps();

            $table->index('question_id');
            $table->index('ai_model_id');
            $table->index('is_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_question_solutions');
    }
};
