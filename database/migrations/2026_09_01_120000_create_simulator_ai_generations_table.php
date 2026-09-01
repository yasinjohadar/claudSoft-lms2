<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulator_ai_generations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('lesson_simulator_id')->nullable()->constrained('lesson_simulators')->nullOnDelete();
            $table->string('operation', 32); // generate|refine
            $table->string('status', 32)->default('queued'); // queued|running|completed|failed|cancelled|paused
            $table->unsignedTinyInteger('progress')->default(0);
            $table->string('stage', 64)->nullable();
            $table->string('stage_label')->nullable();
            $table->json('payload');
            $table->json('partial_result')->nullable();
            $table->json('result')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('heartbeat_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['operation', 'status']);
            $table->index(['lesson_simulator_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulator_ai_generations');
    }
};
