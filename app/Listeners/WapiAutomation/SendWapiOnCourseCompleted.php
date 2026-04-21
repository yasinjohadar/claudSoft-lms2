<?php

namespace App\Listeners\WapiAutomation;

use App\Events\CourseCompleted;
use App\Services\Flaxxa\WapiAutomationService;
use App\WapiAutomation\WapiAutomationEventKey;

class SendWapiOnCourseCompleted
{
    public function __construct(
        private WapiAutomationService $automation
    ) {}

    public function handle(CourseCompleted $event): void
    {
        $this->automation->dispatchForUser(
            WapiAutomationEventKey::COURSE_COMPLETED,
            $event->user,
            [
                'course_id' => $event->course->id,
                'course_title' => $event->course->title,
            ]
        );
    }
}
