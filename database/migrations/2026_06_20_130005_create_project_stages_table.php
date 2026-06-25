<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_stages')) {
            return;
        }

        Schema::create('project_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_challenge_id')->constrained('project_challenges', 'id', 'pc_stages_challenge_fk')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedSmallInteger('duration_days')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->decimal('max_score', 10, 2)->default(100);
            $table->decimal('weight', 8, 2)->default(1);
            $table->boolean('is_optional')->default(false);
            $table->boolean('unlock_after_previous')->default(true);
            $table->json('allowed_link_types')->nullable();
            $table->enum('status', ['locked', 'open', 'closed'])->default('locked');
            $table->timestamps();

            $table->index(['project_challenge_id', 'sort_order'], 'pc_stages_challenge_order_idx');
            $table->index(['project_challenge_id', 'status'], 'pc_stages_challenge_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_stages');
    }
};
