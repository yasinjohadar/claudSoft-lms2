<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_activities')) {
            return;
        }

        Schema::create('project_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_team_id')->constrained('project_teams', 'id', 'pc_activities_team_fk')->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users', 'id', 'pc_activities_actor_fk')->nullOnDelete();
            $table->string('event_type', 100);
            $table->json('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['project_team_id', 'created_at'], 'pc_activities_team_date_idx');
            $table->index('event_type', 'pc_activities_event_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_activities');
    }
};
