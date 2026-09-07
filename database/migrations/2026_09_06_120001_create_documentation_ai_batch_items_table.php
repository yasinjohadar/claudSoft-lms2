<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentation_ai_batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('documentation_ai_batches')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->string('topic', 500);
            $table->string('status', 32)->default('pending'); // pending|running|completed|failed|skipped
            $table->foreignId('documentation_ai_generation_id')->nullable();
            $table->foreign('documentation_ai_generation_id', 'batch_items_generation_fk')
                ->references('id')->on('documentation_ai_generations')->nullOnDelete();
            $table->foreignId('documentation_page_id')->nullable()
                ->constrained('documentation_pages')->nullOnDelete();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'status']);
            $table->index(['batch_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentation_ai_batch_items');
    }
};
