<?php

namespace App\Listeners\Gamification;

use App\Services\Gamification\LeaderboardService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;

class UpdateLeaderboardListener implements ShouldQueue
{
    public function handle(object $event): void
    {
        $debounceKey = 'gamification.leaderboard_update_debounce';

        if (Cache::has($debounceKey)) {
            return;
        }

        Cache::put($debounceKey, true, now()->addSeconds(60));

        app(LeaderboardService::class)->updateAllLeaderboards();
    }
}
