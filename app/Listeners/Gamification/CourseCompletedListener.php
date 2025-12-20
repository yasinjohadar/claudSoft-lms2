<?php

namespace App\Listeners\Gamification;

use App\Events\CourseCompleted;
use App\Services\Gamification\GamificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CourseCompletedListener implements ShouldQueue
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
    public function handle(CourseCompleted $event): void
    {
        try {
            $result = $this->gamificationService->handleCourseCompletion(
                $event->user,
                $event->course->id,
                [
                    'course_title' => $event->course->title ?? '',
                    'completion_rate' => $event->completionRate ?? 100,
                    'total_lessons' => $event->totalLessons ?? 0,
                    'completed_at' => now()->toDateTimeString(),
                ]
            );

            if ($result['success']) {
                Log::info("Gamification: Course completion rewarded", [
                    'user_id' => $event->user->id,
                    'course_id' => $event->course->id,
                    'points_awarded' => $result['points_awarded'],
                    'xp_awarded' => $result['xp_awarded'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Gamification: Failed to handle course completion", [
                'user_id' => $event->user->id,
                'course_id' => $event->course->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
