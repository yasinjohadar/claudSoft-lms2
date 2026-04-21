<?php

namespace App\Listeners\WapiAutomation;

use App\Events\LessonCompleted;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Services\Flaxxa\WapiAutomationService;
use App\WapiAutomation\WapiAutomationEventKey;

class SendWapiOnLessonCompleted
{
    public function __construct(
        private WapiAutomationService $automation
    ) {}

    public function handle(LessonCompleted $event): void
    {
        $payload = $event->lesson;

        if ($payload instanceof CourseModule) {
            $module = $payload->loadMissing('course');
            $lessonId = ($module->module_type === 'lesson' && $module->modulable_type === Lesson::class)
                ? $module->modulable_id
                : $module->id;
            $context = [
                'lesson_id' => $lessonId,
                'lesson_title' => $module->title,
                'module_id' => $module->id,
                'module_title' => $module->title,
                'course_id' => $module->course_id,
                'course_title' => $module->course->title ?? null,
            ];
            $this->automation->dispatchForUser(
                WapiAutomationEventKey::LESSON_COMPLETED,
                $event->user,
                $context
            );

            return;
        }

        if (! $payload instanceof Lesson) {
            return;
        }

        $lesson = $payload;
        $lesson->loadMissing(['module.course']);

        $context = [
            'lesson_id' => $lesson->id,
            'lesson_title' => $lesson->title,
        ];

        $module = $lesson->module;
        if ($module) {
            $context['module_id'] = $module->id;
            $context['module_title'] = $module->title;
            $context['course_id'] = $module->course_id;
            if ($module->relationLoaded('course') && $module->course) {
                $context['course_title'] = $module->course->title;
            }
        }

        $this->automation->dispatchForUser(
            WapiAutomationEventKey::LESSON_COMPLETED,
            $event->user,
            $context
        );
    }
}
