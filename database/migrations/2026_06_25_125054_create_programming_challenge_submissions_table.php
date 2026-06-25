<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('programming_challenge_submissions')) {
            return;
        }

        Schema::create('programming_challenge_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programming_challenge_attempt_id')->constrained('programming_challenge_attempts', 'id', 'pc_submission_attempt_fk')->cascadeOnDelete();
            $table->unsignedInteger('submission_number')->default(1);
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->text('student_notes')->nullable();
            $table->timestamps();
            $table->index(['programming_challenge_attempt_id', 'status'], 'pc_submission_attempt_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programming_challenge_submissions');
    }
};