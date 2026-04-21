<?php

namespace App\Listeners\WapiAutomation;

use App\Events\StudentEnrolledInCourse;
use App\Services\Flaxxa\WapiAutomationService;
use App\WapiAutomation\WapiAutomationEventKey;

class SendWapiOnStudentEnrolledInCourse
{
    public function __construct(
        private WapiAutomationService $automation
    ) {}

    public function handle(StudentEnrolledInCourse $event): void
    {
        if ($event->enrollment->enrollment_status !== 'active') {
            return;
        }

        $this->automation->dispatchForUser(
            WapiAutomationEventKey::STUDENT_ENROLLED_IN_COURSE,
            $event->student,
            [
                'course_id' => $event->course->id,
                'course_title' => $event->course->title,
                'enrollment_date' => $event->enrollment->enrollment_date?->toDateTimeString() ?? '',
            ]
        );
    }
}
