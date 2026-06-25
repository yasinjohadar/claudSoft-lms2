<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_teams')) {
            return;
        }

        Schema::create('project_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_challenge_id')->constrained('project_challenges', 'id', 'pc_teams_challenge_fk')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('leader_id')->nullable()->constrained('users', 'id', 'pc_teams_leader_fk')->nullOnDelete();
            $table->enum('status', ['pending', 'active', 'disqualified', 'completed'])->default('pending');
            $table->decimal('total_score', 10, 2)->default(0);
            $table->decimal('progress_percent', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['project_challenge_id', 'slug'], 'pc_teams_challenge_slug_idx');
            $table->index(['project_challenge_id', 'status'], 'pc_teams_challenge_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_teams');
    }
};
