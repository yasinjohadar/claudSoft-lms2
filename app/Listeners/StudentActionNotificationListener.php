<?php

namespace App\Listeners;

use App\Events\AssignmentAvailable;
use App\Events\AssignmentGraded;
use App\Events\AssignmentSubmitted;
use App\Events\CourseCompleted;
use App\Events\LessonCompleted;
use App\Events\QuizCompleted;
use App\Events\QuizStarted;
use App\Events\StudentActivityTracked;
use App\Models\User;
use App\Services\Notifications\NotificationHubService;

class StudentActionNotificationListener
{
    public function __construct(
        protected NotificationHubService $hub
    ) {}

    public function handleLessonCompleted(LessonCompleted $event): void
    {
        $this->hub->sendToUser($event->user, 'student.lesson.completed', [
            'student_name' => $event->user->name,
            'lesson_title' => $event->lesson->title ?? 'الدرس',
            'course_id' => $event->lesson->course_id ?? null,
        ]);
    }

    public function handleCourseCompleted(CourseCompleted $event): void
    {
        $this->hub->sendToUser($event->user, 'student.course.completed', [
            'student_name' => $event->user->name,
            'course_title' => $event->course->title ?? $event->course->name ?? 'الكورس',
            'course_id' => $event->course->id ?? null,
        ]);
    }

    public function handleQuizStarted(QuizStarted $event): void
    {
        $this->hub->sendToUser($event->user, 'student.quiz.started', [
            'student_name' => $event->user->name,
            'quiz_title' => $event->quiz->title ?? 'الاختبار',
            'quiz_id' => $event->quiz->id,
            'course_id' => $event->quiz->course_id,
            'attempt_id' => $event->attemptId,
            'attempt_number' => $event->attemptNumber,
        ]);
    }

    public function handleQuizCompleted(QuizCompleted $event): void
    {
        $this->hub->sendToUser($event->user, 'student.quiz.completed', [
            'student_name' => $event->user->name,
            'quiz_title' => $event->quiz->title ?? 'الاختبار',
            'quiz_id' => $event->quiz->id,
            'course_id' => $event->quiz->course_id,
            'score' => $event->score,
            'total_questions' => $event->totalQuestions,
            'attempt_id' => $event->attemptId,
            'time_taken' => $event->timeTaken,
        ]);
    }

    public function handleAssignmentSubmitted(AssignmentSubmitted $event): void
    {
        $this->hub->sendToUser($event->user, 'student.assignment.submitted', [
            'student_name' => $event->user->name,
            'assignment_title' => $event->assignment->title ?? 'الواجب',
            'assignment_id' => $event->assignment->id ?? null,
            'course_id' => $event->assignment->course_id ?? null,
            'submission_id' => $event->submission->id ?? null,
        ]);
    }

    public function handleAssignmentAvailable(AssignmentAvailable $event): void
    {
        $users = User::query()->whereIn('id', $event->studentIds->all())->get();
        foreach ($users as $user) {
            $this->hub->sendToUser($user, 'student.assignment.available', [
                'student_name' => $user->name,
                'assignment_title' => $event->assignment->title ?? 'الواجب',
                'assignment_id' => $event->assignment->id,
                'course_id' => $event->assignment->course_id,
                'due_date' => optional($event->assignment->due_date)?->toDateString(),
            ]);
        }
    }

    public function handleAssignmentGraded(AssignmentGraded $event): void
    {
        $submission = $event->submission->loadMissing(['student', 'assignment']);
        $student = $submission->student;

        if (! $student) {
            return;
        }

        $this->hub->sendToUser($student, 'student.assignment.graded', [
            'student_name' => $student->name,
            'assignment_title' => $submission->assignment?->title ?? 'الواجب',
            'assignment_id' => $submission->assignment_id,
            'submission_id' => $submission->id,
            'course_id' => $submission->assignment?->course_id,
            'grade' => $submission->grade,
        ]);
    }

    public function handleActivityTracked(StudentActivityTracked $event): void
    {
        $this->hub->sendToUser($event->user, 'student.activity.tracked', [
            'student_name' => $event->user->name,
            'activity_key' => $event->activityKey,
            ...$event->context,
        ]);
    }
}
