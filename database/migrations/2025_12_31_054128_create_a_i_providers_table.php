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
        Schema::create('ai_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Provider name (e.g., OpenAI Main, Gemini Pro)');
            $table->enum('type', ['openai', 'gemini', 'glm', 'openrouter', 'custom'])->comment('Provider type');
            $table->text('api_key')->comment('Encrypted API key');
            $table->string('api_url')->nullable()->comment('Custom API URL if needed');
            $table->string('model_name')->default('default')->comment('Default model name');
            $table->json('config')->nullable()->comment('Additional configuration (JSON)');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false)->comment('Default provider to use');
            $table->integer('priority')->default(0)->comment('Priority order (higher = preferred)');
            $table->json('usage_stats')->nullable()->comment('Usage statistics');
            $table->json('rate_limits')->nullable()->comment('Rate limiting configuration');
            $table->timestamps();
            
            $table->index(['type', 'is_active']);
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_providers');
    }
};
