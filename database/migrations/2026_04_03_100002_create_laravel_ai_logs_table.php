<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laravel_ai_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laravel_ai_model_id')->constrained('laravel_ai_models')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('operation');
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->string('status', 32);
            $table->text('error_message')->nullable();
            $table->unsignedInteger('latency_ms')->default(0);
            $table->timestamps();

            $table->index(['laravel_ai_model_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laravel_ai_logs');
    }
};
