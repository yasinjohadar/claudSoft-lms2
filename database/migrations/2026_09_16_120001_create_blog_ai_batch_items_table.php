<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_ai_batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('blog_ai_batches')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            // TEXT from the start: a topic is a full brief, not a headline. The
            // documentation twin shipped as varchar(500) and needed an ALTER.
            $table->text('topic');
            $table->string('status', 32)->default('pending'); // pending|running|resume_queued|completed|failed|skipped
            $table->foreignId('blog_ai_generation_id')->nullable();
            $table->foreign('blog_ai_generation_id', 'blog_batch_items_generation_fk')
                ->references('id')->on('blog_ai_generations')->nullOnDelete();
            $table->foreignId('blog_post_id')->nullable()
                ->constrained('blog_posts')->nullOnDelete();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'status']);
            $table->index(['batch_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_ai_batch_items');
    }
};
