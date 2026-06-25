<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_team_members')) {
            return;
        }

        Schema::create('project_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_team_id')->constrained('project_teams', 'id', 'pc_tmembers_team_fk')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users', 'id', 'pc_tmembers_user_fk')->cascadeOnDelete();
            $table->string('role', 50)->default('member');
            $table->enum('status', ['active', 'removed', 'left'])->default('active');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['project_team_id', 'user_id'], 'pc_tmembers_team_user_idx');
            $table->index(['project_team_id', 'status'], 'pc_tmembers_team_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_team_members');
    }
};
