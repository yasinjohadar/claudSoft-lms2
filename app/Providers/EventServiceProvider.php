<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

// Course & Lesson Events
use App\Events\CourseCompleted;
use App\Events\LessonCompleted;

// Assessment Events
use App\Events\QuizCompleted;
use App\Events\QuizStarted;
use App\Events\AssignmentSubmitted;
use App\Events\AssignmentAvailable;
use App\Events\AssignmentGraded;
use App\Events\StudentActivityTracked;

// Payment Events
use App\Events\InvoiceCreated;
use App\Events\PaymentReceived;

// Gamification Events
use App\Events\Gamification\BadgeEarned;
use App\Events\Gamification\AchievementUnlocked;
use App\Events\Gamification\LevelUp;
use App\Events\Gamification\PointsEarned;
use App\Events\Gamification\StreakUpdated;
use App\Events\Gamification\ChallengeCompleted;
use App\Events\Gamification\LeaderboardRankChanged;

// n8n Webhook Events
use App\Events\N8nWebhookEvent;

// Listeners
use App\Listeners\CourseNotificationListener;
use App\Listeners\AssessmentNotificationListener;
use App\Listeners\PaymentNotificationListener;
use App\Listeners\StudentActionNotificationListener;
use App\Listeners\Gamification\SendNotificationListener;
use App\Listeners\Gamification\CheckBadgesListener;
use App\Listeners\N8nWebhookListener;
use App\Listeners\IssueCertificateOnCompletion;

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
            CourseNotificationListener::class . '@handleCourseCompleted',
            StudentActionNotificationListener::class . '@handleCourseCompleted',
            IssueCertificateOnCompletion::class,
            CheckBadgesListener::class,
        ],
        LessonCompleted::class => [
            CourseNotificationListener::class . '@handleLessonCompleted',
            StudentActionNotificationListener::class . '@handleLessonCompleted',
            CheckBadgesListener::class,
        ],

        // Assessment Events
        QuizCompleted::class => [
            AssessmentNotificationListener::class . '@handleQuizCompleted',
            StudentActionNotificationListener::class . '@handleQuizCompleted',
            CheckBadgesListener::class,
        ],
        QuizStarted::class => [
            StudentActionNotificationListener::class . '@handleQuizStarted',
        ],
        AssignmentSubmitted::class => [
            AssessmentNotificationListener::class . '@handleAssignmentSubmitted',
            StudentActionNotificationListener::class . '@handleAssignmentSubmitted',
        ],
        AssignmentAvailable::class => [
            StudentActionNotificationListener::class . '@handleAssignmentAvailable',
        ],
        AssignmentGraded::class => [
            StudentActionNotificationListener::class . '@handleAssignmentGraded',
        ],
        StudentActivityTracked::class => [
            StudentActionNotificationListener::class . '@handleActivityTracked',
        ],

        // Payment Events
        InvoiceCreated::class => [
            PaymentNotificationListener::class . '@handleInvoiceCreated',
        ],
        PaymentReceived::class => [
            PaymentNotificationListener::class . '@handlePaymentReceived',
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
