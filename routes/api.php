<?php

use App\Http\Controllers\Api\N8nWebhookController;
use App\Http\Controllers\Api\Student\AssignmentApiController as StudentAssignmentApiController;
use App\Http\Controllers\Api\Student\AuthController as StudentAuthController;
use App\Http\Controllers\Api\Student\CertificateApiController as StudentCertificateApiController;
use App\Http\Controllers\Api\Student\CourseController as StudentCourseController;
use App\Http\Controllers\Api\Student\DashboardApiController as StudentDashboardApiController;
use App\Http\Controllers\Api\Student\FeedbackApiController as StudentFeedbackApiController;
use App\Http\Controllers\Api\Student\GroupApiController as StudentGroupApiController;
use App\Http\Controllers\Api\Student\PlatformReviewApiController as StudentPlatformReviewApiController;
use App\Http\Controllers\Api\Student\QuestionModuleStatsApiController as StudentQuestionModuleStatsApiController;
use App\Http\Controllers\Api\Student\StudentNotesApiController as StudentStudentNotesApiController;
use App\Http\Controllers\Api\Student\StudyReportApiController as StudentStudyReportApiController;
use App\Http\Controllers\Api\Student\TrainingCampApiController as StudentTrainingCampApiController;
use App\Http\Controllers\Api\Student\WeeklyReportApiController as StudentWeeklyReportApiController;
use App\Http\Controllers\Api\Student\CourseProgressApiController as StudentCourseProgressApiController;
use App\Http\Controllers\Api\Student\ExternalResourceApiController as StudentExternalResourceApiController;
use App\Http\Controllers\Api\Student\Gamification\AchievementApiController as StudentAchievementApiController;
use App\Http\Controllers\Api\Student\Gamification\BadgeApiController as StudentBadgeApiController;
use App\Http\Controllers\Api\Student\Gamification\ChallengeApiController as StudentChallengeApiController;
use App\Http\Controllers\Api\Student\Gamification\LeaderboardApiController as StudentLeaderboardApiController;
use App\Http\Controllers\Api\Student\Gamification\PointsApiController as StudentPointsApiController;
use App\Http\Controllers\Api\Student\Gamification\ShopApiController as StudentShopApiController;
use App\Http\Controllers\Api\Student\Gamification\StreakApiController as StudentStreakApiController;
use App\Http\Controllers\Api\Student\InvoiceController as StudentInvoiceApiController;
use App\Http\Controllers\Api\Student\ModuleProgressApiController as StudentModuleProgressApiController;
use App\Http\Controllers\Api\Student\NotificationController as StudentNotificationApiController;
use App\Http\Controllers\Api\Student\NotificationHubController as StudentNotificationHubApiController;
use App\Http\Controllers\Api\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Api\Student\QuizApiController as StudentQuizApiController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\WhatsAppController;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use App\Http\Controllers\Api\WhatsAppWebWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the framework with the "api" middleware group
| and the /api URI prefix.
|
*/

// ========== Student API (للتطبيقات مثل Flutter) ==========
Route::prefix('student')->name('api.student.')->group(function () {
    Route::post('login', [StudentAuthController::class, 'login'])->name('login');
    Route::post('logout', [StudentAuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
    Route::get('me', [StudentAuthController::class, 'me'])->middleware('auth:sanctum')->name('me');

    // كتالوج عام: بدون توكن أو مع Bearer اختياري لعرض حالة التسجيل للطالب فقط
    Route::middleware(['optional.sanctum'])->group(function () {
        Route::get('catalog', [StudentCourseController::class, 'catalog'])->name('catalog');
        Route::get('catalog/{id}', [StudentCourseController::class, 'catalogShow'])->name('catalog.show');
    });

    Route::middleware(['auth.query_token', 'auth:sanctum', 'role:student'])->group(function () {
        Route::get('invoices/{id}/print', [StudentInvoiceApiController::class, 'printInvoice'])->name('invoices.print');
        Route::get('payments/{id}/print', [StudentInvoiceApiController::class, 'printPayment'])->name('payments.print');
        Route::get('certificates/{certificate}/download', [StudentCertificateApiController::class, 'download'])->name('certificates.download');
        Route::get('courses/{courseId}/certificate', [StudentCourseProgressApiController::class, 'certificate'])->name('courses.certificate');
        Route::get('courses/{courseId}/progress-report', [StudentCourseProgressApiController::class, 'exportReport'])->name('courses.progress-report');
        Route::get('external-resources/{resource}/open', [StudentExternalResourceApiController::class, 'open'])->name('external-resources.open');
    });

    Route::middleware(['log.student.api', 'auth:sanctum', 'role:student', 'student.profile.complete'])->group(function () {
        Route::get('dashboard', [StudentDashboardApiController::class, 'index'])->name('dashboard');

        Route::get('certificates', [StudentCertificateApiController::class, 'index'])->name('certificates.index');
        Route::get('certificates/{certificate}', [StudentCertificateApiController::class, 'show'])->name('certificates.show');

        Route::get('question-modules/stats', [StudentQuestionModuleStatsApiController::class, 'index'])->name('question-modules.stats');
        Route::get('question-modules/{questionModuleId}/stats', [StudentQuestionModuleStatsApiController::class, 'moduleStats'])->name('question-modules.module-stats');

        Route::get('assignments', [StudentAssignmentApiController::class, 'index'])->name('assignments.index');
        Route::get('assignments/{id}', [StudentAssignmentApiController::class, 'show'])->name('assignments.show');
        Route::post('assignments/{id}/submit', [StudentAssignmentApiController::class, 'submit'])->name('assignments.submit');
        Route::post('assignments/{id}/save-draft', [StudentAssignmentApiController::class, 'saveDraft'])->name('assignments.save-draft');

        Route::get('study-reports', [StudentStudyReportApiController::class, 'index'])->name('study-reports.index');
        Route::get('study-reports/{report}', [StudentStudyReportApiController::class, 'show'])->name('study-reports.show');

        Route::get('weekly-reports', [StudentWeeklyReportApiController::class, 'index'])->name('weekly-reports.index');
        Route::get('weekly-reports/{report}', [StudentWeeklyReportApiController::class, 'show'])->name('weekly-reports.show');
        Route::post('weekly-reports/{report}/save', [StudentWeeklyReportApiController::class, 'save'])->name('weekly-reports.save');
        Route::post('weekly-reports/{report}/submit', [StudentWeeklyReportApiController::class, 'submit'])->name('weekly-reports.submit');
        Route::get('weekly-reports/courses/{course}/lessons', [StudentWeeklyReportApiController::class, 'lessons'])->name('weekly-reports.lessons');

        Route::get('ai-feedback', [StudentFeedbackApiController::class, 'index'])->name('ai-feedback.index');
        Route::get('ai-feedback/{feedback}', [StudentFeedbackApiController::class, 'show'])->name('ai-feedback.show');

        Route::get('groups', [StudentGroupApiController::class, 'index'])->name('groups.index');
        Route::post('groups/{group}/request', [StudentGroupApiController::class, 'requestMembership'])->name('groups.request');

        Route::get('training-camps', [StudentTrainingCampApiController::class, 'index'])->name('training-camps.index');
        Route::get('training-camps/{camp}', [StudentTrainingCampApiController::class, 'show'])->name('training-camps.show');

        Route::get('platform-review', [StudentPlatformReviewApiController::class, 'show'])->name('platform-review.show');
        Route::post('platform-review', [StudentPlatformReviewApiController::class, 'store'])->name('platform-review.store');
        Route::put('platform-review/{review}', [StudentPlatformReviewApiController::class, 'update'])->name('platform-review.update');

        Route::get('notes', [StudentStudentNotesApiController::class, 'notes'])->name('notes.index');
        Route::post('notes', [StudentStudentNotesApiController::class, 'storeNote'])->name('notes.store');
        Route::put('notes/{note}', [StudentStudentNotesApiController::class, 'updateNote'])->name('notes.update');
        Route::delete('notes/{note}', [StudentStudentNotesApiController::class, 'deleteNote'])->name('notes.delete');
        Route::get('course-notes', [StudentStudentNotesApiController::class, 'courseNotes'])->name('course-notes.index');
        Route::get('reminders', [StudentStudentNotesApiController::class, 'reminders'])->name('reminders.index');
        Route::get('calendar/events', [StudentStudentNotesApiController::class, 'calendarEvents'])->name('calendar.events');
        Route::get('student-works', [StudentStudentNotesApiController::class, 'works'])->name('student-works.index');

        Route::get('profile', [StudentProfileController::class, 'show'])->name('profile.show');
        Route::put('profile', [StudentProfileController::class, 'update'])->name('profile.update');
        Route::get('nationalities', [StudentProfileController::class, 'nationalities'])->name('nationalities');

        Route::get('courses', [StudentCourseController::class, 'index'])->name('courses.index');
        Route::get('courses/progress-overview', [StudentCourseProgressApiController::class, 'progressOverview'])->name('courses.progress-overview');
        Route::get('courses/{courseId}/progress', [StudentCourseProgressApiController::class, 'progress'])->name('courses.progress');
        Route::post('courses/{id}/enroll', [StudentCourseController::class, 'enroll'])->name('courses.enroll');
        Route::delete('courses/{id}/enroll', [StudentCourseController::class, 'unenroll'])->name('courses.unenroll');

        Route::post('modules/{moduleId}/mark-complete', [StudentModuleProgressApiController::class, 'markComplete'])->name('modules.mark-complete');
        Route::post('modules/{moduleId}/mark-incomplete', [StudentModuleProgressApiController::class, 'markIncomplete'])->name('modules.mark-incomplete');

        Route::get('external-resources', [StudentExternalResourceApiController::class, 'index'])->name('external-resources.index');

        Route::get('invoices', [StudentInvoiceApiController::class, 'invoices'])->name('invoices.index');
        Route::get('invoices/{id}', [StudentInvoiceApiController::class, 'invoice'])->name('invoices.show');
        Route::get('payments', [StudentInvoiceApiController::class, 'payments'])->name('payments.index');
        Route::get('payments/{id}', [StudentInvoiceApiController::class, 'payment'])->name('payments.show');

        Route::get('notifications/unread-count', [StudentNotificationApiController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::post('notifications/mark-all-read', [StudentNotificationApiController::class, 'markAllRead'])->name('notifications.mark-all-read');
        Route::get('notifications', [StudentNotificationApiController::class, 'index'])->name('notifications.index');
        Route::post('notifications/{id}/mark-read', [StudentNotificationApiController::class, 'markRead'])->name('notifications.mark-read');

        Route::prefix('notification-hub')->name('notification-hub.')->group(function () {
            Route::get('inbox', [StudentNotificationHubApiController::class, 'inbox'])->name('inbox');
            Route::get('unread-count', [StudentNotificationHubApiController::class, 'unreadCount'])->name('unread-count');
            Route::post('mark-all-read', [StudentNotificationHubApiController::class, 'markAllRead'])->name('mark-all-read');
            Route::post('{id}/mark-read', [StudentNotificationHubApiController::class, 'markRead'])->name('mark-read');

            Route::post('devices/register-token', [StudentNotificationHubApiController::class, 'registerDeviceToken'])->name('devices.register-token');
            Route::post('devices/unregister-token', [StudentNotificationHubApiController::class, 'unregisterDeviceToken'])->name('devices.unregister-token');
            Route::get('preferences', [StudentNotificationHubApiController::class, 'preferences'])->name('preferences.index');
            Route::post('preferences', [StudentNotificationHubApiController::class, 'savePreference'])->name('preferences.save');
        });

        Route::prefix('gamification')->name('gamification.')->group(function () {
            Route::get('leaderboards', [StudentLeaderboardApiController::class, 'index'])->name('leaderboards.index');
            Route::get('leaderboards/{leaderboard}', [StudentLeaderboardApiController::class, 'show'])->name('leaderboards.show');

            Route::get('badges', [StudentBadgeApiController::class, 'index'])->name('badges.index');

            Route::get('achievements', [StudentAchievementApiController::class, 'index'])->name('achievements.index');
            Route::post('achievements/{userAchievement}/claim', [StudentAchievementApiController::class, 'claim'])->name('achievements.claim');

            Route::get('challenges', [StudentChallengeApiController::class, 'index'])->name('challenges.index');
            Route::post('challenges/{challenge}/accept', [StudentChallengeApiController::class, 'accept'])->name('challenges.accept');

            Route::get('shop', [StudentShopApiController::class, 'index'])->name('shop.index');
            Route::get('shop/items/{item}', [StudentShopApiController::class, 'show'])->name('shop.items.show');
            Route::post('shop/items/{item}/purchase', [StudentShopApiController::class, 'purchase'])->name('shop.items.purchase');

            Route::get('points', [StudentPointsApiController::class, 'index'])->name('points.index');
            Route::get('points/history', [StudentPointsApiController::class, 'history'])->name('points.history');

            Route::get('streak', [StudentStreakApiController::class, 'index'])->name('streak.index');
            Route::get('streak/calendar', [StudentStreakApiController::class, 'calendar'])->name('streak.calendar');
        });

        Route::prefix('quizzes')->name('quizzes.')->group(function () {
            Route::get('{id}/preview', [StudentQuizApiController::class, 'preview'])->name('preview');
            Route::post('{id}/start', [StudentQuizApiController::class, 'start'])->name('start');
            Route::get('attempts', [StudentQuizApiController::class, 'myAttempts'])->name('attempts.index');
            Route::get('attempts/{attempt}', [StudentQuizApiController::class, 'showAttempt'])->name('attempts.show');
            Route::post('attempts/{attempt}/answer', [StudentQuizApiController::class, 'saveAnswer'])->name('attempts.answer');
            Route::post('attempts/{attempt}/submit', [StudentQuizApiController::class, 'submit'])->name('attempts.submit');
        });
    });
});

// Flaxxa WAPI (Sanctum + admin)
Route::prefix('wapi/whatsapp')
    ->name('api.wapi.whatsapp.')
    ->middleware(['auth:sanctum', 'role:admin', 'throttle:wapi-send'])
    ->group(function () {
        Route::post('send-message', [WhatsAppController::class, 'sendMessage'])->name('send-message');
        Route::post('send-template', [WhatsAppController::class, 'sendTemplate'])->name('send-template');
        Route::post('send-campaign', [WhatsAppController::class, 'sendCampaign'])->name('send-campaign');
        Route::post('preview-template', [WhatsAppController::class, 'previewTemplate'])->name('preview-template');
    });

// Webhook Routes (Public - no auth required, but signature verification)
Route::prefix('webhooks')->name('api.webhooks.')->group(function () {

    Route::get('/test', [WebhookController::class, 'test'])->name('test');

    Route::post('/wpforms', [WebhookController::class, 'wpforms'])
        ->middleware('webhook.verify:wpforms')
        ->name('wpforms');

    Route::prefix('n8n')->name('n8n.')->group(function () {
        Route::post('/incoming', [N8nWebhookController::class, 'incoming'])
            ->middleware('webhook.verify:n8n')
            ->name('incoming');

        Route::get('/handlers', [N8nWebhookController::class, 'handlers'])
            ->name('handlers');

        Route::get('/handlers/{handlerType}', [N8nWebhookController::class, 'handlerDocs'])
            ->name('handler.docs');
    });

    Route::prefix('whatsapp')
        ->name('whatsapp.')
        ->middleware(['throttle:60,1'])
        ->group(function () {
            Route::get('/', [WhatsAppWebhookController::class, 'verify'])->name('verify');
            Route::post('/', [WhatsAppWebhookController::class, 'handle'])->name('handle');
        });

    Route::prefix('whatsapp-web')
        ->name('whatsapp-web.')
        ->middleware(['throttle:120,1'])
        ->group(function () {
            Route::post('/incoming', [WhatsAppWebWebhookController::class, 'handleIncoming'])->name('incoming');
        });

    Route::prefix('evolution')
        ->name('evolution.')
        ->middleware(['throttle:120,1'])
        ->group(function () {
            Route::post('/{instance?}', [\App\Http\Controllers\Api\EvolutionWebhookController::class, 'handle'])
                ->where('instance', '[a-zA-Z0-9_-]+')
                ->name('handle');
        });
});
