<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('programming_challenge_attempts')) {
            return;
        }

        Schema::create('programming_challenge_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programming_challenge_id')->constrained('programming_challenges', 'id', 'pc_attempt_challenge_fk')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users', 'id', 'pc_attempt_user_fk')->cascadeOnDelete();
            $table->foreignId('course_module_id')->nullable()->constrained('course_modules', 'id', 'pc_attempt_module_fk')->nullOnDelete();
            $table->unsignedInteger('attempt_number')->default(1);
            $table->enum('status', ['in_progress', 'submitted', 'graded', 'returned'])->default('in_progress');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->decimal('score', 10, 2)->nullable();
            $table->decimal('max_score', 10, 2)->nullable();
            $table->enum('grade_status', ['pending', 'auto_graded', 'graded'])->nullable();
            $table->text('feedback')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users', 'id', 'pc_attempt_grader_fk')->nullOnDelete();
            $table->timestamps();
            $table->index(['programming_challenge_id', 'user_id'], 'pc_attempt_challenge_user_idx');
            $table->index('status', 'pc_attempt_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programming_challenge_attempts');
    }
};