<?php

namespace App\Listeners\Gamification;

use App\Services\Gamification\BadgeService;
use Illuminate\Support\Facades\Log;

class CheckBadgesListener
{
    public function __construct(
        protected BadgeService $badgeService
    ) {}

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

            $awarded = $this->badgeService->checkAllBadgesWithCascade($user);

            if (count($awarded) > 0) {
                Log::info('Badges automatically awarded', [
                    'user_id' => $user->id,
                    'badges_count' => count($awarded),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to check badges', [
                'user_id' => $event->user->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
