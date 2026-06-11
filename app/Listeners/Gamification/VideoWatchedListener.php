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

    public function __construct(
        protected GamificationService $gamificationService
    ) {}

    public function handle(VideoWatched $event): void
    {
        try {
            $minPercentage = (int) config('gamification.points.video_watch.min_watch_percentage', 80);

            if ($event->watchPercentage < $minPercentage) {
                return;
            }

            $result = $this->gamificationService->handleVideoWatch(
                $event->user,
                $event->module->id,
                (int) round($event->watchPercentage),
                [
                    'module_title' => $event->module->title ?? '',
                    'watch_time' => $event->watchedSeconds,
                    'duration' => $event->totalSeconds,
                ]
            );

            if ($result['success'] ?? false) {
                Log::info('Gamification: Video watch rewarded', [
                    'user_id' => $event->user->id,
                    'module_id' => $event->module->id,
                    'watch_percentage' => $event->watchPercentage,
                    'points_awarded' => $result['points_awarded'] ?? 0,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Gamification: Failed to handle video watch', [
                'user_id' => $event->user->id,
                'module_id' => $event->module->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
