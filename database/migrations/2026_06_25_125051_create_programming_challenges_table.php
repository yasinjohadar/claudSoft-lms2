<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('programming_challenges')) {
            return;
        }

        Schema::create('programming_challenges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('instructions')->nullable();
            $table->enum('challenge_type', ['web_sandbox', 'code_runner'])->default('web_sandbox');
            $table->enum('grading_mode', ['manual', 'auto', 'hybrid'])->default('manual');
            $table->enum('difficulty', ['easy', 'medium', 'hard', 'expert'])->default('easy');
            $table->decimal('max_score', 10, 2)->default(100);
            $table->unsignedInteger('time_limit_seconds')->nullable();
            $table->unsignedInteger('attempts_allowed')->default(3);
            $table->boolean('allow_resubmit')->default(true);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_standalone')->default(false);
            $table->json('starter_layout')->nullable();
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index('challenge_type');
            $table->index('is_published');
            $table->index('is_standalone');
            $table->index('difficulty');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programming_challenges');
    }
};