<?php

namespace App\Providers;

use App\Events\AssignmentAvailable;
// Course & Lesson Events
use App\Events\AssignmentGraded;
use App\Events\AssignmentSubmitted;
// Assessment Events
use App\Events\CourseCompleted;
use App\Events\CourseGroupCoursesSynced;
use App\Events\Gamification\AchievementUnlocked;
use App\Events\Gamification\BadgeEarned;
use App\Events\Gamification\ChallengeCompleted;
use App\Events\Gamification\LeaderboardRankChanged;
// Payment Events
use App\Events\Gamification\LevelUp;
use App\Events\Gamification\PointsEarned;
// Gamification Events
use App\Events\Gamification\StreakUpdated;
use App\Events\InvoiceCreated;
use App\Events\LessonBecameVisible;
use App\Events\LessonCompleted;
use App\Events\N8nWebhookEvent;
use App\Events\PaymentReceived;
use App\Events\QuizCompleted;
// n8n Webhook Events
use App\Events\QuizStarted;
use App\Events\StudentActivityTracked;
use App\Events\StudentEnrolledInCourse;
use App\Listeners\AssessmentNotificationListener;
// Listeners
use App\Listeners\CourseNotificationListener;
use App\Listeners\Gamification\SendNotificationListener;
use App\Listeners\IssueCertificateOnCompletion;
use App\Listeners\N8nWebhookListener;
use App\Listeners\PaymentNotificationListener;
use App\Listeners\StudentActionNotificationListener;
use App\Listeners\WapiAutomation\SendWapiOnCourseCompleted;
use App\Listeners\WapiAutomation\SendWapiOnCourseGroupCoursesSynced;
use App\Listeners\WapiAutomation\SendWapiOnLessonBecameVisible;
use App\Listeners\WapiAutomation\SendWapiOnLessonCompleted;
use App\Listeners\WapiAutomation\SendWapiOnQuizCompleted;
use App\Listeners\WapiAutomation\SendWapiOnStudentEnrolledInCourse;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Course & Lesson Events
        CourseCompleted::class => [
            CourseNotificationListener::class.'@handleCourseCompleted',
            StudentActionNotificationListener::class.'@handleCourseCompleted',
            SendWapiOnCourseCompleted::class,
            IssueCertificateOnCompletion::class,
        ],
        LessonCompleted::class => [
            CourseNotificationListener::class.'@handleLessonCompleted',
            StudentActionNotificationListener::class.'@handleLessonCompleted',
            SendWapiOnLessonCompleted::class,
        ],

        // Assessment Events
        QuizCompleted::class => [
            AssessmentNotificationListener::class.'@handleQuizCompleted',
            StudentActionNotificationListener::class.'@handleQuizCompleted',
            SendWapiOnQuizCompleted::class,
        ],
        QuizStarted::class => [
            StudentActionNotificationListener::class.'@handleQuizStarted',
        ],
        AssignmentSubmitted::class => [
            AssessmentNotificationListener::class.'@handleAssignmentSubmitted',
            StudentActionNotificationListener::class.'@handleAssignmentSubmitted',
        ],
        AssignmentAvailable::class => [
            StudentActionNotificationListener::class.'@handleAssignmentAvailable',
        ],
        AssignmentGraded::class => [
            StudentActionNotificationListener::class.'@handleAssignmentGraded',
        ],
        StudentActivityTracked::class => [
            StudentActionNotificationListener::class.'@handleActivityTracked',
        ],

        // Payment Events
        InvoiceCreated::class => [
            PaymentNotificationListener::class.'@handleInvoiceCreated',
        ],
        PaymentReceived::class => [
            PaymentNotificationListener::class.'@handlePaymentReceived',
        ],

        // Gamification Events - already handled by SendNotificationListener
        BadgeEarned::class => [
            SendNotificationListener::class,
        ],
        AchievementUnlocked::class => [
            SendNotificationListener::class,
        ],
        LevelUp::class => [
            SendNotificationListener::class,
        ],
        PointsEarned::class => [
            SendNotificationListener::class,
        ],
        StreakUpdated::class => [
            SendNotificationListener::class,
        ],
        ChallengeCompleted::class => [
            SendNotificationListener::class,
        ],
        LeaderboardRankChanged::class => [
            SendNotificationListener::class,
        ],

        // n8n Webhook Events
        N8nWebhookEvent::class => [
            N8nWebhookListener::class,
        ],

        StudentEnrolledInCourse::class => [
            SendWapiOnStudentEnrolledInCourse::class,
        ],

        LessonBecameVisible::class => [
            SendWapiOnLessonBecameVisible::class,
        ],

        CourseGroupCoursesSynced::class => [
            SendWapiOnCourseGroupCoursesSynced::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
