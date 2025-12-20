<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\StudentProfileController;
use App\Http\Controllers\Student\TrainingCampController;
use App\Http\Controllers\Student\InvoiceController;
use App\Http\Controllers\Student\CourseController;
use App\Http\Controllers\Student\CourseLearningController;
use App\Http\Controllers\Student\CourseProgressController;
use App\Http\Controllers\Student\QuizAttemptController;
use App\Http\Controllers\Student\QuizReviewController;
use App\Http\Controllers\Student\QuestionModuleAttemptController;
use App\Http\Controllers\Student\QuestionModuleStatsController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\Gamification\DashboardController as GamificationDashboardController;
use App\Http\Controllers\Student\Gamification\PointsController as StudentPointsController;
use App\Http\Controllers\Student\Gamification\LevelController as StudentLevelController;
use App\Http\Controllers\Student\Gamification\StreakController as StudentStreakController;
use App\Http\Controllers\Student\Gamification\BadgeController as StudentBadgeController;
use App\Http\Controllers\Student\Gamification\AchievementController as StudentAchievementController;
use App\Http\Controllers\Student\Gamification\LeaderboardController as StudentLeaderboardController;
use App\Http\Controllers\Student\Gamification\ChallengeController as StudentChallengeController;
use App\Http\Controllers\Student\Gamification\ShopController as StudentShopController;
use App\Http\Controllers\Student\Gamification\InventoryController as StudentInventoryController;
use App\Http\Controllers\Student\Gamification\FriendshipController as StudentFriendshipController;
use App\Http\Controllers\Student\Gamification\CompetitionController as StudentCompetitionController;
use App\Http\Controllers\Student\Gamification\SocialActivityController as StudentSocialActivityController;
use App\Http\Controllers\Student\Gamification\NotificationController as StudentNotificationController;
use App\Http\Controllers\Student\NotificationPreferencesController;
use App\Http\Controllers\Student\NoteController;
use App\Http\Controllers\Student\CourseNoteController;
use App\Http\Controllers\Student\ReminderController as StudentReminderController;
use App\Http\Controllers\Student\CalendarController;
use App\Http\Controllers\Student\StudentWorkController;
use App\Http\Controllers\Student\CourseReviewController;

Route::prefix('student')
    ->middleware(['auth', 'role:student'])
    ->group(function () {

        // Dashboard
        Route::get('/', [StudentDashboardController::class, 'index'])->name('student.dashboard');

        // Profile Routes
        Route::prefix('profile')->name('student.profile.')->group(function () {
            Route::get('/', [StudentProfileController::class, 'index'])->name('index');
            Route::get('/edit', [StudentProfileController::class, 'edit'])->name('edit');
            Route::put('/update', [StudentProfileController::class, 'update'])->name('update');
            Route::put('/change-password', [StudentProfileController::class, 'changePassword'])->name('change-password');
            Route::delete('/delete-photo', [StudentProfileController::class, 'deletePhoto'])->name('delete-photo');
        });

        // Training Camps Routes
        Route::prefix('training-camps')->name('student.training-camps.')->group(function () {
            Route::get('/', [TrainingCampController::class, 'index'])->name('index');
            Route::get('/my/enrollments', [TrainingCampController::class, 'myEnrollments'])->name('my-enrollments');
            Route::post('/{id}/enroll', [TrainingCampController::class, 'enroll'])->name('enroll');
            Route::post('/{id}/cancel', [TrainingCampController::class, 'cancelEnrollment'])->name('cancel-enrollment');
            Route::get('/{slug}', [TrainingCampController::class, 'show'])->name('show');
        });

        // Invoices & Payments Routes
        Route::prefix('invoices')->name('student.invoices.')->group(function () {
            Route::get('/', [InvoiceController::class, 'index'])->name('index');
            Route::get('/{id}', [InvoiceController::class, 'show'])->name('show');
        });

        Route::get('/payments', [InvoiceController::class, 'payments'])->name('student.payments.index');
        Route::get('/payments/{id}', [InvoiceController::class, 'showPayment'])->name('student.payments.show');

        // ========== Course Learning Routes ==========

        // Browse Courses (Catalog)
        Route::prefix('courses')->name('student.courses.')->group(function () {
            Route::get('/', [CourseController::class, 'index'])->name('index'); // Browse all courses
            Route::get('/my-courses', [CourseController::class, 'myCourses'])->name('my-courses'); // My enrolled courses
            Route::get('/{id}/preview', [CourseController::class, 'show'])->name('show'); // Preview course before enrollment
            Route::post('/{id}/enroll', [CourseController::class, 'enroll'])->name('enroll'); // Enroll in course
            Route::delete('/{id}/unenroll', [CourseController::class, 'unenroll'])->name('unenroll'); // Unenroll from course
        });

        // Course Learning (صفحة التعلم - Learning Page)
        Route::prefix('learn')->name('student.learn.')->group(function () {
            Route::get('/courses/{courseId}', [CourseLearningController::class, 'show'])->name('course'); // Main learning page
            Route::get('/courses/{courseId}/continue', [CourseController::class, 'learn'])->name('continue'); // Continue learning from last point

            // Module Content Display
            Route::get('/modules/{moduleId}', [CourseLearningController::class, 'showModule'])->name('module'); // Show module content

            // Mark as Complete - زر "تم الإنجاز" ✅
            Route::post('/modules/{moduleId}/mark-complete', [CourseLearningController::class, 'markAsComplete'])->name('module.mark-complete');
            Route::post('/modules/{moduleId}/mark-incomplete', [CourseLearningController::class, 'markAsIncomplete'])->name('module.mark-incomplete');

            // Video Progress Tracking
            Route::post('/modules/{moduleId}/track-video-progress', [CourseLearningController::class, 'trackVideoProgress'])->name('module.track-video');

            // Resource Download
            Route::get('/modules/{moduleId}/download-resource', [CourseLearningController::class, 'downloadResource'])->name('module.download-resource');
        });

        // Course Progress & Statistics
        Route::prefix('progress')->name('student.progress.')->group(function () {
            Route::get('/courses/{courseId}', [CourseProgressController::class, 'show'])->name('show'); // Progress report for a course
            Route::get('/overview', [CourseProgressController::class, 'overview'])->name('overview'); // Overview of all courses
            Route::get('/courses/{courseId}/certificate', [CourseProgressController::class, 'certificate'])->name('certificate'); // Download certificate
            Route::get('/courses/{courseId}/certificate/view', [CourseProgressController::class, 'viewCertificate'])->name('certificate.view'); // View certificate online
            Route::get('/courses/{courseId}/export', [CourseProgressController::class, 'exportReport'])->name('export'); // Export progress report
            Route::get('/courses/{courseId}/stats', [CourseProgressController::class, 'getStats'])->name('stats'); // AJAX: Get statistics
        });

        // ========== Assignments Routes (Student) ==========
        Route::prefix('assignments')->name('student.assignments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Student\AssignmentSubmissionController::class, 'index'])->name('index'); // List all assignments
            Route::get('/{id}', [\App\Http\Controllers\Student\AssignmentSubmissionController::class, 'show'])->name('show'); // View assignment
            Route::post('/{id}/submit', [\App\Http\Controllers\Student\AssignmentSubmissionController::class, 'submit'])->name('submit'); // Submit assignment
            Route::post('/{id}/save-draft', [\App\Http\Controllers\Student\AssignmentSubmissionController::class, 'saveDraft'])->name('save-draft'); // Save draft
            Route::delete('/submissions/{submissionId}/delete-file', [\App\Http\Controllers\Student\AssignmentSubmissionController::class, 'deleteFile'])->name('delete-file'); // Delete file
        });

        // ========== Quizzes Routes (Student) ==========

        // Browse & Take Quizzes
        Route::prefix('quizzes')->name('student.quizzes.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Student\QuizAttemptController::class, 'index'])->name('index'); // Browse available quizzes
            Route::get('/{id}', [\App\Http\Controllers\Student\QuizAttemptController::class, 'show'])->name('show'); // View quiz details before starting
            Route::post('/{id}/start', [\App\Http\Controllers\Student\QuizAttemptController::class, 'start'])->name('start'); // Start new attempt
            Route::get('/attempt/{attemptId}/take', [\App\Http\Controllers\Student\QuizAttemptController::class, 'take'])->name('take'); // Take quiz interface
            Route::post('/attempt/{attemptId}/save-answer', [\App\Http\Controllers\Student\QuizAttemptController::class, 'saveAnswer'])->name('save-answer'); // Save answer (AJAX)
            Route::post('/attempt/{attemptId}/mark-review/{questionId}', [\App\Http\Controllers\Student\QuizAttemptController::class, 'markForReview'])->name('mark-review'); // Mark question for review
            Route::post('/attempt/{attemptId}/submit', [\App\Http\Controllers\Student\QuizAttemptController::class, 'submit'])->name('submit'); // Submit quiz
            Route::post('/attempt/{attemptId}/mark-completed', [\App\Http\Controllers\Student\QuizAttemptController::class, 'markCompleted'])->name('mark-completed'); // Mark as completed "تم الإنجاز" ✅
            Route::get('/attempt/{attemptId}/progress', [\App\Http\Controllers\Student\QuizAttemptController::class, 'getProgress'])->name('progress'); // Get progress (AJAX)
        });

        // Review & Analytics
        Route::prefix('quizzes/review')->name('student.quizzes.review.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Student\QuizReviewController::class, 'index'])->name('index'); // List all attempts
            Route::get('/{attemptId}', [\App\Http\Controllers\Student\QuizReviewController::class, 'show'])->name('show'); // Review specific attempt
            Route::get('/analytics/overview', [\App\Http\Controllers\Student\QuizReviewController::class, 'analytics'])->name('analytics'); // Performance analytics
            Route::get('/quiz/{quizId}/compare', [\App\Http\Controllers\Student\QuizReviewController::class, 'compareAttempts'])->name('compare'); // Compare attempts
            Route::get('/quiz/{quizId}/history', [\App\Http\Controllers\Student\QuizReviewController::class, 'history'])->name('history'); // Quiz history
            Route::get('/{attemptId}/question/{questionId}', [\App\Http\Controllers\Student\QuizReviewController::class, 'getQuestionReview'])->name('question'); // Question review (AJAX)
            Route::get('/{attemptId}/download-report', [\App\Http\Controllers\Student\QuizReviewController::class, 'downloadReport'])->name('download-report'); // Download report
        });

        // ========== Question Modules Routes (Student) ==========

        // Question Module Attempts
        Route::prefix('question-modules')->name('student.question-module.')->group(function () {
            Route::get('/{questionModule}/start', [QuestionModuleAttemptController::class, 'start'])->name('start'); // Start new attempt
            Route::get('/attempts/{attempt}/take', [QuestionModuleAttemptController::class, 'take'])->name('take'); // Take test interface
            Route::post('/attempts/{attempt}/save-answer', [QuestionModuleAttemptController::class, 'saveAnswer'])->name('save-answer'); // Save answer (AJAX)
            Route::post('/attempts/{attempt}/submit', [QuestionModuleAttemptController::class, 'submit'])->name('submit'); // Submit test
            Route::get('/attempts/{attempt}/result', [QuestionModuleAttemptController::class, 'result'])->name('result'); // View results
        });

        // Question Module Statistics
        Route::prefix('question-modules/stats')->name('student.question-module.stats.')->group(function () {
            Route::get('/', [QuestionModuleStatsController::class, 'index'])->name('index'); // Main statistics page
            Route::get('/dashboard', [QuestionModuleStatsController::class, 'getDashboardStats'])->name('dashboard'); // AJAX stats for dashboard
            Route::get('/{questionModule}/module', [QuestionModuleStatsController::class, 'showModuleStats'])->name('module'); // Specific module stats
        });

        // ========== Gamification Routes (Student) ==========

        Route::prefix('gamification')->name('gamification.')->group(function () {
            // Dashboard & Profile
            Route::get('/', [GamificationDashboardController::class, 'index'])->name('dashboard');
            Route::get('/profile', [GamificationDashboardController::class, 'profile'])->name('profile');
            Route::get('/statistics', [GamificationDashboardController::class, 'statistics'])->name('statistics');

            // Points
            Route::prefix('points')->name('points.')->group(function () {
                Route::get('/', [StudentPointsController::class, 'index'])->name('index');
                Route::get('/history', [StudentPointsController::class, 'history'])->name('history');
                Route::get('/how-to-earn', [StudentPointsController::class, 'howToEarn'])->name('how-to-earn');
            });

            // Levels
            Route::prefix('levels')->name('levels.')->group(function () {
                Route::get('/', [StudentLevelController::class, 'index'])->name('index');
                Route::get('/all', [StudentLevelController::class, 'all'])->name('all');
                Route::get('/{level}', [StudentLevelController::class, 'show'])->name('show');
            });

            // Streaks
            Route::prefix('streak')->name('streak.')->group(function () {
                Route::get('/', [StudentStreakController::class, 'index'])->name('index');
                Route::get('/calendar', [StudentStreakController::class, 'calendar'])->name('calendar');
                Route::get('/history', [StudentStreakController::class, 'history'])->name('history');
            });

            // Badges
            Route::prefix('badges')->name('badges.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Student\Gamification\BadgeController::class, 'index'])->name('index');
                Route::get('/collection', [\App\Http\Controllers\Student\Gamification\BadgeController::class, 'collection'])->name('collection');
                Route::get('/recommended', [\App\Http\Controllers\Student\Gamification\BadgeController::class, 'recommended'])->name('recommended');
                Route::get('/{badge}', [\App\Http\Controllers\Student\Gamification\BadgeController::class, 'show'])->name('show');
            });

            // Achievements
            Route::prefix('achievements')->name('achievements.')->group(function () {
                Route::get('/', [StudentAchievementController::class, 'index'])->name('index');
                Route::get('/recommended', [StudentAchievementController::class, 'recommended'])->name('recommended');
                Route::get('/{achievement}', [StudentAchievementController::class, 'show'])->name('show');
                Route::post('/{userAchievement}/claim', [StudentAchievementController::class, 'claim'])->name('claim');
            });

            // Leaderboards
            Route::prefix('leaderboards')->name('leaderboards.')->group(function () {
                Route::get('/', [StudentLeaderboardController::class, 'index'])->name('index');
                Route::get('/my-rank', [StudentLeaderboardController::class, 'myRank'])->name('my-rank');
                Route::get('/{leaderboard}', [StudentLeaderboardController::class, 'show'])->name('show');
                Route::get('/{leaderboard}/division/{division}', [StudentLeaderboardController::class, 'division'])->name('division');
            });

            // Challenges
            Route::prefix('challenges')->name('challenges.')->group(function () {
                Route::get('/', [StudentChallengeController::class, 'index'])->name('index');
                Route::get('/active', [StudentChallengeController::class, 'active'])->name('active');
                Route::get('/daily', [StudentChallengeController::class, 'daily'])->name('daily');
                Route::get('/weekly', [StudentChallengeController::class, 'weekly'])->name('weekly');
                Route::get('/monthly', [StudentChallengeController::class, 'monthly'])->name('monthly');
                Route::get('/special', [StudentChallengeController::class, 'special'])->name('special');
                Route::get('/recommended', [StudentChallengeController::class, 'recommended'])->name('recommended');
                Route::get('/my-stats', [StudentChallengeController::class, 'myStats'])->name('my-stats');
                Route::get('/history', [StudentChallengeController::class, 'history'])->name('history');
                Route::get('/{challenge}', [StudentChallengeController::class, 'show'])->name('show');
                Route::post('/{challenge}/accept', [StudentChallengeController::class, 'accept'])->name('accept');
                Route::post('/user-challenges/{userChallenge}/cancel', [StudentChallengeController::class, 'cancel'])->name('cancel');
                Route::get('/user-challenges/{userChallenge}/progress', [StudentChallengeController::class, 'progress'])->name('progress');
            });

            // Shop
            Route::prefix('shop')->name('shop.')->group(function () {
                Route::get('/', [StudentShopController::class, 'index'])->name('index');
                Route::get('/categories', [StudentShopController::class, 'categories'])->name('categories');
                Route::get('/categories/{shopCategory}', [StudentShopController::class, 'categoryItems'])->name('category-items');
                Route::get('/featured', [StudentShopController::class, 'featured'])->name('featured');
                Route::get('/limited-offers', [StudentShopController::class, 'timeLimitedOffers'])->name('limited-offers');
                Route::get('/search', [StudentShopController::class, 'search'])->name('search');
                Route::get('/my-stats', [StudentShopController::class, 'myStats'])->name('my-stats');
                Route::get('/purchase-history', [StudentShopController::class, 'purchaseHistory'])->name('purchase-history');
                Route::get('/items/{shopItem}', [StudentShopController::class, 'show'])->name('show');
                Route::post('/items/{shopItem}/purchase', [StudentShopController::class, 'purchase'])->name('purchase');
            });

            // Inventory
            Route::prefix('inventory')->name('inventory.')->group(function () {
                Route::get('/', [StudentInventoryController::class, 'index'])->name('index');
                Route::get('/active', [StudentInventoryController::class, 'active'])->name('active');
                Route::get('/cosmetics', [StudentInventoryController::class, 'cosmetics'])->name('cosmetics');
                Route::get('/boosters', [StudentInventoryController::class, 'boosters'])->name('boosters');
                Route::get('/consumables', [StudentInventoryController::class, 'consumables'])->name('consumables');
                Route::get('/stats', [StudentInventoryController::class, 'stats'])->name('stats');
                Route::get('/{inventory}', [StudentInventoryController::class, 'show'])->name('show');
                Route::post('/{inventory}/activate', [StudentInventoryController::class, 'activate'])->name('activate');
                Route::post('/{inventory}/deactivate', [StudentInventoryController::class, 'deactivate'])->name('deactivate');
                Route::post('/{inventory}/consume', [StudentInventoryController::class, 'consume'])->name('consume');
            });

            // Friendships
            Route::prefix('friends')->name('friends.')->group(function () {
                Route::get('/', [StudentFriendshipController::class, 'index'])->name('index');
                Route::post('/send-request', [StudentFriendshipController::class, 'sendRequest'])->name('send-request');
                Route::post('/{friendship}/accept', [StudentFriendshipController::class, 'acceptRequest'])->name('accept');
                Route::post('/{friendship}/reject', [StudentFriendshipController::class, 'rejectRequest'])->name('reject');
                Route::post('/{friendship}/cancel', [StudentFriendshipController::class, 'cancelRequest'])->name('cancel');
                Route::post('/unfriend', [StudentFriendshipController::class, 'unfriend'])->name('unfriend');
                Route::get('/pending-requests', [StudentFriendshipController::class, 'pendingRequests'])->name('pending-requests');
                Route::get('/sent-requests', [StudentFriendshipController::class, 'sentRequests'])->name('sent-requests');
                Route::get('/suggestions', [StudentFriendshipController::class, 'suggestions'])->name('suggestions');
                Route::get('/search', [StudentFriendshipController::class, 'search'])->name('search');
                Route::get('/status', [StudentFriendshipController::class, 'status'])->name('status');
            });

            // Competitions
            Route::prefix('competitions')->name('competitions.')->group(function () {
                Route::get('/active', [StudentCompetitionController::class, 'active'])->name('active');
                Route::get('/completed', [StudentCompetitionController::class, 'completed'])->name('completed');
                Route::post('/create', [StudentCompetitionController::class, 'create'])->name('create');
                Route::get('/my-stats', [StudentCompetitionController::class, 'myStats'])->name('my-stats');
                Route::get('/{competition}', [StudentCompetitionController::class, 'show'])->name('show');
                Route::post('/{competition}/leave', [StudentCompetitionController::class, 'leave'])->name('leave');
                Route::delete('/{competition}', [StudentCompetitionController::class, 'destroy'])->name('destroy');
            });

            // Social Activities
            Route::prefix('social')->name('social.')->group(function () {
                Route::get('/feed', [StudentSocialActivityController::class, 'feed'])->name('feed');
                Route::get('/my-activities', [StudentSocialActivityController::class, 'myActivities'])->name('my-activities');
                Route::get('/users/{targetUser}/activities', [StudentSocialActivityController::class, 'userActivities'])->name('user-activities');
                Route::post('/activities/{activity}/like', [StudentSocialActivityController::class, 'like'])->name('like');
                Route::post('/activities/{activity}/unlike', [StudentSocialActivityController::class, 'unlike'])->name('unlike');
                Route::post('/activities/{activity}/comment', [StudentSocialActivityController::class, 'comment'])->name('comment');
                Route::delete('/comments/{commentId}', [StudentSocialActivityController::class, 'deleteComment'])->name('delete-comment');
                Route::post('/share/achievement', [StudentSocialActivityController::class, 'shareAchievement'])->name('share-achievement');
                Route::post('/share/badge', [StudentSocialActivityController::class, 'shareBadge'])->name('share-badge');
                Route::delete('/activities/{activity}', [StudentSocialActivityController::class, 'destroy'])->name('delete-activity');
            });

            // Notifications
            Route::prefix('notifications')->name('notifications.')->group(function () {
                Route::get('/', [StudentNotificationController::class, 'index'])->name('index');
                Route::get('/api', [StudentNotificationController::class, 'api'])->name('api');
                Route::get('/api/unread-count', [StudentNotificationController::class, 'unreadCount'])->name('api.unread-count');
                Route::get('/unread-count', [StudentNotificationController::class, 'unreadCount'])->name('unread-count');
                Route::post('/{notification}/mark-as-read', [StudentNotificationController::class, 'markAsRead'])->name('mark-as-read');
                Route::post('/{notification}/mark-read', [StudentNotificationController::class, 'markAsRead'])->name('mark-read');
                Route::post('/mark-all-as-read', [StudentNotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
                Route::post('/mark-all-read', [StudentNotificationController::class, 'markAllAsRead'])->name('mark-all-read');
                Route::delete('/{notification}', [StudentNotificationController::class, 'destroy'])->name('destroy');
                Route::get('/my-report', [StudentNotificationController::class, 'myReport'])->name('my-report');
                Route::get('/preferences', [StudentNotificationController::class, 'getPreferences'])->name('get-preferences');
                Route::post('/preferences', [StudentNotificationController::class, 'updatePreferences'])->name('update-preferences');
            });
        });

        // Settings Routes
        Route::prefix('settings')->name('student.settings.')->group(function () {
            Route::get('/notifications', [NotificationPreferencesController::class, 'index'])->name('notifications');
            Route::post('/notifications', [NotificationPreferencesController::class, 'update'])->name('notifications.update');
        });

        // Notes Routes (المفكرة الشخصية)
        Route::prefix('notes')->name('student.notes.')->group(function () {
            Route::get('/', [NoteController::class, 'index'])->name('index');
            Route::post('/', [NoteController::class, 'store'])->name('store');
            Route::put('/{note}', [NoteController::class, 'update'])->name('update');
            Route::delete('/{note}', [NoteController::class, 'destroy'])->name('destroy');
            Route::post('/{note}/pin', [NoteController::class, 'togglePin'])->name('pin');
            Route::post('/{note}/favorite', [NoteController::class, 'toggleFavorite'])->name('favorite');
            Route::post('/{note}/archive', [NoteController::class, 'archive'])->name('archive');
            Route::get('/archived', [NoteController::class, 'archived'])->name('archived');
        });

        // Course Notes Routes (ملاحظات الكورسات)
        Route::prefix('course-notes')->name('student.course-notes.')->group(function () {
            Route::get('/', [CourseNoteController::class, 'index'])->name('index');
            Route::post('/', [CourseNoteController::class, 'store'])->name('store');
            Route::put('/{courseNote}', [CourseNoteController::class, 'update'])->name('update');
            Route::delete('/{courseNote}', [CourseNoteController::class, 'destroy'])->name('destroy');
            Route::get('/course/{courseId}', [CourseNoteController::class, 'byCourse'])->name('by-course');
        });

        // Reminders Routes (التذكيرات)
        Route::prefix('reminders')->name('student.reminders.')->group(function () {
            Route::get('/', [StudentReminderController::class, 'index'])->name('index');
            Route::get('/{reminder}', [StudentReminderController::class, 'show'])->name('show');
        });

        // Calendar Routes (التقويم)
        Route::get('/calendar', [CalendarController::class, 'index'])->name('student.calendar.index');
        Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('student.calendar.events');

        // Student Works Routes (جدول الأعمال)
        Route::prefix('works')->name('student.works.')->group(function () {
            Route::get('/', [StudentWorkController::class, 'index'])->name('index');
            Route::get('/create', [StudentWorkController::class, 'create'])->name('create');
            Route::post('/', [StudentWorkController::class, 'store'])->name('store');
            Route::get('/portfolio', [StudentWorkController::class, 'portfolio'])->name('portfolio');
            Route::get('/{work}', [StudentWorkController::class, 'show'])->name('show');
            Route::get('/{work}/edit', [StudentWorkController::class, 'edit'])->name('edit');
            Route::put('/{work}', [StudentWorkController::class, 'update'])->name('update');
            Route::delete('/{work}', [StudentWorkController::class, 'destroy'])->name('destroy');
            Route::post('/{work}/submit', [StudentWorkController::class, 'submit'])->name('submit');
        });

        // Course Reviews Routes (مراجعات الكورسات)
        Route::prefix('courses/{course}/reviews')->name('student.courses.reviews.')->group(function () {
            Route::post('/', [CourseReviewController::class, 'store'])->name('store');
            Route::put('/{review}', [CourseReviewController::class, 'update'])->name('update');
            Route::delete('/{review}', [CourseReviewController::class, 'destroy'])->name('destroy');
        });
        Route::post('/reviews/{review}/helpful', [CourseReviewController::class, 'markHelpful'])->name('student.reviews.helpful');

    });
