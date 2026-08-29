<?php

namespace App\Listeners;

use App\Events\CourseCompleted;
use App\Events\LessonCompleted;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Services\Gamification\NotificationService;
use Illuminate\Support\Facades\Log;

class CourseNotificationListener
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle CourseCompleted event
     */
    public function handleCourseCompleted(CourseCompleted $event): void
    {
        try {
            $user = $event->user;
            $course = $event->course;

            // إرسال إشعار إتمام الكورس
            $this->notificationService->send(
                user: $user,
                type: 'course_completed',
                title: 'أكملت كورس بنجاح! 🎉',
                message: "تهانينا! لقد أكملت كورس \"{$course->title}\" بنجاح. يمكنك الآن الحصول على شهادتك.",
                icon: '🎓',
                actionUrl: route('student.progress.certificate', ['courseId' => $course->id]),
                relatedType: get_class($course),
                relatedId: $course->id,
                metadata: [
                    'course_id' => $course->id,
                    'course_title' => $course->title,
                    'completed_at' => now()->toDateTimeString(),
                ]
            );

            Log::info('Course completion notification sent', [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send course completion notification', [
                'error' => $e->getMessage(),
                'user_id' => $event->user->id ?? null,
                'course_id' => $event->course->id ?? null,
            ]);
        }
    }

    /**
     * Handle LessonCompleted event
     */
    public function handleLessonCompleted(LessonCompleted $event): void
    {
        try {
            $user = $event->user;
            $payload = $event->lesson;

            if ($payload instanceof CourseModule) {
                $courseId = $payload->course_id;
                $title = $payload->title;
                $relatedType = CourseModule::class;
                $relatedId = $payload->id;
                $metadataLessonKey = ($payload->module_type === 'lesson' && $payload->modulable_type === Lesson::class)
                    ? $payload->modulable_id
                    : $payload->id;
            } elseif ($payload instanceof Lesson) {
                $lesson = $payload->loadMissing('module');
                $courseId = $lesson->module->course_id ?? null;
                $title = $lesson->title;
                $relatedType = Lesson::class;
                $relatedId = $lesson->id;
                $metadataLessonKey = $lesson->id;
            } else {
                Log::warning('LessonCompleted: unexpected payload type', [
                    'type' => is_object($payload) ? $payload::class : gettype($payload),
                ]);

                return;
            }

            $actionUrl = $courseId ? route('student.learn.continue', ['courseId' => $courseId]) : null;

            // إرسال إشعار إتمام الدرس
            $this->notificationService->send(
                user: $user,
                type: 'lesson_completed',
                title: 'أكملت درساً جديداً! ✅',
                message: "رائع! أكملت درس \"{$title}\". استمر في التقدم!",
                icon: '📖',
                actionUrl: $actionUrl,
                relatedType: $relatedType,
                relatedId: $relatedId,
                metadata: [
                    'lesson_id' => $metadataLessonKey,
                    'lesson_title' => $title,
                    'completed_at' => now()->toDateTimeString(),
                ]
            );

            Log::info('Lesson completion notification sent', [
                'user_id' => $user->id,
                'lesson_id' => $metadataLessonKey,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send lesson completion notification', [
                'error' => $e->getMessage(),
                'user_id' => $event->user->id ?? null,
                'lesson_id' => is_object($event->lesson) ? $event->lesson->id ?? null : null,
            ]);
        }
    }

    /**
     * Register the listeners for the subscriber
     */
    public function subscribe($events): array
    {
        return [
            CourseCompleted::class => 'handleCourseCompleted',
            LessonCompleted::class => 'handleLessonCompleted',
        ];
    }
}
