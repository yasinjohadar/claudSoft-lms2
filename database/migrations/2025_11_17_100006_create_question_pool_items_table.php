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
        Schema::create('question_pool_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pool_id')->constrained('question_pools')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('question_bank')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();

            // Indexes and Unique Constraint
            $table->index(['pool_id', 'question_id']);
            $table->unique(['pool_id', 'question_id'], 'pool_question_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_pool_items');
    }
};
