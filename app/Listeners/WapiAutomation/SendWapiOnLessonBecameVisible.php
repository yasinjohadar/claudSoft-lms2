<?php

namespace App\Listeners\WapiAutomation;

use App\Events\LessonBecameVisible;
use App\Models\User;
use App\Services\Flaxxa\WapiAutomationService;
use App\WapiAutomation\WapiAutomationEventKey;

class SendWapiOnLessonBecameVisible
{
    public function __construct(
        private WapiAutomationService $automation
    ) {}

    public function handle(LessonBecameVisible $event): void
    {
        $lesson = $event->lesson->loadMissing(['module.course']);
        $module = $lesson->module;
        if (! $module) {
            return;
        }

        $courseId = $module->course_id;
        if (! $courseId) {
            return;
        }

        $course = $module->course ?? $module->course()->first();
        if (! $course) {
            return;
        }

        User::query()
            ->role('student')
            ->whereHas('courseEnrollments', function ($q) use ($courseId) {
                $q->where('course_id', $courseId)
                    ->where('enrollment_status', 'active');
            })
            ->where(function ($q) {
                $q->whereNotNull('phone')->where('phone', '!=', '')
                    ->orWhereNotNull('full_phone')->where('full_phone', '!=', '');
            })
            ->chunkById(100, function ($students) use ($lesson, $module, $course): void {
                foreach ($students as $student) {
                    $this->automation->dispatchForUser(
                        WapiAutomationEventKey::LESSON_BECAME_VISIBLE,
                        $student,
                        [
                            'course_id' => $course->id,
                            'course_title' => $course->title,
                            'module_id' => $module->id,
                            'module_title' => $module->title,
                            'lesson_id' => $lesson->id,
                            'lesson_title' => $lesson->title,
                        ]
                    );
                }
            });
    }
}
