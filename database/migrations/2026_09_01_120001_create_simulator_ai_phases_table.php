<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulator_ai_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generation_id')
                ->constrained('simulator_ai_generations')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->string('phase', 16); // plan|html|css|js
            $table->string('label');
            $table->string('status', 16)->default('pending'); // pending|done|failed
            $table->longText('content')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['generation_id', 'position']);
            $table->index(['generation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulator_ai_phases');
    }
};
