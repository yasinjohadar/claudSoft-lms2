<?php

namespace App\Listeners\Gamification;

use App\Events\QuizCompleted;
use App\Services\Gamification\BadgeService;
use App\Services\Gamification\GamificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class QuizCompletedListener implements ShouldQueue
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
    public function handle(QuizCompleted $event): void
    {
        try {
            $result = $this->gamificationService->handleQuizCompletion(
                $event->user,
                $event->quiz->id,
                $event->score,
                $event->totalQuestions,
                [
                    'quiz_title' => $event->quiz->title ?? '',
                    'attempt_id' => $event->attemptId ?? null,
                    'time_taken' => $event->timeTaken ?? 0,
                ]
            );

            if ($result['success']) {
                Log::info("Gamification: Quiz completion rewarded", [
                    'user_id' => $event->user->id,
                    'quiz_id' => $event->quiz->id,
                    'score' => $event->score,
                    'total_questions' => $event->totalQuestions,
                    'points_awarded' => $result['points_awarded'],
                    'xp_awarded' => $result['xp_awarded'],
                ]);
            }

            $this->badgeService->checkAllBadgesWithCascade($event->user);
        } catch (\Exception $e) {
            Log::error("Gamification: Failed to handle quiz completion", [
                'user_id' => $event->user->id,
                'quiz_id' => $event->quiz->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
