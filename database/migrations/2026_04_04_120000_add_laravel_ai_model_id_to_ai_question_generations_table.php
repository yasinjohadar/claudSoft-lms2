<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_question_generations', function (Blueprint $table) {
            $table->foreignId('laravel_ai_model_id')
                ->nullable()
                ->after('ai_model_id')
                ->constrained('laravel_ai_models')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ai_question_generations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('laravel_ai_model_id');
        });
    }
};
