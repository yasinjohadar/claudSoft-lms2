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
        Schema::create('question_module_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_module_id')->constrained('question_modules')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->integer('attempt_number')->default(1);
            $table->enum('status', ['in_progress', 'completed', 'abandoned'])->default('in_progress');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('time_spent')->nullable()->comment('Time spent in seconds');
            $table->decimal('total_score', 8, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->boolean('is_passed')->default(false);
            $table->json('question_order')->nullable()->comment('Order of questions for this attempt');
            $table->timestamps();

            $table->unique(['question_module_id', 'student_id', 'attempt_number'], 'qm_attempts_unique');
            $table->index(['student_id', 'status'], 'qm_attempts_student_status');
            $table->index(['question_module_id', 'status'], 'qm_attempts_module_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_module_attempts');
    }
};
