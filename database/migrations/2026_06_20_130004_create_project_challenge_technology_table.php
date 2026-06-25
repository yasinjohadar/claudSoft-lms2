<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_challenge_technology')) {
            return;
        }

        Schema::create('project_challenge_technology', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_challenge_id')->constrained('project_challenges', 'id', 'pc_ctech_challenge_fk')->cascadeOnDelete();
            $table->foreignId('project_technology_id')->constrained('project_technologies', 'id', 'pc_ctech_technology_fk')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_challenge_id', 'project_technology_id'], 'pc_ctech_unique_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_challenge_technology');
    }
};
