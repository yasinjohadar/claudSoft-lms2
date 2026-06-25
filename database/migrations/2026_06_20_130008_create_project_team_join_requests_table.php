<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_team_join_requests')) {
            return;
        }

        Schema::create('project_team_join_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_team_id')->constrained('project_teams', 'id', 'pc_tjoin_team_fk')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users', 'id', 'pc_tjoin_user_fk')->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users', 'id', 'pc_tjoin_reviewer_fk')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reject_reason')->nullable();
            $table->timestamps();

            $table->index(['project_team_id', 'status'], 'pc_tjoin_team_status_idx');
            $table->index(['user_id', 'status'], 'pc_tjoin_user_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_team_join_requests');
    }
};
