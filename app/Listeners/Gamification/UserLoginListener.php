<?php

namespace App\Listeners\Gamification;

use Illuminate\Auth\Events\Login;
use App\Services\Gamification\GamificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UserLoginListener implements ShouldQueue
{
    use InteractsWithQueue;

    protected GamificationService $gamificationService;

    /**
     * Create the event listener.
     */
    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        try {
            // التحقق من أن المستخدم هو طالب فقط
            if (!$event->user || $event->user->role !== 'student') {
                return;
            }

            $result = $this->gamificationService->handleDailyLogin($event->user);

            if (isset($result['is_new_streak_day']) && $result['is_new_streak_day']) {
                Log::info("Gamification: Daily login rewarded", [
                    'user_id' => $event->user->id,
                    'current_streak' => $result['current_streak'],
                    'multiplier' => $result['multiplier'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Gamification: Failed to handle user login", [
                'user_id' => $event->user?->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
