<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_challenges')) {
            return;
        }

        Schema::create('project_challenges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('cover_image')->nullable();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->enum('difficulty', ['easy', 'medium', 'hard', 'expert'])->default('easy');
            $table->enum('project_type', ['team_project', 'open_challenge', 'hackathon', 'capstone'])->default('team_project');
            $table->unsignedInteger('points_total')->default(0);
            $table->string('expected_duration')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('max_teams')->nullable();
            $table->unsignedTinyInteger('min_members')->default(1);
            $table->unsignedTinyInteger('max_members')->default(5);
            $table->boolean('allow_late_join')->default(false);
            $table->enum('team_approval_mode', ['auto', 'admin_approval', 'leader_approval', 'hybrid'])->default('hybrid');
            $table->enum('status', ['draft', 'published', 'archived', 'closed'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->string('language', 5)->default('ar');
            $table->json('settings')->nullable();
            $table->unsignedTinyInteger('showcase_threshold')->default(100);
            $table->foreignId('created_by')->nullable()->constrained('users', 'id', 'pc_challenges_created_fk')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users', 'id', 'pc_challenges_updated_fk')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status', 'pc_challenges_status_idx');
            $table->index('difficulty', 'pc_challenges_difficulty_idx');
            $table->index('project_type', 'pc_challenges_type_idx');
            $table->index('is_featured', 'pc_challenges_featured_idx');
            $table->index(['starts_at', 'ends_at'], 'pc_challenges_dates_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_challenges');
    }
};
