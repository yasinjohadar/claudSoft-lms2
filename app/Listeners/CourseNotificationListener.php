<?php

namespace App\Listeners;

use App\Events\CourseCompleted;
use App\Events\LessonCompleted;
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
            $lesson = $event->lesson;
            $courseId = $lesson->module->course_id ?? null;
            $actionUrl = $courseId ? route('student.learn.course', ['courseId' => $courseId]) : null;

            // إرسال إشعار إتمام الدرس
            $this->notificationService->send(
                user: $user,
                type: 'lesson_completed',
                title: 'أكملت درساً جديداً! ✅',
                message: "رائع! أكملت درس \"{$lesson->title}\". استمر في التقدم!",
                icon: '📖',
                actionUrl: $actionUrl,
                relatedType: get_class($lesson),
                relatedId: $lesson->id,
                metadata: [
                    'lesson_id' => $lesson->id,
                    'lesson_title' => $lesson->title,
                    'completed_at' => now()->toDateTimeString(),
                ]
            );

            Log::info('Lesson completion notification sent', [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send lesson completion notification', [
                'error' => $e->getMessage(),
                'user_id' => $event->user->id ?? null,
                'lesson_id' => $event->lesson->id ?? null,
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
