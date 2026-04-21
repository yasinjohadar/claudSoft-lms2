<?php

namespace App\Listeners\WapiAutomation;

use App\Events\CourseGroupCoursesSynced;
use App\Models\Course;
use App\Services\Flaxxa\WapiAutomationService;
use App\WapiAutomation\WapiAutomationEventKey;

class SendWapiOnCourseGroupCoursesSynced
{
    public function __construct(
        private WapiAutomationService $automation
    ) {}

    public function handle(CourseGroupCoursesSynced $event): void
    {
        if ($event->addedCourseIds === []) {
            return;
        }

        $group = $event->group->loadMissing('members.student');
        $courses = Course::query()
            ->whereIn('id', $event->addedCourseIds)
            ->get()
            ->keyBy('id');

        foreach ($event->addedCourseIds as $courseId) {
            $course = $courses->get($courseId);
            if (! $course) {
                continue;
            }

            foreach ($group->members as $membership) {
                $student = $membership->student;
                if (! $student) {
                    continue;
                }

                $this->automation->dispatchForUser(
                    WapiAutomationEventKey::COURSE_GROUP_UPDATED,
                    $student,
                    [
                        'course_id' => $course->id,
                        'course_title' => $course->title,
                        'group_id' => $group->id,
                    ]
                );
            }
        }
    }
}
