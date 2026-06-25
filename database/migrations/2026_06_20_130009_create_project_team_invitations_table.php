<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_team_invitations')) {
            return;
        }

        Schema::create('project_team_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_team_id')->constrained('project_teams', 'id', 'pc_tinv_team_fk')->cascadeOnDelete();
            $table->foreignId('invited_user_id')->constrained('users', 'id', 'pc_tinv_invited_fk')->cascadeOnDelete();
            $table->foreignId('invited_by')->constrained('users', 'id', 'pc_tinv_inviter_fk')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['project_team_id', 'status'], 'pc_tinv_team_status_idx');
            $table->index(['invited_user_id', 'status'], 'pc_tinv_user_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_team_invitations');
    }
};
