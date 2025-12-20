<?php

namespace App\Listeners\Gamification;

use App\Services\Gamification\BadgeService;
use App\Services\Gamification\AchievementService;
use Illuminate\Support\Facades\Log;

class CheckBadgesListener
{
    protected BadgeService $badgeService;
    protected AchievementService $achievementService;

    /**
     * Create the event listener.
     */
    public function __construct(
        BadgeService $badgeService,
        AchievementService $achievementService
    ) {
        $this->badgeService = $badgeService;
        $this->achievementService = $achievementService;
    }

    /**
     * التحقق من الشارات بعد أي حدث
     */
    public function handle($event): void
    {
        if (!isset($event->user)) {
            return;
        }

        try {
            $user = $event->user;

            // التحقق من جميع الشارات
            $awarded = $this->badgeService->checkAllBadges($user);

            if (count($awarded) > 0) {
                Log::info("Badges automatically awarded", [
                    'user_id' => $user->id,
                    'badges_count' => count($awarded),
                ]);
            }

            // التحقق من جميع الإنجازات
            $completed = $this->achievementService->checkAllAchievements($user);

            if (count($completed) > 0) {
                Log::info("Achievements automatically completed", [
                    'user_id' => $user->id,
                    'achievements_count' => count($completed),
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Failed to check badges and achievements", [
                'user_id' => $event->user->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
