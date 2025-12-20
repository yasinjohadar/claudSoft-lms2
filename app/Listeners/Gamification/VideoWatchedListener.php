<?php

namespace App\Listeners\Gamification;

use App\Events\VideoWatched;
use App\Services\Gamification\GamificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class VideoWatchedListener implements ShouldQueue
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
    public function handle(VideoWatched $event): void
    {
        try {
            $result = $this->gamificationService->handleVideoWatch(
                $event->user,
                $event->video->id,
                $event->watchPercentage,
                [
                    'video_title' => $event->video->title ?? '',
                    'duration' => $event->video->duration ?? 0,
                    'watch_time' => $event->watchTime ?? 0,
                ]
            );

            if ($result['success']) {
                Log::info("Gamification: Video watch rewarded", [
                    'user_id' => $event->user->id,
                    'video_id' => $event->video->id,
                    'watch_percentage' => $event->watchPercentage,
                    'points_awarded' => $result['points_awarded'],
                    'xp_awarded' => $result['xp_awarded'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Gamification: Failed to handle video watch", [
                'user_id' => $event->user->id,
                'video_id' => $event->video->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
