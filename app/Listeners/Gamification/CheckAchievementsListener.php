<?php

namespace App\Listeners\Gamification;

use App\Services\Gamification\AchievementService;
use Illuminate\Support\Facades\Log;

class CheckAchievementsListener
{
    public function __construct(
        protected AchievementService $achievementService
    ) {}

    /**
     * التحقق من الإنجازات بعد أي حدث
     */
    public function handle($event): void
    {
        if (!isset($event->user)) {
            return;
        }

        try {
            $completed = $this->achievementService->checkAllAchievements($event->user);

            if (count($completed) > 0) {
                Log::info('Achievements automatically completed', [
                    'user_id' => $event->user->id,
                    'achievements_count' => count($completed),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to check achievements', [
                'user_id' => $event->user->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
