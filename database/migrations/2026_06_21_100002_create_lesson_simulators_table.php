<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_simulators', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('topic_key');
            $table->json('spec_json');
            $table->string('spec_version')->default('1.0');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->json('languages')->nullable();
            $table->json('ai_generation_meta')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('course_lesson_simulator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_simulator_id')->constrained('lesson_simulators')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['course_id', 'lesson_simulator_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_lesson_simulator');
        Schema::dropIfExists('lesson_simulators');
    }
};
