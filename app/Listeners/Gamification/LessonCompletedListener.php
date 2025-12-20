<?php

namespace App\Listeners\Gamification;

use App\Events\LessonCompleted;
use App\Services\Gamification\GamificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LessonCompletedListener implements ShouldQueue
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
    public function handle(LessonCompleted $event): void
    {
        try {
            $result = $this->gamificationService->handleLessonCompletion(
                $event->user,
                $event->lesson->id,
                [
                    'lesson_title' => $event->lesson->title ?? '',
                    'course_id' => $event->lesson->course_id ?? null,
                ]
            );

            if ($result['success']) {
                Log::info("Gamification: Lesson completion rewarded", [
                    'user_id' => $event->user->id,
                    'lesson_id' => $event->lesson->id,
                    'points_awarded' => $result['points_awarded'],
                    'xp_awarded' => $result['xp_awarded'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Gamification: Failed to handle lesson completion", [
                'user_id' => $event->user->id,
                'lesson_id' => $event->lesson->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
