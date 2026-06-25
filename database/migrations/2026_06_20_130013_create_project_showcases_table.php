<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_showcases')) {
            return;
        }

        Schema::create('project_showcases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_team_id')->constrained('project_teams', 'id', 'pc_showcase_team_fk')->cascadeOnDelete();
            $table->foreignId('project_challenge_id')->constrained('project_challenges', 'id', 'pc_showcase_challenge_fk')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->string('github_url', 2048)->nullable();
            $table->string('demo_url', 2048)->nullable();
            $table->string('video_url', 2048)->nullable();
            $table->string('cover_image')->nullable();
            $table->json('screenshots')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->enum('status', ['draft', 'published', 'hidden'])->default('draft');
            $table->decimal('avg_rating', 3, 2)->nullable();
            $table->timestamps();

            $table->unique('project_team_id', 'pc_showcase_team_unique_idx');
            $table->index(['project_challenge_id', 'status'], 'pc_showcase_challenge_status_idx');
            $table->index('published_at', 'pc_showcase_published_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_showcases');
    }
};
