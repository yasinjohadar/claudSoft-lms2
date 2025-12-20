<?php

namespace App\Listeners\Gamification;

use App\Events\AssignmentSubmitted;
use App\Services\Gamification\GamificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class AssignmentSubmittedListener implements ShouldQueue
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
    public function handle(AssignmentSubmitted $event): void
    {
        try {
            $result = $this->gamificationService->handleAssignmentSubmission(
                $event->user,
                $event->assignment->id,
                [
                    'assignment_title' => $event->assignment->title ?? '',
                    'submission_id' => $event->submissionId ?? null,
                    'submitted_at' => now()->toDateTimeString(),
                ]
            );

            if ($result['success']) {
                Log::info("Gamification: Assignment submission rewarded", [
                    'user_id' => $event->user->id,
                    'assignment_id' => $event->assignment->id,
                    'points_awarded' => $result['points_awarded'],
                    'xp_awarded' => $result['xp_awarded'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Gamification: Failed to handle assignment submission", [
                'user_id' => $event->user->id,
                'assignment_id' => $event->assignment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
