<?php

namespace App\Listeners\WapiAutomation;

use App\Events\QuizCompleted;
use App\Services\Flaxxa\WapiAutomationService;
use App\WapiAutomation\WapiAutomationEventKey;

class SendWapiOnQuizCompleted
{
    public function __construct(
        private WapiAutomationService $automation
    ) {}

    public function handle(QuizCompleted $event): void
    {
        $quiz = $event->quiz;
        $quiz->loadMissing('course');

        $context = [
            'quiz_id' => $quiz->id,
            'quiz_title' => $quiz->title,
            'score' => $event->score,
            'total_questions' => $event->totalQuestions,
            'attempt_id' => $event->attemptId,
            'time_taken' => $event->timeTaken,
        ];

        if ($quiz->course_id) {
            $context['course_id'] = $quiz->course_id;
            if ($quiz->relationLoaded('course') && $quiz->course) {
                $context['course_title'] = $quiz->course->title;
            }
        }

        if ($quiz->lesson_id) {
            $context['lesson_id'] = $quiz->lesson_id;
        }

        $this->automation->dispatchForUser(
            WapiAutomationEventKey::QUIZ_COMPLETED,
            $event->user,
            $context
        );
    }
}
