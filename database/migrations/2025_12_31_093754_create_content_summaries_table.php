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
        Schema::create('content_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('summarizable_type');
            $table->unsignedBigInteger('summarizable_id');
            $table->text('summary_text');
            $table->string('summary_type')->default('short');
            $table->foreignId('ai_model_id')->nullable()->constrained('ai_models')->onDelete('set null');
            $table->integer('tokens_used')->default(0);
            $table->decimal('cost', 10, 6)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['summarizable_type', 'summarizable_id']);
            $table->index('ai_model_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_summaries');
    }
};
