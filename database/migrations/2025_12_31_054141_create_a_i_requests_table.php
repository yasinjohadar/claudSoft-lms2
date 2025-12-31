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
        Schema::create('ai_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('ai_providers')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('request_type', [
                'question_generation',
                'quiz_generation',
                'essay_grading',
                'content_creation',
                'content_translation',
                'personalized_learning',
                'student_support',
                'analytics',
                'other'
            ])->comment('Type of AI request');
            $table->json('input_data')->nullable()->comment('Input data sent to AI');
            $table->json('response_data')->nullable()->comment('Response from AI');
            $table->integer('tokens_used')->default(0)->comment('Total tokens used');
            $table->integer('input_tokens')->default(0)->comment('Input tokens');
            $table->integer('output_tokens')->default(0)->comment('Output tokens');
            $table->decimal('cost', 10, 6)->default(0)->comment('Cost in USD');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->string('model_used')->nullable()->comment('Model name used');
            $table->integer('response_time_ms')->nullable()->comment('Response time in milliseconds');
            $table->timestamps();
            
            $table->index(['provider_id', 'status']);
            $table->index(['user_id', 'request_type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_requests');
    }
};
