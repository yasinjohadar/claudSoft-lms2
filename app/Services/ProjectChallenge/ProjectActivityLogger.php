<?php

namespace App\Services\ProjectChallenge;

use App\Models\ProjectChallenge\ProjectActivity;
use App\Models\ProjectChallenge\ProjectTeam;
use App\Models\User;

class ProjectActivityLogger
{
    public function log(
        ProjectTeam $team,
        string $eventType,
        ?User $actor = null,
        array $payload = []
    ): ProjectActivity {
        return ProjectActivity::create([
            'project_team_id' => $team->id,
            'actor_id' => $actor?->id,
            'event_type' => $eventType,
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }
}
