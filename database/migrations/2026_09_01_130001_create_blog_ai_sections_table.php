<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_ai_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generation_id')
                ->constrained('blog_ai_generations')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->string('heading');
            $table->text('brief')->nullable();
            $table->string('status', 16)->default('pending'); // pending|done|failed
            $table->longText('html')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['generation_id', 'position']);
            $table->index(['generation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_ai_sections');
    }
};
