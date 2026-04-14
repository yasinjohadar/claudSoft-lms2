<?php

use App\Http\Controllers\Admin\AIBlogPostController;
use App\Http\Controllers\Admin\AIContentController;
use App\Http\Controllers\Admin\AIDocumentationPageController;
use App\Http\Controllers\Admin\AIFrontendCourseController;
use App\Http\Controllers\Admin\AIGradingSettingsController;
use App\Http\Controllers\Admin\AIModelController;
use App\Http\Controllers\Admin\AIQuestionGenerationController;
use App\Http\Controllers\Admin\AIQuestionSolvingController;
use App\Http\Controllers\Admin\AISettingsController;
use App\Http\Controllers\Admin\AIStudentFeedbackController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\BlogTagController;
use App\Http\Controllers\Admin\BulkUserImportController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\ContactSettingController;
use App\Http\Controllers\Admin\CourseCategoryController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\CourseEnrollmentController;
use App\Http\Controllers\Admin\CourseGroupController;
use App\Http\Controllers\Admin\CourseModuleCompletionSummaryController;
use App\Http\Controllers\Admin\CourseModuleController;
use App\Http\Controllers\Admin\CourseReviewController as AdminCourseReviewController;
use App\Http\Controllers\Admin\CourseSectionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentationCategoryController;
use App\Http\Controllers\Admin\DocumentationPageController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FrontendCourseController;
use App\Http\Controllers\Admin\Gamification\AchievementController as AdminAchievementController;
use App\Http\Controllers\Admin\Gamification\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\Gamification\BadgeController as AdminBadgeController;
use App\Http\Controllers\Admin\Gamification\ChallengeController as AdminChallengeController;
use App\Http\Controllers\Admin\Gamification\CompetitionController as AdminCompetitionController;
use App\Http\Controllers\Admin\Gamification\DashboardController as GamificationDashboardController;
use App\Http\Controllers\Admin\Gamification\LeaderboardController as AdminLeaderboardController;
use App\Http\Controllers\Admin\Gamification\LevelController as AdminLevelController;
use App\Http\Controllers\Admin\Gamification\PointsController as AdminPointsController;
use App\Http\Controllers\Admin\Gamification\PurchaseController as AdminPurchaseController;
use App\Http\Controllers\Admin\Gamification\ShopCategoryController as AdminShopCategoryController;
use App\Http\Controllers\Admin\Gamification\ShopItemController as AdminShopItemController;
use App\Http\Controllers\Admin\Gamification\SocialActivityController as AdminSocialActivityController;
use App\Http\Controllers\Admin\GoogleSettingController;
use App\Http\Controllers\Admin\GroupRegistrationController;
use App\Http\Controllers\Admin\GroupRegistrationSettingController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\LaravelAiModelController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\ModuleCompletionReportController;
use App\Http\Controllers\Admin\N8nWebhookController;
use App\Http\Controllers\Admin\NotificationHubAdminController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\ReminderController;
use App\Http\Controllers\Admin\ResourceController as AdminResourceController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\StudentCourseAiReportController;
use App\Http\Controllers\Admin\StudentWorkController;
use App\Http\Controllers\Admin\TrainingCampController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserDeviceController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\WebhookManagementController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware('auth')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

        // Test route
        Route::get('/test-questions', function () {
            return view('test-route');
        });

        // Admin routes

        // Bulk User Import Routes (MUST be before Route::resource('users'))
        Route::prefix('users/bulk-import')->name('users.bulk-import.')->group(function () {
            Route::get('/', [BulkUserImportController::class, 'index'])->name('index');
            Route::get('/reports', [BulkUserImportController::class, 'reports'])->name('reports');
            Route::post('/upload', [BulkUserImportController::class, 'upload'])->name('upload');
            Route::get('/preview', [BulkUserImportController::class, 'preview'])->name('preview');
            Route::post('/process', [BulkUserImportController::class, 'process'])->name('process');
            Route::get('/report/{session}', [BulkUserImportController::class, 'report'])->name('report');
            Route::get('/download-template', [BulkUserImportController::class, 'downloadTemplate'])->name('template');
            Route::get('/download-errors/{session}', [BulkUserImportController::class, 'downloadErrors'])->name('errors');
        });

        Route::get('users/{user}/admin-notes', [UserController::class, 'adminNotesFragment'])->name('admin.users.admin-notes');
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::put('users/{user}/change-password', [UserController::class, 'updatePassword'])->name('users.update-password');
        Route::post('users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
        Route::get('users/{userId}/courses', [UserController::class, 'showCourses'])->name('admin.users.courses');
        Route::get('users/{user}/student-details', [UserController::class, 'studentDetails'])->name('users.student-details');
        Route::post('users/{user}/add-to-group', [UserController::class, 'addToGroup'])->name('users.add-to-group');
        Route::post('users/{user}/remove-from-group', [UserController::class, 'removeFromGroup'])->name('users.remove-from-group');
        Route::post('users/{user}/enroll-course', [UserController::class, 'enrollCourse'])->name('users.enroll-course');
        Route::post('users/{user}/unenroll-course', [UserController::class, 'unenrollCourse'])->name('users.unenroll-course');

        // Impersonation routes
        Route::post('impersonate/{user}', [ImpersonationController::class, 'impersonate'])->name('admin.impersonate');
        Route::post('stop-impersonate', [ImpersonationController::class, 'stop'])->name('admin.stop-impersonate');

        // Course Categories routes
        Route::resource('course-categories', CourseCategoryController::class);
        Route::post('course-categories/{id}/restore', [CourseCategoryController::class, 'restore'])->name('course-categories.restore');
        Route::delete('course-categories/{id}/force-delete', [CourseCategoryController::class, 'forceDelete'])->name('course-categories.force-delete');

        // Training Camps routes
        Route::get('training-camps/{camp}/modal-data', [TrainingCampController::class, 'modalData'])->name('training-camps.modal-data');
        Route::resource('training-camps', TrainingCampController::class);
        Route::get('training-camps-enrollments', [TrainingCampController::class, 'enrollments'])->name('training-camps.enrollments');
        Route::post('training-camps-enrollments/{id}/approve', [TrainingCampController::class, 'approveEnrollmentOld'])->name('training-camps.enrollments.old.approve');
        Route::post('training-camps-enrollments/{id}/reject', [TrainingCampController::class, 'rejectEnrollmentOld'])->name('training-camps.enrollments.old.reject');
        Route::post('training-camps-enrollments/{id}/update-status', [TrainingCampController::class, 'updateEnrollmentStatusOld'])->name('training-camps.enrollments.old.update-status');

        // Camp enrollments management routes (for camp show page)
        Route::prefix('training-camps/{camp}/enrollments')->name('training-camps.enrollments.')->group(function () {
            Route::get('/', [TrainingCampController::class, 'campEnrollments'])->name('index');
            Route::get('/create-individual', [TrainingCampController::class, 'createIndividualEnrollment'])->name('create-individual');
            Route::get('/create-bulk', [TrainingCampController::class, 'createBulkEnrollment'])->name('create-bulk');
            Route::post('/', [TrainingCampController::class, 'storeEnrollment'])->name('store');
            Route::post('/bulk', [TrainingCampController::class, 'bulkStoreEnrollments'])->name('bulk-store');
            Route::get('/search-students', [TrainingCampController::class, 'searchStudents'])->name('search-students');
            Route::get('/groups/{group}/students', [TrainingCampController::class, 'getGroupStudents'])->name('group-students');
            Route::get('/{enrollment}', [TrainingCampController::class, 'showEnrollment'])->name('show');
            Route::delete('/{enrollment}', [TrainingCampController::class, 'destroyEnrollment'])->name('destroy');
            Route::post('/{enrollment}/approve', [TrainingCampController::class, 'approveEnrollment'])->name('approve');
            Route::post('/{enrollment}/reject', [TrainingCampController::class, 'rejectEnrollment'])->name('reject');
            Route::post('/{enrollment}/update-status', [TrainingCampController::class, 'updateEnrollmentStatus'])->name('update-status');
        });

        // Invoices routes
        Route::resource('invoices', InvoiceController::class)->except(['edit', 'update']);
        Route::get('invoices-due-overdue', [InvoiceController::class, 'dueOverdue'])->name('invoices.due-overdue');
        Route::post('invoices/{id}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
        Route::post('invoices/{id}/mark-as-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.mark-as-paid');
        Route::post('invoices/{id}/force-delete', [InvoiceController::class, 'forceDelete'])->name('invoices.force-delete');
        Route::post('invoices/{id}/send-whatsapp', [InvoiceController::class, 'sendPdfViaWhatsApp'])->name('invoices.send-whatsapp');

        // Payments routes
        Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('payments/{id}/cancel', [PaymentController::class, 'cancel'])->name('payments.cancel');
        Route::post('payments/{id}/refund', [PaymentController::class, 'refund'])->name('payments.refund');

        // Payment Methods routes
        Route::resource('payment-methods', PaymentMethodController::class);

        // ========== Course Management Routes ==========

        // Courses routes
        Route::resource('courses', CourseController::class);
        Route::get('courses/{course}/lessons', [CourseController::class, 'getLessons'])->name('admin.courses.lessons');
        Route::post('courses/{id}/duplicate', [CourseController::class, 'duplicate'])->name('courses.duplicate');
        Route::post('courses/{id}/toggle-publish', [CourseController::class, 'togglePublish'])->name('courses.toggle-publish');
        Route::post('courses/{id}/toggle-visibility', [CourseController::class, 'toggleVisibility'])->name('courses.toggle-visibility');
        Route::get('courses/{id}/modules', [CourseController::class, 'getModules'])->name('courses.modules');
        Route::get('courses/{course}/restrictions/groups', [\App\Http\Controllers\Admin\AccessRestrictionController::class, 'getCourseRestrictionGroups'])->name('courses.restrictions.groups');
        Route::get('courses/{course}/restrictions/bulk-state', [\App\Http\Controllers\Admin\AccessRestrictionController::class, 'getCourseRestrictionBulkState'])->name('courses.restrictions.bulk-state');
        Route::post('courses/{course}/modules/restrictions/sync-bulk', [\App\Http\Controllers\Admin\AccessRestrictionController::class, 'syncBulkModuleRestrictions'])->name('courses.modules.restrictions.sync-bulk');
        Route::get('courses/{course}/modules/{module}/completions', [ModuleCompletionReportController::class, 'index'])->name('courses.modules.completions');
        Route::get('courses/{course}/completion-summary', [CourseModuleCompletionSummaryController::class, 'index'])->name('courses.completion-summary');

        // Course Sections routes
        Route::resource('courses.sections', CourseSectionController::class)->except(['index']);
        Route::post('sections/{id}/toggle-visibility', [CourseSectionController::class, 'toggleVisibility'])->name('sections.toggle-visibility');
        Route::post('sections/{id}/toggle-lock', [CourseSectionController::class, 'toggleLock'])->name('sections.toggle-lock');
        Route::post('sections/reorder', [CourseSectionController::class, 'reorder'])->name('sections.reorder');

        // Section Access Restrictions routes
        Route::get('sections/{section}/restrictions', [\App\Http\Controllers\Admin\AccessRestrictionController::class, 'getSectionRestrictions'])->name('sections.restrictions.get');
        Route::post('sections/{section}/restrictions/sync', [\App\Http\Controllers\Admin\AccessRestrictionController::class, 'syncSectionRestrictions'])->name('sections.restrictions.sync');

        // Module Access Restrictions routes
        Route::get('modules/{module}/restrictions', [\App\Http\Controllers\Admin\AccessRestrictionController::class, 'getModuleRestrictions'])->name('modules.restrictions.get');
        Route::post('modules/{module}/restrictions/sync', [\App\Http\Controllers\Admin\AccessRestrictionController::class, 'syncModuleRestrictions'])->name('modules.restrictions.sync');

        // Section Questions Management routes
        Route::get('sections/{sectionId}/questions', [CourseSectionController::class, 'manageQuestions'])->name('sections.questions.manage');
        Route::post('sections/{sectionId}/questions/import', [CourseSectionController::class, 'importQuestion'])->name('sections.questions.import');
        Route::delete('sections/{sectionId}/questions/{questionId}', [CourseSectionController::class, 'removeQuestion'])->name('sections.questions.remove');
        Route::post('sections/{sectionId}/questions/reorder', [CourseSectionController::class, 'reorderQuestions'])->name('sections.questions.reorder');
        Route::put('sections/{sectionId}/questions/{questionId}/settings', [CourseSectionController::class, 'updateQuestionSettings'])->name('sections.questions.update-settings');
        Route::get('sections/{sectionId}/questions/create/{type}', [CourseSectionController::class, 'createQuestion'])->name('sections.questions.create');

        // Course Modules routes
        Route::get('course-modules', [CourseModuleController::class, 'index'])->name('course-modules.index');
        Route::resource('sections.modules', CourseModuleController::class)->except(['index']);
        Route::post('modules/{id}/duplicate', [CourseModuleController::class, 'duplicate'])->name('modules.duplicate');
        Route::post('modules/{id}/toggle-visibility', [CourseModuleController::class, 'toggleVisibility'])->name('modules.toggle-visibility');
        Route::post('modules/reorder', [CourseModuleController::class, 'reorder'])->name('modules.reorder');
        Route::get('courses/{courseId}/sections-ajax', [CourseModuleController::class, 'getSectionsByCourse'])->name('modules.sections-ajax');

        // Lessons routes
        Route::resource('lessons', LessonController::class);
        Route::post('lessons/{moduleId}/reorder', [LessonController::class, 'reorder'])->name('lessons.reorder');
        Route::post('lessons/{id}/duplicate', [LessonController::class, 'duplicate'])->name('lessons.duplicate');
        Route::post('lessons/{id}/toggle-publish', [LessonController::class, 'togglePublish'])->name('lessons.toggle-publish');
        Route::post('lessons/{id}/toggle-visibility', [LessonController::class, 'toggleVisibility'])->name('lessons.toggle-visibility');
        Route::get('lessons/{id}/attachments/{attachmentId}/download', [LessonController::class, 'downloadAttachment'])->name('lessons.attachments.download');

        // Videos routes
        Route::resource('videos', VideoController::class);
        Route::get('videos/{id}/usage-info', [VideoController::class, 'getUsageInfo'])->name('videos.usage-info');
        Route::post('videos/{id}/duplicate', [VideoController::class, 'duplicate'])->name('videos.duplicate');
        Route::post('videos/{id}/toggle-publish', [VideoController::class, 'togglePublish'])->name('videos.toggle-publish');
        Route::post('videos/{id}/toggle-visibility', [VideoController::class, 'toggleVisibility'])->name('videos.toggle-visibility');
        Route::post('videos/{id}/update-processing-status', [VideoController::class, 'updateProcessingStatus'])->name('videos.update-processing-status');

        // Resources routes
        Route::resource('resources', AdminResourceController::class);
        Route::post('resources/{id}/duplicate', [AdminResourceController::class, 'duplicate'])->name('resources.duplicate');
        Route::post('resources/{id}/toggle-publish', [AdminResourceController::class, 'togglePublish'])->name('resources.toggle-publish');
        Route::post('resources/{id}/toggle-visibility', [AdminResourceController::class, 'toggleVisibility'])->name('resources.toggle-visibility');
        Route::get('resources/{id}/download', [AdminResourceController::class, 'download'])->name('resources.download');
        Route::get('resources/{id}/preview', [AdminResourceController::class, 'preview'])->name('resources.preview');
        Route::post('resources/store-ajax', [AdminResourceController::class, 'storeAjax'])->name('resources.store-ajax');

        // Course Enrollments routes
        Route::get('courses/{courseId}/enrollments', [CourseEnrollmentController::class, 'index'])->name('courses.enrollments.index');
        Route::get('courses/{courseId}/enrollments/create', [CourseEnrollmentController::class, 'create'])->name('courses.enrollments.create');
        Route::post('courses/{courseId}/enrollments/enroll-individual', [CourseEnrollmentController::class, 'enrollIndividual'])->name('courses.enrollments.enroll-individual');

        // Bulk Enrollment (Excel/CSV)
        Route::get('courses/{courseId}/enrollments/bulk', [CourseEnrollmentController::class, 'showBulkEnroll'])->name('courses.enrollments.bulk');
        Route::post('courses/{courseId}/enrollments/bulk', [CourseEnrollmentController::class, 'processBulkEnroll'])->name('courses.enrollments.bulk.process');
        Route::get('enrollments/download-template', [CourseEnrollmentController::class, 'downloadTemplate'])->name('courses.enrollments.download-template');

        // Select Multiple Enrollment
        Route::get('courses/{courseId}/enrollments/select-multiple', [CourseEnrollmentController::class, 'showSelectEnroll'])->name('courses.enrollments.select-multiple');
        Route::post('courses/{courseId}/enrollments/select-multiple', [CourseEnrollmentController::class, 'processSelectEnroll'])->name('courses.enrollments.select-multiple.process');

        // Group Enrollment
        Route::get('courses/{courseId}/enrollments/group', [CourseEnrollmentController::class, 'showGroupEnroll'])->name('courses.enrollments.group');
        Route::post('courses/{courseId}/enrollments/group', [CourseEnrollmentController::class, 'processGroupEnroll'])->name('courses.enrollments.group.process');

        // Unenroll & Progress Report
        Route::delete('enrollments/{enrollmentId}', [CourseEnrollmentController::class, 'unenroll'])->name('courses.enrollments.unenroll');
        Route::get('enrollments/{enrollmentId}/progress', [CourseEnrollmentController::class, 'showProgress'])->name('courses.enrollments.progress');
        Route::get('courses/{courseId}/enrollments/progress-report', [CourseEnrollmentController::class, 'progressReport'])->name('courses.enrollments.progress-report');

        // Course Groups routes
        Route::get('groups/select-course', [CourseGroupController::class, 'selectCourse'])->name('groups.select-course');
        Route::get('groups/create-with-course', [CourseGroupController::class, 'createWithCourse'])->name('groups.create-with-course');
        Route::resource('courses.groups', CourseGroupController::class);
        // Route for showing group without requiring courseId
        Route::get('groups/{id}/show', [CourseGroupController::class, 'showGroup'])->name('groups.show');
        Route::get('groups/{id}/edit', [CourseGroupController::class, 'editGroup'])->name('groups.edit');
        Route::post('groups/{groupId}/add-member', [CourseGroupController::class, 'addMember'])->name('groups.add-member');
        Route::get('groups/{groupId}/bulk-enroll', [CourseGroupController::class, 'showBulkEnrollPage'])->name('groups.bulk-enroll-page');
        Route::post('groups/{groupId}/add-bulk-members', [CourseGroupController::class, 'addBulkMembers'])->name('groups.add-bulk-members');
        Route::post('groups/{group}/members/{user}/payments', [CourseGroupController::class, 'recordMemberPayment'])->name('groups.members.payments.store');
        Route::post('groups/{group}/members/{user}/training-camp-enrollment', [CourseGroupController::class, 'storeMemberTrainingCampEnrollment'])->name('groups.members.training-camp-enrollment');
        Route::delete('groups/{groupId}/remove-member/{memberId}', [CourseGroupController::class, 'removeMember'])->name('groups.remove-member');
        Route::delete('groups/{groupId}/bulk-remove-members', [CourseGroupController::class, 'bulkRemoveMembers'])->name('groups.bulk-remove-members');
        Route::post('groups/{groupId}/update-member-role/{memberId}', [CourseGroupController::class, 'updateMemberRole'])->name('groups.update-member-role');
        Route::post('groups/{groupId}/toggle-visibility', [CourseGroupController::class, 'toggleVisibility'])->name('groups.toggle-visibility');
        Route::post('groups/{groupId}/toggle-active', [CourseGroupController::class, 'toggleActive'])->name('groups.toggle-active');

        // Group Membership Requests routes
        Route::get('courses/{courseId}/groups/{groupId}/membership-requests', [CourseGroupController::class, 'membershipRequests'])->name('courses.groups.membership-requests');
        Route::post('courses/{courseId}/groups/{groupId}/membership-requests/{requestId}/approve', [CourseGroupController::class, 'approveRequest'])->name('courses.groups.membership-requests.approve');
        Route::post('courses/{courseId}/groups/{groupId}/membership-requests/{requestId}/reject', [CourseGroupController::class, 'rejectRequest'])->name('courses.groups.membership-requests.reject');
        Route::delete('courses/{courseId}/groups/{groupId}/membership-requests/{requestId}/delete', [CourseGroupController::class, 'deleteRequest'])->name('courses.groups.membership-requests.delete');
        Route::delete('courses/{courseId}/groups/{groupId}/membership-requests/delete-multiple', [CourseGroupController::class, 'deleteMultipleRequests'])->name('courses.groups.membership-requests.delete-multiple');
        Route::post('courses/{courseId}/groups/{groupId}/membership-requests/approve-multiple', [CourseGroupController::class, 'approveMultipleRequests'])->name('courses.groups.membership-requests.approve-multiple');
        Route::post('courses/{courseId}/groups/{groupId}/membership-requests/approve-all', [CourseGroupController::class, 'approveAllPendingRequests'])->name('courses.groups.membership-requests.approve-all');

        // General management routes (all courses)
        Route::get('all-enrollments', [CourseEnrollmentController::class, 'allEnrollments'])->name('enrollments.all');
        Route::post('enrollments/{enrollmentId}/approve', [CourseEnrollmentController::class, 'approve'])->name('enrollments.approve');
        Route::post('enrollments/{enrollmentId}/reject', [CourseEnrollmentController::class, 'reject'])->name('enrollments.reject');

        // Group Registration Routes
        Route::prefix('group-registrations')->name('admin.group-registrations.')->group(function () {
            Route::get('/', [GroupRegistrationController::class, 'index'])->name('index');
            Route::get('/whatsapp-report', [GroupRegistrationController::class, 'whatsappReport'])->name('whatsapp-report');
            Route::get('/{registration}', [GroupRegistrationController::class, 'show'])->name('show');
            Route::post('/{registration}/reprocess', [GroupRegistrationController::class, 'reprocess'])->name('reprocess');
            Route::post('/{registration}/resend-email', [GroupRegistrationController::class, 'resendEmail'])->name('resend-email');
            Route::post('/{registration}/resend-whatsapp', [GroupRegistrationController::class, 'resendWhatsApp'])->name('resend-whatsapp');
            Route::delete('/{registration}', [GroupRegistrationController::class, 'destroy'])->name('destroy');
        });

        // Group Registration Settings Routes
        Route::prefix('groups/{group}/registration-settings')->name('admin.group-registration-settings.')->group(function () {
            Route::get('/', [GroupRegistrationSettingController::class, 'index'])->name('index');
            Route::put('/', [GroupRegistrationSettingController::class, 'update'])->name('update');
        });
        Route::get('all-groups', [CourseGroupController::class, 'allGroups'])->name('groups.all');
        Route::delete('groups/{id}/delete', [CourseGroupController::class, 'deleteGroup'])->name('groups.delete');
        Route::get('all-lessons', [LessonController::class, 'allLessons'])->name('lessons.all');

        // ========== Assignments Routes ==========
        Route::resource('assignments', \App\Http\Controllers\Admin\AssignmentController::class);
        Route::post('assignments/{id}/toggle-publish', [\App\Http\Controllers\Admin\AssignmentController::class, 'togglePublish'])->name('assignments.toggle-publish');
        Route::get('assignments/course/{courseId}/lessons', [\App\Http\Controllers\Admin\AssignmentController::class, 'getLessons'])->name('assignments.get-lessons');
        Route::post('assignments/{id}/delete-attachment', [\App\Http\Controllers\Admin\AssignmentController::class, 'deleteAttachment'])->name('assignments.delete-attachment');
        Route::post('submissions/{submissionId}/grade', [\App\Http\Controllers\Admin\AssignmentController::class, 'gradeSubmission'])->name('submissions.grade');
        Route::post('submissions/{submissionId}/grant-resubmission', [\App\Http\Controllers\Admin\AssignmentController::class, 'grantResubmission'])->name('submissions.grant-resubmission');

        // ========== Quizzes Routes ==========

        // Quizzes Management
        Route::resource('quizzes', \App\Http\Controllers\Admin\QuizController::class);
        Route::post('quizzes/{id}/toggle-publish', [\App\Http\Controllers\Admin\QuizController::class, 'togglePublish'])->name('quizzes.toggle-publish');
        Route::get('quizzes/course/{courseId}/lessons', [\App\Http\Controllers\Admin\QuizController::class, 'getLessons'])->name('quizzes.get-lessons');
        Route::post('quizzes/{id}/recalculate-score', [\App\Http\Controllers\Admin\QuizController::class, 'recalculateScore'])->name('quizzes.recalculate-score');

        // Quiz Questions Management
        Route::get('quizzes/{id}/manage-questions', [\App\Http\Controllers\Admin\QuizController::class, 'manageQuestions'])->name('quizzes.manage-questions');
        Route::get('quizzes/{id}/import-questions', [\App\Http\Controllers\Admin\QuizController::class, 'importQuestions'])->name('quizzes.import-questions');
        Route::post('quizzes/{id}/add-question', [\App\Http\Controllers\Admin\QuizController::class, 'addQuestion'])->name('quizzes.add-question');
        Route::delete('quizzes/{id}/remove-question/{questionId}', [\App\Http\Controllers\Admin\QuizController::class, 'removeQuestion'])->name('quizzes.remove-question');
        Route::post('quizzes/{id}/remove-multiple-questions', [\App\Http\Controllers\Admin\QuizController::class, 'removeMultipleQuestions'])->name('quizzes.remove-multiple-questions');
        Route::post('quizzes/{id}/reorder-questions', [\App\Http\Controllers\Admin\QuizController::class, 'reorderQuestions'])->name('quizzes.reorder-questions');

        // Question Bank Management
        Route::get('question-bank/create/{type}', [\App\Http\Controllers\Admin\QuestionBankController::class, 'createByType'])->name('question-bank.create.type');
        Route::resource('question-bank', \App\Http\Controllers\Admin\QuestionBankController::class);
        Route::post('question-bank/{id}/duplicate', [\App\Http\Controllers\Admin\QuestionBankController::class, 'duplicate'])->name('question-bank.duplicate');
        Route::get('question-bank/{id}/preview', [\App\Http\Controllers\Admin\QuestionBankController::class, 'preview'])->name('question-bank.preview');
        Route::get('question-bank/course/{courseId}/questions', [\App\Http\Controllers\Admin\QuestionBankController::class, 'getQuestionsByCourse'])->name('question-bank.by-course');
        Route::get('question-bank/type/{typeId}/questions', [\App\Http\Controllers\Admin\QuestionBankController::class, 'getQuestionsByType'])->name('question-bank.by-type');
        Route::post('question-bank/bulk-action', [\App\Http\Controllers\Admin\QuestionBankController::class, 'bulkAction'])->name('question-bank.bulk-action');
        Route::post('question-bank/delete-multiple', [\App\Http\Controllers\Admin\QuestionBankController::class, 'destroyMultiple'])->name('question-bank.delete-multiple');

        // Excel Import/Export
        Route::get('question-bank/import/excel', [\App\Http\Controllers\Admin\QuestionBankController::class, 'showImportForm'])->name('question-bank.import.excel');
        Route::post('question-bank/import/preview', [\App\Http\Controllers\Admin\QuestionBankController::class, 'previewImport'])->name('question-bank.import.preview');
        Route::post('question-bank/import/process', [\App\Http\Controllers\Admin\QuestionBankController::class, 'processImport'])->name('question-bank.import.process');
        Route::get('question-bank/export/template', [\App\Http\Controllers\Admin\QuestionBankController::class, 'downloadTemplate'])->name('question-bank.export.template');

        // Question Pools Management
        Route::resource('question-pools', \App\Http\Controllers\Admin\QuestionPoolController::class);
        Route::post('question-pools/{id}/duplicate', [\App\Http\Controllers\Admin\QuestionPoolController::class, 'duplicate'])->name('question-pools.duplicate');
        Route::post('question-pools/{id}/add-question', [\App\Http\Controllers\Admin\QuestionPoolController::class, 'addQuestion'])->name('question-pools.add-question');
        Route::delete('question-pools/{id}/remove-question/{itemId}', [\App\Http\Controllers\Admin\QuestionPoolController::class, 'removeQuestion'])->name('question-pools.remove-question');
        Route::post('question-pools/{id}/update-order', [\App\Http\Controllers\Admin\QuestionPoolController::class, 'updateOrder'])->name('question-pools.update-order');
        Route::post('question-pools/{id}/generate-questions', [\App\Http\Controllers\Admin\QuestionPoolController::class, 'generateQuestions'])->name('question-pools.generate-questions');
        Route::get('question-pools/{id}/statistics', [\App\Http\Controllers\Admin\QuestionPoolController::class, 'getStatistics'])->name('question-pools.statistics');

        // Quiz Grading
        Route::prefix('grading')->name('grading.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\QuizGradingController::class, 'index'])->name('index');
            Route::get('/{attemptId}/report', [\App\Http\Controllers\Admin\QuizGradingController::class, 'attemptReport'])->name('attempt-report');
            Route::get('/{attemptId}', [\App\Http\Controllers\Admin\QuizGradingController::class, 'show'])->name('show');
            Route::post('/responses/{responseId}/grade', [\App\Http\Controllers\Admin\QuizGradingController::class, 'gradeResponse'])->name('grade-response');
            Route::post('/bulk-grade', [\App\Http\Controllers\Admin\QuizGradingController::class, 'gradeBulk'])->name('bulk-grade');
            Route::post('/{attemptId}/complete', [\App\Http\Controllers\Admin\QuizGradingController::class, 'completeGrading'])->name('complete');
            Route::post('/{attemptId}/regrade', [\App\Http\Controllers\Admin\QuizGradingController::class, 'regradeAttempt'])->name('regrade');
            Route::get('/quiz/{quizId}/stats', [\App\Http\Controllers\Admin\QuizGradingController::class, 'getQuizStats'])->name('quiz-stats');
            Route::post('/export-report', [\App\Http\Controllers\Admin\QuizGradingController::class, 'exportReport'])->name('export-report');
        });

        // Quiz Analytics
        Route::prefix('quiz-analytics')->name('quiz-analytics.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\QuizAnalyticsController::class, 'index'])->name('index');
            Route::get('/quiz/{quizId}', [\App\Http\Controllers\Admin\QuizAnalyticsController::class, 'quiz'])->name('quiz');
            Route::get('/student/{studentId}', [\App\Http\Controllers\Admin\QuizAnalyticsController::class, 'student'])->name('student');
            Route::get('/course/{courseId}', [\App\Http\Controllers\Admin\QuizAnalyticsController::class, 'course'])->name('course');
            Route::post('/compare', [\App\Http\Controllers\Admin\QuizAnalyticsController::class, 'compare'])->name('compare');
            Route::post('/export', [\App\Http\Controllers\Admin\QuizAnalyticsController::class, 'export'])->name('export');
        });

        // ========== Question Modules Routes ==========

        // Question Modules Management
        Route::resource('question-modules', \App\Http\Controllers\Admin\QuestionModuleController::class);
        Route::get('question-modules/{id}/manage-questions', [\App\Http\Controllers\Admin\QuestionModuleController::class, 'manageQuestions'])->name('question-modules.manage-questions');
        Route::get('question-modules/{id}/import-questions', [\App\Http\Controllers\Admin\QuestionModuleController::class, 'importQuestions'])->name('question-modules.import-questions');
        Route::post('question-modules/{id}/add-question', [\App\Http\Controllers\Admin\QuestionModuleController::class, 'addQuestion'])->name('question-modules.add-question');
        Route::delete('question-modules/{id}/remove-question/{questionId}', [\App\Http\Controllers\Admin\QuestionModuleController::class, 'removeQuestion'])->name('question-modules.remove-question');
        Route::put('question-modules/{id}/update-question-settings/{questionId}', [\App\Http\Controllers\Admin\QuestionModuleController::class, 'updateQuestionSettings'])->name('question-modules.update-question-settings');
        Route::post('question-modules/{id}/reorder-questions', [\App\Http\Controllers\Admin\QuestionModuleController::class, 'reorderQuestions'])->name('question-modules.reorder-questions');
        Route::post('question-modules/{id}/toggle-publish', [\App\Http\Controllers\Admin\QuestionModuleController::class, 'togglePublish'])->name('question-modules.toggle-publish');
        Route::post('question-modules/{id}/toggle-visibility', [\App\Http\Controllers\Admin\QuestionModuleController::class, 'toggleVisibility'])->name('question-modules.toggle-visibility');

        // Question Module Grading
        Route::prefix('question-module-grading')->name('admin.question-module-grading.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\QuestionModuleGradingController::class, 'index'])->name('index');
            Route::get('/{attemptId}', [\App\Http\Controllers\Admin\QuestionModuleGradingController::class, 'show'])->name('show');
            Route::post('/responses/{responseId}/grade', [\App\Http\Controllers\Admin\QuestionModuleGradingController::class, 'gradeResponse'])->name('grade-response');
            Route::post('/bulk-grade', [\App\Http\Controllers\Admin\QuestionModuleGradingController::class, 'gradeBulk'])->name('bulk-grade');
            Route::post('/{attemptId}/complete', [\App\Http\Controllers\Admin\QuestionModuleGradingController::class, 'completeGrading'])->name('complete');
        });

        // ========== Gamification Routes ==========

        Route::prefix('gamification')->name('admin.gamification.')->group(function () {
            // Dashboard
            Route::get('/', [GamificationDashboardController::class, 'index'])->name('dashboard');
            Route::get('/analytics', [GamificationDashboardController::class, 'analytics'])->name('analytics');

            // Points Management
            Route::prefix('points')->name('points.')->group(function () {
                Route::get('/', [AdminPointsController::class, 'index'])->name('index');
                Route::get('/create', [AdminPointsController::class, 'create'])->name('create');
                Route::post('/', [AdminPointsController::class, 'store'])->name('store');
                Route::get('/user/{user}', [AdminPointsController::class, 'userTransactions'])->name('user-transactions');
                Route::get('/report', [AdminPointsController::class, 'report'])->name('report');
                Route::post('/recalculate/{user}', [AdminPointsController::class, 'recalculate'])->name('recalculate');
                Route::delete('/{transaction}', [AdminPointsController::class, 'destroy'])->name('destroy');
            });

            // Levels Management
            Route::prefix('levels')->name('levels.')->group(function () {
                Route::get('/', [AdminLevelController::class, 'index'])->name('index');
                Route::get('/create', [AdminLevelController::class, 'create'])->name('create');
                Route::post('/', [AdminLevelController::class, 'store'])->name('store');
                Route::get('/{level}/edit', [AdminLevelController::class, 'edit'])->name('edit');
                Route::put('/{level}', [AdminLevelController::class, 'update'])->name('update');
                Route::delete('/{level}', [AdminLevelController::class, 'destroy'])->name('destroy');
                Route::get('/statistics', [AdminLevelController::class, 'statistics'])->name('statistics');
                Route::post('/generate', [AdminLevelController::class, 'generate'])->name('generate');
            });

            // Badges Management
            Route::prefix('badges')->name('badges.')->group(function () {
                Route::get('/', [AdminBadgeController::class, 'index'])->name('index');
                Route::get('/create', [AdminBadgeController::class, 'create'])->name('create');
                Route::post('/', [AdminBadgeController::class, 'store'])->name('store');
                Route::get('/{badge}', [AdminBadgeController::class, 'show'])->name('show');
                Route::get('/{badge}/edit', [AdminBadgeController::class, 'edit'])->name('edit');
                Route::put('/{badge}', [AdminBadgeController::class, 'update'])->name('update');
                Route::delete('/{badge}', [AdminBadgeController::class, 'destroy'])->name('destroy');
                Route::post('/award', [AdminBadgeController::class, 'awardToUser'])->name('award');
                Route::post('/{badge}/toggle-active', [AdminBadgeController::class, 'toggleActive'])->name('toggle-active');
                Route::get('/statistics/overview', [AdminBadgeController::class, 'statistics'])->name('statistics');
            });

            // Achievements Management
            Route::prefix('achievements')->name('achievements.')->group(function () {
                Route::get('/', [AdminAchievementController::class, 'index'])->name('index');
                Route::get('/create', [AdminAchievementController::class, 'create'])->name('create');
                Route::post('/', [AdminAchievementController::class, 'store'])->name('store');
                Route::get('/{achievement}', [AdminAchievementController::class, 'show'])->name('show');
                Route::get('/{achievement}/edit', [AdminAchievementController::class, 'edit'])->name('edit');
                Route::put('/{achievement}', [AdminAchievementController::class, 'update'])->name('update');
                Route::delete('/{achievement}', [AdminAchievementController::class, 'destroy'])->name('destroy');
                Route::post('/{achievement}/toggle-active', [AdminAchievementController::class, 'toggleActive'])->name('toggle-active');
                Route::get('/statistics/overview', [AdminAchievementController::class, 'statistics'])->name('statistics');
            });

            // Leaderboards Management
            Route::prefix('leaderboards')->name('leaderboards.')->group(function () {
                Route::get('/', [AdminLeaderboardController::class, 'index'])->name('index');
                Route::get('/create', [AdminLeaderboardController::class, 'create'])->name('create');
                Route::post('/', [AdminLeaderboardController::class, 'store'])->name('store');
                Route::get('/{leaderboard}', [AdminLeaderboardController::class, 'show'])->name('show');
                Route::get('/{leaderboard}/edit', [AdminLeaderboardController::class, 'edit'])->name('edit');
                Route::put('/{leaderboard}', [AdminLeaderboardController::class, 'update'])->name('update');
                Route::delete('/{leaderboard}', [AdminLeaderboardController::class, 'destroy'])->name('destroy');
                Route::post('/{leaderboard}/update', [AdminLeaderboardController::class, 'updateLeaderboard'])->name('update-data');
                Route::post('/update-all', [AdminLeaderboardController::class, 'updateAll'])->name('update-all');
                Route::post('/{leaderboard}/award-rewards', [AdminLeaderboardController::class, 'awardRewards'])->name('award-rewards');
                Route::post('/{leaderboard}/toggle-active', [AdminLeaderboardController::class, 'toggleActive'])->name('toggle-active');
            });

            // Challenges Management
            Route::prefix('challenges')->name('challenges.')->group(function () {
                Route::get('/', [AdminChallengeController::class, 'index'])->name('index');
                Route::get('/create', [AdminChallengeController::class, 'create'])->name('create');
                Route::post('/', [AdminChallengeController::class, 'store'])->name('store');
                Route::get('/statistics/overview', [AdminChallengeController::class, 'statistics'])->name('statistics');
                Route::post('/assign-to-user', [AdminChallengeController::class, 'assignToUser'])->name('assign-to-user');
                Route::post('/assign-to-multiple', [AdminChallengeController::class, 'assignToMultipleUsers'])->name('assign-to-multiple');
                Route::get('/{challenge}', [AdminChallengeController::class, 'show'])->name('show');
                Route::get('/{challenge}/edit', [AdminChallengeController::class, 'edit'])->name('edit');
                Route::put('/{challenge}', [AdminChallengeController::class, 'update'])->name('update');
                Route::delete('/{challenge}', [AdminChallengeController::class, 'destroy'])->name('destroy');
                Route::post('/{challenge}/toggle-active', [AdminChallengeController::class, 'toggleActive'])->name('toggle-active');
                Route::get('/{challenge}/participants', [AdminChallengeController::class, 'participants'])->name('participants');
                Route::post('/user-challenges/{userChallenge}/update-progress', [AdminChallengeController::class, 'updateUserProgress'])->name('update-user-progress');
                Route::post('/user-challenges/{userChallenge}/cancel', [AdminChallengeController::class, 'cancelUserChallenge'])->name('cancel-user-challenge');
            });

            // Shop Categories Management
            Route::prefix('shop/categories')->name('shop.categories.')->group(function () {
                Route::get('/', [AdminShopCategoryController::class, 'index'])->name('index');
                Route::post('/', [AdminShopCategoryController::class, 'store'])->name('store');
                Route::get('/{shopCategory}', [AdminShopCategoryController::class, 'show'])->name('show');
                Route::put('/{shopCategory}', [AdminShopCategoryController::class, 'update'])->name('update');
                Route::delete('/{shopCategory}', [AdminShopCategoryController::class, 'destroy'])->name('destroy');
                Route::post('/{shopCategory}/toggle-active', [AdminShopCategoryController::class, 'toggleActive'])->name('toggle-active');
            });

            // Shop Items Management
            Route::prefix('shop/items')->name('shop.items.')->group(function () {
                Route::get('/', [AdminShopItemController::class, 'index'])->name('index');
                Route::get('/create', [AdminShopItemController::class, 'create'])->name('create');
                Route::post('/', [AdminShopItemController::class, 'store'])->name('store');
                Route::get('/statistics/overview', [AdminShopItemController::class, 'statistics'])->name('statistics');
                Route::get('/top-selling', [AdminShopItemController::class, 'topSelling'])->name('top-selling');
                Route::get('/featured', [AdminShopItemController::class, 'featured'])->name('featured');
                Route::get('/{shopItem}', [AdminShopItemController::class, 'show'])->name('show');
                Route::get('/{shopItem}/edit', [AdminShopItemController::class, 'edit'])->name('edit');
                Route::put('/{shopItem}', [AdminShopItemController::class, 'update'])->name('update');
                Route::delete('/{shopItem}', [AdminShopItemController::class, 'destroy'])->name('destroy');
                Route::post('/{shopItem}/toggle-active', [AdminShopItemController::class, 'toggleActive'])->name('toggle-active');
                Route::post('/{shopItem}/apply-discount', [AdminShopItemController::class, 'applyDiscount'])->name('apply-discount');
                Route::post('/{shopItem}/remove-discount', [AdminShopItemController::class, 'removeDiscount'])->name('remove-discount');
                Route::post('/{shopItem}/update-stock', [AdminShopItemController::class, 'updateStock'])->name('update-stock');
            });

            // Purchases Management
            Route::prefix('shop/purchases')->name('shop.purchases.')->group(function () {
                Route::get('/', [AdminPurchaseController::class, 'index'])->name('index');
                Route::get('/statistics', [AdminPurchaseController::class, 'statistics'])->name('statistics');
                Route::get('/report', [AdminPurchaseController::class, 'report'])->name('report');
                Route::get('/{purchase}', [AdminPurchaseController::class, 'show'])->name('show');
            });

            // Social Activities Management
            Route::prefix('social/activities')->name('social.activities.')->group(function () {
                Route::get('/', [AdminSocialActivityController::class, 'index'])->name('index');
                Route::get('/statistics', [AdminSocialActivityController::class, 'statistics'])->name('statistics');
                Route::get('/{socialActivity}', [AdminSocialActivityController::class, 'show'])->name('show');
                Route::delete('/{socialActivity}', [AdminSocialActivityController::class, 'destroy'])->name('destroy');
            });

            // Competitions Management
            Route::prefix('social/competitions')->name('social.competitions.')->group(function () {
                Route::get('/', [AdminCompetitionController::class, 'index'])->name('index');
                Route::get('/statistics', [AdminCompetitionController::class, 'statistics'])->name('statistics');
                Route::get('/{competition}', [AdminCompetitionController::class, 'show'])->name('show');
                Route::post('/{competition}/end', [AdminCompetitionController::class, 'end'])->name('end');
                Route::delete('/{competition}', [AdminCompetitionController::class, 'destroy'])->name('destroy');
            });

            // Analytics
            Route::prefix('analytics')->name('analytics.')->group(function () {
                Route::get('/dashboard', [AdminAnalyticsController::class, 'dashboard'])->name('dashboard');
                Route::get('/points', [AdminAnalyticsController::class, 'points'])->name('points');
                Route::get('/levels', [AdminAnalyticsController::class, 'levels'])->name('levels');
                Route::get('/badges', [AdminAnalyticsController::class, 'badges'])->name('badges');
                Route::get('/engagement', [AdminAnalyticsController::class, 'engagement'])->name('engagement');
                Route::get('/students/{user}/report', [AdminAnalyticsController::class, 'studentReport'])->name('student-report');
                Route::post('/clear-cache', [AdminAnalyticsController::class, 'clearCache'])->name('clear-cache');
            });
        });

        // ========== Notification Management Routes ==========
        Route::prefix('notifications')->name('admin.notifications.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\NotificationManagementController::class, 'index'])->name('index');
            Route::get('/history', [\App\Http\Controllers\Admin\NotificationManagementController::class, 'history'])->name('history');
            Route::get('/statistics', [\App\Http\Controllers\Admin\NotificationManagementController::class, 'statistics'])->name('statistics');

            // Send notifications
            Route::post('/send-to-student', [\App\Http\Controllers\Admin\NotificationManagementController::class, 'sendToStudent'])->name('send.student');
            Route::post('/send-to-course', [\App\Http\Controllers\Admin\NotificationManagementController::class, 'sendToCourse'])->name('send.course');
            Route::post('/send-to-group', [\App\Http\Controllers\Admin\NotificationManagementController::class, 'sendToGroup'])->name('send.group');
            Route::post('/send-broadcast', [\App\Http\Controllers\Admin\NotificationManagementController::class, 'sendBroadcast'])->name('send.broadcast');

            // API endpoints
            Route::get('/api/students', [\App\Http\Controllers\Admin\NotificationManagementController::class, 'getStudents'])->name('api.students');
            Route::get('/api/courses', [\App\Http\Controllers\Admin\NotificationManagementController::class, 'getCourses'])->name('api.courses');
            Route::get('/api/groups', [\App\Http\Controllers\Admin\NotificationManagementController::class, 'getGroups'])->name('api.groups');
        });

        Route::prefix('notification-hub')->name('admin.notification-hub.')->group(function () {
            Route::get('/settings', [NotificationHubAdminController::class, 'settings'])->name('settings.get');
            Route::post('/settings', [NotificationHubAdminController::class, 'updateSettings'])->name('settings.update');
            Route::post('/events/toggle', [NotificationHubAdminController::class, 'updateEventToggle'])->name('events.toggle');

            Route::get('/templates', [NotificationHubAdminController::class, 'templates'])->name('templates.index');
            Route::post('/templates', [NotificationHubAdminController::class, 'storeTemplate'])->name('templates.store');
            Route::put('/templates/{id}', [NotificationHubAdminController::class, 'updateTemplate'])->name('templates.update');
            Route::delete('/templates/{id}', [NotificationHubAdminController::class, 'deleteTemplate'])->name('templates.delete');

            Route::post('/send-segmented', [NotificationHubAdminController::class, 'sendSegmented'])->name('send.segmented');
            Route::get('/logs', [NotificationHubAdminController::class, 'logs'])->name('logs.index');
        });

        // ========== Email Settings Routes ==========
        Route::prefix('settings/email')->name('admin.settings.email.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\EmailSettingController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\EmailSettingController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\EmailSettingController::class, 'store'])->name('store');
            Route::post('/test-temp', [\App\Http\Controllers\Admin\EmailSettingController::class, 'testTemp'])->name('test-temp');
            Route::get('/{emailSetting}/edit', [\App\Http\Controllers\Admin\EmailSettingController::class, 'edit'])->name('edit');
            Route::put('/{emailSetting}', [\App\Http\Controllers\Admin\EmailSettingController::class, 'update'])->name('update');
            Route::delete('/{emailSetting}', [\App\Http\Controllers\Admin\EmailSettingController::class, 'destroy'])->name('destroy');
            Route::post('/{emailSetting}/activate', [\App\Http\Controllers\Admin\EmailSettingController::class, 'activate'])->name('activate');
            Route::post('/{emailSetting}/test', [\App\Http\Controllers\Admin\EmailSettingController::class, 'test'])->name('test');
            Route::get('/provider/{provider}', [\App\Http\Controllers\Admin\EmailSettingController::class, 'getProviderPreset'])->name('provider.preset');
        });

        // ========== Email Templates Routes ==========
        Route::resource('email-templates', EmailTemplateController::class)->names([
            'index' => 'admin.email-templates.index',
            'create' => 'admin.email-templates.create',
            'store' => 'admin.email-templates.store',
            'show' => 'admin.email-templates.show',
            'edit' => 'admin.email-templates.edit',
            'update' => 'admin.email-templates.update',
            'destroy' => 'admin.email-templates.destroy',
        ]);
        Route::get('email-templates/{emailTemplate}/preview', [EmailTemplateController::class, 'preview'])->name('admin.email-templates.preview');
        Route::post('email-templates/{emailTemplate}/duplicate', [EmailTemplateController::class, 'duplicate'])->name('admin.email-templates.duplicate');
        Route::post('email-templates/{emailTemplate}/send-test', [EmailTemplateController::class, 'sendTest'])->name('admin.email-templates.send-test');

        // ========== Reminders Routes ==========
        Route::prefix('reminders')->name('admin.reminders.')->group(function () {
            Route::get('/', [ReminderController::class, 'index'])->name('index');
            Route::get('/statistics', [ReminderController::class, 'statistics'])->name('statistics');
            Route::get('/create', [ReminderController::class, 'create'])->name('create');
            Route::post('/', [ReminderController::class, 'store'])->name('store');
            Route::get('/{reminder}', [ReminderController::class, 'show'])->name('show');
            Route::get('/{reminder}/edit', [ReminderController::class, 'edit'])->name('edit');
            Route::put('/{reminder}', [ReminderController::class, 'update'])->name('update');
            Route::delete('/{reminder}', [ReminderController::class, 'destroy'])->name('destroy');
            Route::post('/{reminder}/send', [ReminderController::class, 'send'])->name('send');
        });

        // ========== Calendar Routes ==========
        Route::get('/calendar', [CalendarController::class, 'index'])->name('admin.calendar.index');
        Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('admin.calendar.events');

        // ========== Student Works Routes ==========
        Route::prefix('student-works')->name('admin.student-works.')->group(function () {
            Route::get('/', [StudentWorkController::class, 'index'])->name('index');
            Route::get('/create', [StudentWorkController::class, 'create'])->name('create');
            Route::post('/', [StudentWorkController::class, 'store'])->name('store');
            Route::get('/{studentWork}', [StudentWorkController::class, 'show'])->name('show');
            Route::get('/{studentWork}/edit', [StudentWorkController::class, 'edit'])->name('edit');
            Route::put('/{studentWork}', [StudentWorkController::class, 'update'])->name('update');
            Route::delete('/{studentWork}', [StudentWorkController::class, 'destroy'])->name('destroy');
            Route::post('/{studentWork}/approve', [StudentWorkController::class, 'approve'])->name('approve');
            Route::post('/{studentWork}/reject', [StudentWorkController::class, 'reject'])->name('reject');
            Route::post('/{studentWork}/toggle-featured', [StudentWorkController::class, 'toggleFeatured'])->name('toggle-featured');
            Route::post('/{studentWork}/toggle-active', [StudentWorkController::class, 'toggleActive'])->name('toggle-active');
        });

        // ========== Course Reviews Routes ==========
        Route::prefix('course-reviews')->name('admin.course-reviews.')->group(function () {
            Route::get('/', [AdminCourseReviewController::class, 'index'])->name('index');
            Route::get('/{review}', [AdminCourseReviewController::class, 'show'])->name('show');
            Route::post('/{review}/approve', [AdminCourseReviewController::class, 'approve'])->name('approve');
            Route::post('/{review}/reject', [AdminCourseReviewController::class, 'reject'])->name('reject');
            Route::post('/{review}/toggle-featured', [AdminCourseReviewController::class, 'toggleFeatured'])->name('toggle-featured');
            Route::delete('/{review}', [AdminCourseReviewController::class, 'destroy'])->name('destroy');
        });

        // ========== Platform Reviews Routes ==========
        Route::prefix('platform-reviews')->name('admin.platform-reviews.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\FrontendReviewController::class, 'index'])->name('index');
            Route::get('/{review}', [\App\Http\Controllers\Admin\FrontendReviewController::class, 'show'])->name('show');
            Route::post('/{review}/approve', [\App\Http\Controllers\Admin\FrontendReviewController::class, 'approve'])->name('approve');
            Route::post('/{review}/reject', [\App\Http\Controllers\Admin\FrontendReviewController::class, 'reject'])->name('reject');
            Route::post('/{review}/toggle-featured', [\App\Http\Controllers\Admin\FrontendReviewController::class, 'toggleFeatured'])->name('toggle-featured');
            Route::delete('/{review}', [\App\Http\Controllers\Admin\FrontendReviewController::class, 'destroy'])->name('destroy');
        });

        // ========== Webhooks Management Routes ==========
        Route::prefix('webhooks')->name('admin.webhooks.')->group(function () {
            Route::get('/', [WebhookManagementController::class, 'index'])->name('index');
            Route::get('/submissions', [WebhookManagementController::class, 'submissions'])->name('submissions');
            Route::get('/submissions/{submission}', [WebhookManagementController::class, 'showSubmission'])->name('submission.show');
            Route::post('/submissions/{submission}/retry', [WebhookManagementController::class, 'retrySubmission'])->name('submission.retry');
            Route::get('/logs', [WebhookManagementController::class, 'logs'])->name('logs');
            Route::get('/logs/{log}', [WebhookManagementController::class, 'showLog'])->name('log.show');
            Route::post('/cleanup', [WebhookManagementController::class, 'cleanupLogs'])->name('cleanup');
            Route::get('/export', [WebhookManagementController::class, 'export'])->name('export');

            // Webhook Tokens Management
            Route::prefix('tokens')->name('tokens.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\WebhookTokenController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\Admin\WebhookTokenController::class, 'create'])->name('create');
                Route::post('/', [\App\Http\Controllers\Admin\WebhookTokenController::class, 'store'])->name('store');
                Route::get('/{token}', [\App\Http\Controllers\Admin\WebhookTokenController::class, 'show'])->name('show');
                Route::get('/{token}/edit', [\App\Http\Controllers\Admin\WebhookTokenController::class, 'edit'])->name('edit');
                Route::put('/{token}', [\App\Http\Controllers\Admin\WebhookTokenController::class, 'update'])->name('update');
                Route::delete('/{token}', [\App\Http\Controllers\Admin\WebhookTokenController::class, 'destroy'])->name('destroy');
                Route::post('/{token}/toggle', [\App\Http\Controllers\Admin\WebhookTokenController::class, 'toggleActive'])->name('toggle');
                Route::get('/generate/token', [\App\Http\Controllers\Admin\WebhookTokenController::class, 'generateToken'])->name('generate');
            });
        });

        // ========== n8n Webhooks Integration Routes ==========
        Route::prefix('n8n')->name('admin.n8n.')->group(function () {
            // Dashboard
            Route::get('/', [N8nWebhookController::class, 'index'])->name('index');

            // Endpoints Management
            Route::get('/endpoints', [N8nWebhookController::class, 'endpoints'])->name('endpoints.index');
            Route::get('/endpoints/create', [N8nWebhookController::class, 'createEndpoint'])->name('endpoints.create');
            Route::post('/endpoints', [N8nWebhookController::class, 'storeEndpoint'])->name('endpoints.store');
            Route::get('/endpoints/{endpoint}', [N8nWebhookController::class, 'showEndpoint'])->name('endpoints.show');
            Route::get('/endpoints/{endpoint}/edit', [N8nWebhookController::class, 'editEndpoint'])->name('endpoints.edit');
            Route::put('/endpoints/{endpoint}', [N8nWebhookController::class, 'updateEndpoint'])->name('endpoints.update');
            Route::delete('/endpoints/{endpoint}', [N8nWebhookController::class, 'destroyEndpoint'])->name('endpoints.destroy');
            Route::post('/endpoints/{endpoint}/toggle', [N8nWebhookController::class, 'toggleEndpoint'])->name('endpoints.toggle');
            Route::post('/endpoints/{endpoint}/test', [N8nWebhookController::class, 'testEndpoint'])->name('endpoints.test');

            // Logs Management
            Route::get('/logs', [N8nWebhookController::class, 'logs'])->name('logs.index');
            Route::get('/logs/{log}', [N8nWebhookController::class, 'showLog'])->name('logs.show');
            Route::post('/logs/{log}/retry', [N8nWebhookController::class, 'retryLog'])->name('logs.retry');

            // Incoming Handlers Management
            Route::get('/handlers', [N8nWebhookController::class, 'handlers'])->name('handlers.index');
            Route::get('/handlers/create', [N8nWebhookController::class, 'createHandler'])->name('handlers.create');
            Route::post('/handlers', [N8nWebhookController::class, 'storeHandler'])->name('handlers.store');
            Route::get('/handlers/{handler}', [N8nWebhookController::class, 'showHandler'])->name('handlers.show');
            Route::get('/handlers/{handler}/edit', [N8nWebhookController::class, 'editHandler'])->name('handlers.edit');
            Route::put('/handlers/{handler}', [N8nWebhookController::class, 'updateHandler'])->name('handlers.update');
            Route::delete('/handlers/{handler}', [N8nWebhookController::class, 'destroyHandler'])->name('handlers.destroy');
            Route::post('/handlers/{handler}/toggle', [N8nWebhookController::class, 'toggleHandler'])->name('handlers.toggle');

            // Documentation & Statistics
            Route::get('/documentation', [N8nWebhookController::class, 'documentation'])->name('documentation');
            Route::get('/statistics', [N8nWebhookController::class, 'statistics'])->name('statistics');
        });

        // Frontend Courses Management
        Route::get('frontend-courses/ai/create', [AIFrontendCourseController::class, 'create'])->name('admin.frontend-courses.ai.create');
        Route::post('frontend-courses/ai/generate', [AIFrontendCourseController::class, 'generate'])->name('admin.frontend-courses.ai.generate');

        Route::resource('frontend-courses', FrontendCourseController::class)->names([
            'index' => 'admin.frontend-courses.index',
            'create' => 'admin.frontend-courses.create',
            'store' => 'admin.frontend-courses.store',
            'show' => 'admin.frontend-courses.show',
            'edit' => 'admin.frontend-courses.edit',
            'update' => 'admin.frontend-courses.update',
            'destroy' => 'admin.frontend-courses.destroy',
        ]);

        // ========== Blog Management Routes ==========

        // Blog Posts Management
        Route::prefix('blog')->name('admin.blog.')->group(function () {
            // AI Posts
            Route::get('ai-posts/create', [AIBlogPostController::class, 'create'])->name('ai-posts.create');
            Route::post('ai-posts', [AIBlogPostController::class, 'store'])->name('ai-posts.store');
            Route::post('ai-posts/generate', [AIBlogPostController::class, 'generate'])->name('ai-posts.generate');

            // Posts
            Route::resource('posts', BlogPostController::class)->names([
                'index' => 'posts.index',
                'create' => 'posts.create',
                'store' => 'posts.store',
                'show' => 'posts.show',
                'edit' => 'posts.edit',
                'update' => 'posts.update',
                'destroy' => 'posts.destroy',
            ]);
            Route::post('posts/{post}/toggle-featured', [BlogPostController::class, 'toggleFeatured'])->name('posts.toggle-featured');
            Route::post('posts/{post}/toggle-publish', [BlogPostController::class, 'togglePublish'])->name('posts.toggle-publish');
            Route::delete('posts/{post}/delete-image', [BlogPostController::class, 'deleteFeaturedImage'])->name('posts.delete-image');

            // Categories
            Route::resource('categories', BlogCategoryController::class)->names([
                'index' => 'categories.index',
                'create' => 'categories.create',
                'store' => 'categories.store',
                'show' => 'categories.show',
                'edit' => 'categories.edit',
                'update' => 'categories.update',
                'destroy' => 'categories.destroy',
            ]);
            Route::post('categories/{category}/toggle-active', [BlogCategoryController::class, 'toggleActive'])->name('categories.toggle-active');
            Route::post('categories/reorder', [BlogCategoryController::class, 'reorder'])->name('categories.reorder');

            // Tags
            Route::resource('tags', BlogTagController::class)->names([
                'index' => 'tags.index',
                'create' => 'tags.create',
                'store' => 'tags.store',
                'show' => 'tags.show',
                'edit' => 'tags.edit',
                'update' => 'tags.update',
                'destroy' => 'tags.destroy',
            ]);
            Route::post('tags/update-counts', [BlogTagController::class, 'updatePostsCount'])->name('tags.update-counts');
        });

        // ========== Documentation (التوثيق) ==========
        Route::prefix('docs')->name('admin.docs.')->group(function () {
            Route::resource('categories', DocumentationCategoryController::class)
                ->parameters(['categories' => 'documentation_category'])
                ->except(['show'])
                ->names([
                    'index' => 'categories.index',
                    'create' => 'categories.create',
                    'store' => 'categories.store',
                    'edit' => 'categories.edit',
                    'update' => 'categories.update',
                    'destroy' => 'categories.destroy',
                ]);
            Route::post('categories/{documentation_category}/toggle-active', [DocumentationCategoryController::class, 'toggleActive'])
                ->name('categories.toggle-active');

            Route::get('ai-pages/create', [AIDocumentationPageController::class, 'create'])->name('ai-pages.create');
            Route::get('ai-pages/improve', [AIDocumentationPageController::class, 'improve'])->name('ai-pages.improve');
            Route::post('ai-pages/generate', [AIDocumentationPageController::class, 'generate'])->name('ai-pages.generate');
            Route::post('ai-pages/refine', [AIDocumentationPageController::class, 'refine'])->name('ai-pages.refine');
            Route::post('ai-pages', [AIDocumentationPageController::class, 'store'])->name('ai-pages.store');

            Route::resource('pages', DocumentationPageController::class)
                ->parameters(['pages' => 'documentation_page'])
                ->except(['show'])
                ->names([
                    'index' => 'pages.index',
                    'create' => 'pages.create',
                    'store' => 'pages.store',
                    'edit' => 'pages.edit',
                    'update' => 'pages.update',
                    'destroy' => 'pages.destroy',
                ]);
            Route::post('pages/{documentation_page}/toggle-publish', [DocumentationPageController::class, 'togglePublish'])
                ->name('pages.toggle-publish');
        });

        // ========== FAQs Management Routes ==========
        Route::resource('faqs', FaqController::class)->names([
            'index' => 'admin.faqs.index',
            'create' => 'admin.faqs.create',
            'store' => 'admin.faqs.store',
            'edit' => 'admin.faqs.edit',
            'update' => 'admin.faqs.update',
            'destroy' => 'admin.faqs.destroy',
        ]);
        Route::post('faqs/{faq}/toggle-active', [FaqController::class, 'toggleActive'])->name('admin.faqs.toggle-active');

        // ========== Contact Settings Routes ==========
        Route::get('contact-settings/edit', [ContactSettingController::class, 'edit'])->name('admin.contact-settings.edit');
        Route::put('contact-settings', [ContactSettingController::class, 'update'])->name('admin.contact-settings.update');

        // ========== Google Settings Routes ==========
        Route::get('google-settings/edit', [GoogleSettingController::class, 'edit'])->name('admin.google-settings.edit');
        Route::put('google-settings', [GoogleSettingController::class, 'update'])->name('admin.google-settings.update');

        // ========== Site Settings Routes ==========
        Route::get('settings/site', [SiteSettingController::class, 'index'])->name('admin.settings.site.index');
        Route::post('settings/site', [SiteSettingController::class, 'update'])->name('admin.settings.site.update');

        // ========== AI Routes ==========
        Route::prefix('ai')->name('admin.ai.')->group(function () {
            // AI Models
            Route::resource('models', AIModelController::class)->names([
                'index' => 'models.index',
                'create' => 'models.create',
                'store' => 'models.store',
                'edit' => 'models.edit',
                'update' => 'models.update',
                'destroy' => 'models.destroy',
            ]);
            Route::post('models/{model}/test', [AIModelController::class, 'test'])->name('models.test');
            Route::post('models/test-temp', [AIModelController::class, 'testTemp'])->name('models.test-temp');
            Route::post('models/{model}/set-default', [AIModelController::class, 'setDefault'])->name('models.set-default');
            Route::post('models/{model}/toggle-active', [AIModelController::class, 'toggleActive'])->name('models.toggle-active');
            // Groq models fetch
            Route::post('models/fetch-groq-models', [AIModelController::class, 'fetchGroqModels'])->name('models.fetch-groq-models');

            // Question Creation (Direct creation to question bank)
            Route::get('question-creation/create', [\App\Http\Controllers\Admin\AIQuestionCreationController::class, 'create'])->name('question-creation.create');
            Route::post('question-creation', [\App\Http\Controllers\Admin\AIQuestionCreationController::class, 'store'])->name('question-creation.store');

            Route::resource('question-generations', AIQuestionGenerationController::class)->names([
                'index' => 'question-generations.index',
                'create' => 'question-generations.create',
                'store' => 'question-generations.store',
                'show' => 'question-generations.show',
            ]);

            Route::post('question-generations/{generation}/process', [AIQuestionGenerationController::class, 'process'])->name('question-generations.process');
            Route::post('question-generations/{generation}/save', [AIQuestionGenerationController::class, 'save'])->name('question-generations.save');
            Route::post('question-generations/{generation}/save-selected', [AIQuestionGenerationController::class, 'saveSelected'])->name('question-generations.save-selected');
            Route::post('question-generations/{generation}/regenerate', [AIQuestionGenerationController::class, 'regenerate'])->name('question-generations.regenerate');

            // Question Solutions
            Route::get('question-solutions', [AIQuestionSolvingController::class, 'index'])->name('question-solutions.index');
            Route::post('question-solutions/solve/{question}', [AIQuestionSolvingController::class, 'solve'])->name('question-solutions.solve');
            Route::post('question-solutions/solve-multiple', [AIQuestionSolvingController::class, 'solveMultiple'])->name('question-solutions.solve-multiple');
            Route::post('question-solutions/{solution}/verify', [AIQuestionSolvingController::class, 'verify'])->name('question-solutions.verify');
            Route::get('question-solutions/{solution}', [AIQuestionSolvingController::class, 'show'])->name('question-solutions.show');

            // Student Feedback
            Route::get('student-feedback', [AIStudentFeedbackController::class, 'index'])->name('student-feedback.index');
            Route::get('student-feedback/create', [AIStudentFeedbackController::class, 'create'])->name('student-feedback.create');
            Route::post('student-feedback/store', [AIStudentFeedbackController::class, 'store'])->name('student-feedback.store');
            Route::post('student-feedback/generate/{student}', [AIStudentFeedbackController::class, 'generateFeedback'])->name('student-feedback.generate-feedback');
            Route::get('student-feedback/{studentFeedback}', [AIStudentFeedbackController::class, 'show'])->name('student-feedback.show');

            // Student progress AI reports (course / group batch)
            Route::get('student-progress-reports', [StudentCourseAiReportController::class, 'index'])->name('student-progress-reports.index');
            Route::get('student-progress-reports/batches', [StudentCourseAiReportController::class, 'batchesIndex'])->name('student-progress-reports.batches.index');
            Route::get('student-progress-reports/batches/{batch}', [StudentCourseAiReportController::class, 'showBatch'])->name('student-progress-reports.batches.show');
            Route::get('student-progress-reports/create', [StudentCourseAiReportController::class, 'create'])->name('student-progress-reports.create');
            Route::get('student-progress-reports/enrolled-students', [StudentCourseAiReportController::class, 'enrolledStudents'])->name('student-progress-reports.enrolled-students');
            Route::get('student-progress-reports/course-groups', [StudentCourseAiReportController::class, 'courseGroups'])->name('student-progress-reports.course-groups');
            Route::post('student-progress-reports/preview', [StudentCourseAiReportController::class, 'preview'])->name('student-progress-reports.preview');
            Route::post('student-progress-reports/dispatch', [StudentCourseAiReportController::class, 'dispatchBatch'])->name('student-progress-reports.dispatch');
            Route::get('student-progress-reports/{report}', [StudentCourseAiReportController::class, 'show'])->name('student-progress-reports.show');

            // Content
            Route::post('content/summarize', [AIContentController::class, 'summarize'])->name('content.summarize');
            Route::get('content/lesson-summary/{lesson}', [AIContentController::class, 'lessonSummary'])->name('content.lesson-summary');
            Route::post('content/improve', [AIContentController::class, 'improve'])->name('content.improve');
            Route::post('content/grammar-check', [AIContentController::class, 'grammarCheck'])->name('content.grammar-check');

            // Settings
            Route::get('settings', [AISettingsController::class, 'index'])->name('settings.index');
            Route::put('settings', [AISettingsController::class, 'update'])->name('settings.update');
            Route::get('settings/grading', [AIGradingSettingsController::class, 'index'])->name('settings.grading');
            Route::put('settings/grading', [AIGradingSettingsController::class, 'update'])->name('settings.grading.update');
        });

        // ========== Laravel AI SDK (parallel stack; DB-backed credentials) ==========
        Route::prefix('ai-sdk')->name('admin.ai-sdk.')->group(function () {
            Route::post('models/test-temp', [LaravelAiModelController::class, 'testTemp'])->name('models.test-temp');
            Route::resource('models', LaravelAiModelController::class)
                ->parameters(['models' => 'laravel_ai_model'])
                ->except(['show'])
                ->names([
                    'index' => 'models.index',
                    'create' => 'models.create',
                    'store' => 'models.store',
                    'edit' => 'models.edit',
                    'update' => 'models.update',
                    'destroy' => 'models.destroy',
                ]);
            Route::post('models/{laravel_ai_model}/test', [LaravelAiModelController::class, 'test'])->name('models.test');
        });

        // ========== WhatsApp Routes ==========
        Route::prefix('whatsapp-settings')->name('admin.whatsapp-settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\WhatsAppSettingsController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\WhatsAppSettingsController::class, 'update'])->name('update');
            Route::post('/test-connection', [\App\Http\Controllers\Admin\WhatsAppSettingsController::class, 'testConnection'])->name('test-connection');
            Route::get('/queue-worker/status', [\App\Http\Controllers\Admin\WhatsAppSettingsController::class, 'queueWorkerStatus'])->name('queue-worker.status');
            Route::post('/queue-worker/start', [\App\Http\Controllers\Admin\WhatsAppSettingsController::class, 'queueWorkerStart'])->name('queue-worker.start');
            Route::post('/queue-worker/stop', [\App\Http\Controllers\Admin\WhatsAppSettingsController::class, 'queueWorkerStop'])->name('queue-worker.stop');
        });

        Route::prefix('whatsapp-messages')->name('admin.whatsapp-messages.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'index'])->name('index');
            Route::get('/send', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'create'])->name('create');
            Route::get('/search-students', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'searchStudents'])->name('search-students');
            Route::post('/send', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'send'])->name('send');
            Route::post('/broadcast', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'broadcast'])->name('broadcast');
            Route::get('/broadcast/students-count', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'getStudentsCount'])->name('broadcast.students-count');
            Route::get('/broadcasts', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'broadcastsIndex'])->name('broadcasts.index');
            Route::get('/broadcasts/{broadcast}', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'showBroadcast'])->name('broadcasts.show');
            Route::post('/{message}/retry', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'retry'])->name('retry');
            Route::get('/{message}', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'show'])->name('show');
        });

        // WhatsApp Message Templates (قوالب رسائل واتساب)
        Route::prefix('whatsapp-templates')->name('admin.whatsapp-templates.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\WhatsAppMessageTemplateController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\WhatsAppMessageTemplateController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\WhatsAppMessageTemplateController::class, 'store'])->name('store');
            Route::get('/{whatsapp_template}/edit', [\App\Http\Controllers\Admin\WhatsAppMessageTemplateController::class, 'edit'])->name('edit');
            Route::put('/{whatsapp_template}', [\App\Http\Controllers\Admin\WhatsAppMessageTemplateController::class, 'update'])->name('update');
            Route::delete('/{whatsapp_template}', [\App\Http\Controllers\Admin\WhatsAppMessageTemplateController::class, 'destroy'])->name('destroy');
            Route::get('/{whatsapp_template}/json', [\App\Http\Controllers\Admin\WhatsAppMessageTemplateController::class, 'getTemplate'])->name('get');
        });

        // WhatsApp Web Routes
        Route::prefix('whatsapp-web')->name('admin.whatsapp-web.')->group(function () {
            Route::get('/connect', [\App\Http\Controllers\Admin\WhatsAppWebController::class, 'connect'])->name('connect');
            Route::post('/start-connection', [\App\Http\Controllers\Admin\WhatsAppWebController::class, 'startConnection'])->name('start-connection');
            Route::get('/qr/{sessionId}', [\App\Http\Controllers\Admin\WhatsAppWebController::class, 'getQrCode'])->name('qr');
            Route::get('/status/{sessionId}', [\App\Http\Controllers\Admin\WhatsAppWebController::class, 'getStatus'])->name('status');
            Route::post('/disconnect/{sessionId}', [\App\Http\Controllers\Admin\WhatsAppWebController::class, 'disconnect'])->name('disconnect');
        });

        // WhatsApp Web Settings Routes
        Route::prefix('whatsapp-web-settings')->name('admin.whatsapp-web-settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\WhatsAppWebSettingsController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\WhatsAppWebSettingsController::class, 'update'])->name('update');
            Route::post('/test-connection', [\App\Http\Controllers\Admin\WhatsAppWebSettingsController::class, 'testConnection'])->name('test-connection');
        });

        // ========== User Sessions Routes ==========
        Route::prefix('user-sessions')->name('admin.user-sessions.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\UserSessionController::class, 'index'])->name('index');
            Route::get('/active', [\App\Http\Controllers\Admin\UserSessionController::class, 'activeSessions'])->name('active');
            Route::get('/statistics', [\App\Http\Controllers\Admin\UserSessionController::class, 'statistics'])->name('statistics');
            Route::get('/user/{userId}', [\App\Http\Controllers\Admin\UserSessionController::class, 'userSessions'])->name('user');
            Route::get('/{id}', [\App\Http\Controllers\Admin\UserSessionController::class, 'show'])->name('show');
        });

        // ========== User Devices Routes ==========
        Route::prefix('user-devices')->name('admin.user-devices.')->group(function () {
            Route::get('/', [UserDeviceController::class, 'index'])->name('index');
            Route::get('/user/{userId}', [UserDeviceController::class, 'userDevices'])->name('user');
            Route::get('/{id}', [UserDeviceController::class, 'show'])->name('show');
            Route::post('/{id}/block', [UserDeviceController::class, 'block'])->name('block');
            Route::post('/{id}/unblock', [UserDeviceController::class, 'unblock'])->name('unblock');
            Route::post('/{id}/trust', [UserDeviceController::class, 'trust'])->name('trust');
            Route::post('/{id}/untrust', [UserDeviceController::class, 'untrust'])->name('untrust');
            Route::put('/{id}/device-name', [UserDeviceController::class, 'updateDeviceName'])->name('update-name');
        });

        // ===============================================
        // نظام النسخ الاحتياطي
        // ===============================================
        Route::prefix('backups')->name('backups.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\BackupController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\BackupController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\BackupController::class, 'store'])->name('store');
            Route::get('/{backup}', [\App\Http\Controllers\Admin\BackupController::class, 'show'])->name('show');
            Route::get('/{backup}/status', [\App\Http\Controllers\Admin\BackupController::class, 'status'])->name('status');
            Route::post('/{backup}/run', [\App\Http\Controllers\Admin\BackupController::class, 'run'])->name('run');
            Route::post('/{backup}/restore', [\App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('restore');
            Route::get('/{backup}/download', [\App\Http\Controllers\Admin\BackupController::class, 'download'])->name('download');
            Route::delete('/{backup}', [\App\Http\Controllers\Admin\BackupController::class, 'destroy'])->name('destroy');
            Route::get('/stats/overview', [\App\Http\Controllers\Admin\BackupController::class, 'stats'])->name('stats');
        });

        Route::prefix('backup-schedules')->name('backup-schedules.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\BackupScheduleController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\BackupScheduleController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\BackupScheduleController::class, 'store'])->name('store');
            Route::get('/{schedule}', [\App\Http\Controllers\Admin\BackupScheduleController::class, 'show'])->name('show');
            Route::get('/{schedule}/edit', [\App\Http\Controllers\Admin\BackupScheduleController::class, 'edit'])->name('edit');
            Route::put('/{schedule}', [\App\Http\Controllers\Admin\BackupScheduleController::class, 'update'])->name('update');
            Route::delete('/{schedule}', [\App\Http\Controllers\Admin\BackupScheduleController::class, 'destroy'])->name('destroy');
            Route::post('/{schedule}/execute', [\App\Http\Controllers\Admin\BackupScheduleController::class, 'execute'])->name('execute');
            Route::post('/{schedule}/toggle-active', [\App\Http\Controllers\Admin\BackupScheduleController::class, 'toggleActive'])->name('toggle-active');
        });

        Route::prefix('backup-storage')->name('backup-storage.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\BackupStorageController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\BackupStorageController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\BackupStorageController::class, 'store'])->name('store');
            Route::get('/{config}/edit', [\App\Http\Controllers\Admin\BackupStorageController::class, 'edit'])->name('edit');
            Route::put('/{config}', [\App\Http\Controllers\Admin\BackupStorageController::class, 'update'])->name('update');
            Route::delete('/{config}', [\App\Http\Controllers\Admin\BackupStorageController::class, 'destroy'])->name('destroy');
            Route::post('/{config}/test', [\App\Http\Controllers\Admin\BackupStorageController::class, 'test'])->name('test');
            Route::post('/test-connection', [\App\Http\Controllers\Admin\BackupStorageController::class, 'testConnection'])->name('test-connection');
            Route::get('/analytics', [\App\Http\Controllers\Admin\BackupStorageAnalyticsController::class, 'index'])->name('analytics');
        });

        // App Storage
        Route::prefix('app-storage')->name('app-storage.')->group(function () {
            Route::get('/configs', [\App\Http\Controllers\Admin\AppStorageController::class, 'index'])->name('configs.index');
            Route::get('/configs/create', [\App\Http\Controllers\Admin\AppStorageController::class, 'create'])->name('configs.create');
            Route::post('/configs', [\App\Http\Controllers\Admin\AppStorageController::class, 'store'])->name('configs.store');
            Route::get('/configs/{config}', [\App\Http\Controllers\Admin\AppStorageController::class, 'show'])->name('configs.show');
            Route::get('/configs/{config}/edit', [\App\Http\Controllers\Admin\AppStorageController::class, 'edit'])->name('configs.edit');
            Route::put('/configs/{config}', [\App\Http\Controllers\Admin\AppStorageController::class, 'update'])->name('configs.update');
            Route::delete('/configs/{config}', [\App\Http\Controllers\Admin\AppStorageController::class, 'destroy'])->name('configs.destroy');
            Route::post('/configs/test-connection', [\App\Http\Controllers\Admin\AppStorageController::class, 'testConnection'])->name('configs.test-connection');
            Route::post('/configs/{config}/test', [\App\Http\Controllers\Admin\AppStorageController::class, 'test'])->name('configs.test');
            Route::get('/analytics', [\App\Http\Controllers\Admin\AppStorageAnalyticsController::class, 'index'])->name('analytics');
        });

        Route::prefix('storage-disk-mappings')->name('storage-disk-mappings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\StorageDiskMappingController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\StorageDiskMappingController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\StorageDiskMappingController::class, 'store'])->name('store');
            Route::get('/{mapping}', [\App\Http\Controllers\Admin\StorageDiskMappingController::class, 'show'])->name('show');
            Route::get('/{mapping}/edit', [\App\Http\Controllers\Admin\StorageDiskMappingController::class, 'edit'])->name('edit');
            Route::put('/{mapping}', [\App\Http\Controllers\Admin\StorageDiskMappingController::class, 'update'])->name('update');
            Route::delete('/{mapping}', [\App\Http\Controllers\Admin\StorageDiskMappingController::class, 'destroy'])->name('destroy');
        });

    });
