<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_challenge_skill')) {
            return;
        }

        Schema::create('project_challenge_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_challenge_id')->constrained('project_challenges', 'id', 'pc_cskill_challenge_fk')->cascadeOnDelete();
            $table->foreignId('project_skill_id')->constrained('project_skills', 'id', 'pc_cskill_skill_fk')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_challenge_id', 'project_skill_id'], 'pc_cskill_unique_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_challenge_skill');
    }
};
