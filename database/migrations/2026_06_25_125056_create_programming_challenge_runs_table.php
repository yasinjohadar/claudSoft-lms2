<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('programming_challenge_runs')) {
            return;
        }

        Schema::create('programming_challenge_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programming_challenge_attempt_id')->nullable()->constrained('programming_challenge_attempts', 'id', 'pc_runs_attempt_fk')->cascadeOnDelete();
            $table->foreignId('programming_challenge_submission_id')->nullable()->constrained('programming_challenge_submissions', 'id', 'pc_runs_submission_fk')->cascadeOnDelete();
            $table->string('trigger', 20)->default('run');
            $table->string('runtime_slug')->nullable();
            $table->longText('stdout')->nullable();
            $table->longText('stderr')->nullable();
            $table->integer('exit_code')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->json('test_results')->nullable();
            $table->timestamps();
            $table->index('programming_challenge_attempt_id', 'pc_runs_attempt_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programming_challenge_runs');
    }
};