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
        Schema::create('ai_student_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('quiz_attempt_id')->nullable()->constrained('quiz_attempts')->onDelete('set null');
            $table->enum('feedback_type', ['performance', 'general', 'improvement']);
            $table->text('feedback_text');
            $table->json('suggestions')->nullable();
            $table->foreignId('ai_model_id')->nullable()->constrained('ai_models')->onDelete('set null');
            $table->integer('tokens_used')->default(0);
            $table->decimal('cost', 10, 6)->default(0);
            $table->timestamps();

            $table->index('student_id');
            $table->index('quiz_attempt_id');
            $table->index('ai_model_id');
            $table->index('feedback_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_student_feedback');
    }
};
