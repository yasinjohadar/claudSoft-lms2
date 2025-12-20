<?php

namespace App\Listeners;

use App\Events\QuizCompleted;
use App\Events\AssignmentSubmitted;
use App\Services\Gamification\NotificationService;
use Illuminate\Support\Facades\Log;

class AssessmentNotificationListener
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle QuizCompleted event
     */
    public function handleQuizCompleted(QuizCompleted $event): void
    {
        try {
            $user = $event->user;
            $quiz = $event->quiz;
            $score = $event->score;
            $totalQuestions = $event->totalQuestions;

            // حساب النسبة المئوية
            $percentage = $totalQuestions > 0 ? round(($score / $totalQuestions) * 100, 1) : 0;

            // تحديد نوع الإشعار حسب النتيجة
            $passingScore = $quiz->passing_score ?? 50;
            $isPassed = $percentage >= $passingScore;

            if ($isPassed) {
                // إشعار النجاح
                $this->notificationService->send(
                    user: $user,
                    type: 'quiz_passed',
                    title: 'نجحت في الاختبار! 🎉',
                    message: "تهانينا! حصلت على {$percentage}% في اختبار \"{$quiz->title}\". أحسنت!",
                    icon: '✅',
                    actionUrl: route('student.quizzes.review.show', ['attemptId' => $event->attemptId]),
                    relatedType: get_class($quiz),
                    relatedId: $quiz->id,
                    metadata: [
                        'quiz_id' => $quiz->id,
                        'quiz_title' => $quiz->title,
                        'score' => $score,
                        'total_questions' => $totalQuestions,
                        'percentage' => $percentage,
                        'passed' => true,
                        'attempt_id' => $event->attemptId,
                        'time_taken' => $event->timeTaken,
                    ]
                );
            } else {
                // إشعار عدم النجاح
                $this->notificationService->send(
                    user: $user,
                    type: 'quiz_failed',
                    title: 'نتيجة الاختبار',
                    message: "حصلت على {$percentage}% في اختبار \"{$quiz->title}\". يمكنك المحاولة مرة أخرى لتحسين نتيجتك!",
                    icon: '📝',
                    actionUrl: route('student.quizzes.show', ['id' => $quiz->id]),
                    relatedType: get_class($quiz),
                    relatedId: $quiz->id,
                    metadata: [
                        'quiz_id' => $quiz->id,
                        'quiz_title' => $quiz->title,
                        'score' => $score,
                        'total_questions' => $totalQuestions,
                        'percentage' => $percentage,
                        'passed' => false,
                        'attempt_id' => $event->attemptId,
                    ]
                );
            }

            Log::info('Quiz completion notification sent', [
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'passed' => $isPassed,
                'percentage' => $percentage,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send quiz completion notification', [
                'error' => $e->getMessage(),
                'user_id' => $event->user->id ?? null,
                'quiz_id' => $event->quiz->id ?? null,
            ]);
        }
    }

    /**
     * Handle AssignmentSubmitted event
     */
    public function handleAssignmentSubmitted(AssignmentSubmitted $event): void
    {
        try {
            $user = $event->user;
            $assignment = $event->assignment;

            // إرسال إشعار تأكيد تسليم الواجب
            $this->notificationService->send(
                user: $user,
                type: 'assignment_submitted',
                title: 'تم تسليم الواجب بنجاح! 📤',
                message: "تم تسليم واجب \"{$assignment->title}\" بنجاح. سيتم مراجعته وتصحيحه قريباً.",
                icon: '✓',
                actionUrl: route('student.assignments.show', ['id' => $assignment->id]),
                relatedType: get_class($assignment),
                relatedId: $assignment->id,
                metadata: [
                    'assignment_id' => $assignment->id,
                    'assignment_title' => $assignment->title,
                    'submitted_at' => now()->toDateTimeString(),
                ]
            );

            Log::info('Assignment submission notification sent', [
                'user_id' => $user->id,
                'assignment_id' => $assignment->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send assignment submission notification', [
                'error' => $e->getMessage(),
                'user_id' => $event->user->id ?? null,
            ]);
        }
    }

    /**
     * Register the listeners for the subscriber
     */
    public function subscribe($events): array
    {
        return [
            QuizCompleted::class => 'handleQuizCompleted',
            AssignmentSubmitted::class => 'handleAssignmentSubmitted',
        ];
    }
}
