<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_stage_submissions')) {
            return;
        }

        Schema::create('project_stage_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_team_id')->constrained('project_teams', 'id', 'pc_ssub_team_fk')->cascadeOnDelete();
            $table->foreignId('project_stage_id')->constrained('project_stages', 'id', 'pc_ssub_stage_fk')->cascadeOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users', 'id', 'pc_ssub_submitter_fk')->nullOnDelete();
            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'resubmit_required'])->default('draft');
            $table->decimal('progress_percent', 5, 2)->nullable();
            $table->decimal('score', 10, 2)->nullable();
            $table->decimal('max_score', 10, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users', 'id', 'pc_ssub_reviewer_fk')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('resubmit_deadline')->nullable();
            $table->text('reject_reason')->nullable();
            $table->timestamps();

            $table->unique(['project_team_id', 'project_stage_id'], 'pc_ssub_team_stage_idx');
            $table->index(['project_team_id', 'status'], 'pc_ssub_team_status_idx');
            $table->index(['project_stage_id', 'status'], 'pc_ssub_stage_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_stage_submissions');
    }
};
