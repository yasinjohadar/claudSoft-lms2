<?php

namespace App\Listeners\Gamification;

use App\Events\LessonCompleted;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Services\Gamification\BadgeService;
use App\Services\Gamification\GamificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LessonCompletedListener implements ShouldQueue
{
    use InteractsWithQueue;

    protected GamificationService $gamificationService;
    protected BadgeService $badgeService;

    /**
     * Create the event listener.
     */
    public function __construct(
        GamificationService $gamificationService,
        BadgeService $badgeService
    ) {
        $this->gamificationService = $gamificationService;
        $this->badgeService = $badgeService;
    }

    /**
     * Handle the event.
     */
    public function handle(LessonCompleted $event): void
    {
        try {
            $payload = $event->lesson;

            if ($payload instanceof CourseModule) {
                $lessonPrimaryKey = ($payload->module_type === 'lesson' && $payload->modulable_type === Lesson::class)
                    ? (int) $payload->modulable_id
                    : (int) $payload->id;
                $meta = [
                    'lesson_title' => $payload->title ?? '',
                    'course_id' => $payload->course_id,
                ];
            } elseif ($payload instanceof Lesson) {
                $payload->loadMissing('module');
                $lessonPrimaryKey = (int) $payload->id;
                $meta = [
                    'lesson_title' => $payload->title ?? '',
                    'course_id' => $payload->module?->course_id,
                ];
            } else {
                return;
            }

            $result = $this->gamificationService->handleLessonCompletion(
                $event->user,
                $lessonPrimaryKey,
                $meta
            );

            if ($result['success']) {
                Log::info('Gamification: Lesson completion rewarded', [
                    'user_id' => $event->user->id,
                    'lesson_id' => $lessonPrimaryKey,
                    'points_awarded' => $result['points_awarded'],
                    'xp_awarded' => $result['xp_awarded'],
                ]);
            }

            $this->badgeService->checkAllBadgesWithCascade($event->user);
        } catch (\Exception $e) {
            Log::error('Gamification: Failed to handle lesson completion', [
                'user_id' => $event->user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
