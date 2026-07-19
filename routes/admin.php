<?php

use App\Http\Controllers\Admin\AccessRestrictionController;
use App\Http\Controllers\Admin\AIBlogPostController;
use App\Http\Controllers\Admin\AIContentController;
use App\Http\Controllers\Admin\AIDocumentationPageController;
use App\Http\Controllers\Admin\AIFrontendCourseController;
use App\Http\Controllers\Admin\AIGradingSettingsController;
use App\Http\Controllers\Admin\AIModelController;
use App\Http\Controllers\Admin\AIQuestionCreationController;
use App\Http\Controllers\Admin\AIQuestionGenerationController;
use App\Http\Controllers\Admin\AIQuestionSolvingController;
use App\Http\Controllers\Admin\AISettingsController;
use App\Http\Controllers\Admin\AIStudentFeedbackController;
use App\Http\Controllers\Admin\AppStorageAnalyticsController;
use App\Http\Controllers\Admin\AppStorageController;
use App\Http\Controllers\Admin\AssignmentController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\BackupScheduleController;
use App\Http\Controllers\Admin\BackupStorageAnalyticsController;
use App\Http\Controllers\Admin\BackupStorageController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\BlogTagController;
use App\Http\Controllers\Admin\BulkEmailController;
use App\Http\Controllers\Admin\BulkEmailSettingsController;
use App\Http\Controllers\Admin\BulkUserImportController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\ChallengeGradingController;
use App\Http\Controllers\Admin\ContactSettingController;
use App\Http\Controllers\Admin\CourseCategoryController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\CourseDocumentationLinkController;
use App\Http\Controllers\Admin\CourseSimulatorLinkController;
use App\Http\Controllers\Admin\CourseEnrollmentController;
use App\Http\Controllers\Admin\CourseGroupController;
use App\Http\Controllers\Admin\CourseModuleCompletionSummaryController;
use App\Http\Controllers\Admin\CourseModuleController;
use App\Http\Controllers\Admin\CourseReviewController as AdminCourseReviewController;
use App\Http\Controllers\Admin\CourseSectionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DatabaseInfoController;
use App\Http\Controllers\Admin\DocumentationCategoryController;
use App\Http\Controllers\Admin\DocumentationPageController;
use App\Http\Controllers\Admin\DocumentationPageDocumentationLinkController;
use App\Http\Controllers\Admin\EmailSettingController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\EvolutionChatsController;
use App\Http\Controllers\Admin\EvolutionContactsController;
use App\Http\Controllers\Admin\EvolutionGroupCompareController;
use App\Http\Controllers\Admin\EvolutionGroupsController;
use App\Http\Controllers\Admin\EvolutionInstanceController;
use App\Http\Controllers\Admin\EvolutionSendController;
use App\Http\Controllers\Admin\EvolutionSettingsController;
use App\Http\Controllers\Admin\EvolutionWebhookAdminController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FlaxxaWapiController;
use App\Http\Controllers\Admin\FlaxxaWapiSettingsController;
use App\Http\Controllers\Admin\FrontendCourseController;
use App\Http\Controllers\Admin\FrontendReviewController;
use App\Http\Controllers\Admin\Gamification\AchievementController as AdminAchievementController;
use App\Http\Controllers\Admin\Gamification\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\Gamification\BadgeController as AdminBadgeController;
use App\Http\Controllers\Admin\Gamification\BadgeReportController as AdminBadgeReportController;
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
use App\Http\Controllers\Admin\LessonSimulatorAiController;
use App\Http\Controllers\Admin\LessonSimulatorController;
use App\Http\Controllers\Admin\SimulatorCategoryController;
use App\Http\Controllers\Admin\MarketingAnalyticsController;
use App\Http\Controllers\Admin\MetaPixelSettingController;
use App\Http\Controllers\Admin\ModuleCompletionReportController;
use App\Http\Controllers\Admin\N8nWebhookController;
use App\Http\Controllers\Admin\NotificationHubAdminController;
use App\Http\Controllers\Admin\NotificationManagementController;
use App\Http\Controllers\Admin\PasswordResetMessageSettingsController;
use App\Http\Controllers\Admin\AccountCreatedMessageSettingsController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PaymentWhatsAppMessageSettingsController;
use App\Http\Controllers\Admin\PhoneOtpSettingsController;
use App\Http\Controllers\Admin\ProgrammingChallengeController;
use App\Http\Controllers\Admin\ProjectChallengeController;
use App\Http\Controllers\Admin\ProjectGradingController;
use App\Http\Controllers\Admin\ProjectTeamController;
use App\Http\Controllers\Admin\QuestionBankController;
use App\Http\Controllers\Admin\QuestionBankExportController;
use App\Http\Controllers\Admin\QuestionBankAiGenerationController;
use App\Http\Controllers\Admin\QuestionBankTypeImportController;
use App\Http\Controllers\Admin\QuestionModuleController;
use App\Http\Controllers\Admin\QuestionModuleGradingController;
use App\Http\Controllers\Admin\QuestionPoolController;
use App\Http\Controllers\Admin\QuizAnalyticsController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\QuizGradingController;
use App\Http\Controllers\Admin\QuizPreviewController;
use App\Http\Controllers\Admin\ReminderController;
use App\Http\Controllers\Admin\ResourceController as AdminResourceController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\StorageDiskMappingController;
use App\Http\Controllers\Admin\StorageInventoryController;
use App\Http\Controllers\Admin\StudentCourseAiReportController;
use App\Http\Controllers\Admin\StudentGiftController;
use App\Http\Controllers\Admin\StudentProfileCardController;
use App\Http\Controllers\Admin\StudentProfileCardSettingController;
use App\Http\Controllers\Admin\StudentWeeklyReportController as AdminStudentWeeklyReportController;
use App\Http\Controllers\Admin\StudentWeeklyReportScheduleController as AdminStudentWeeklyReportScheduleController;
use App\Http\Controllers\Admin\StudentWorkController;
use App\Http\Controllers\Admin\TrainingCampController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminHeaderNotificationController;
use App\Http\Controllers\Admin\UserDeviceController;
use App\Http\Controllers\Admin\DeviceSecuritySettingsController;
use App\Http\Controllers\Admin\UserSendEmailController;
use App\Http\Controllers\Admin\UserSendWhatsAppController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\UserSessionController;
use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\WapiAutomationRuleController;
use App\Http\Controllers\Admin\WapiTemplateController;
use App\Http\Controllers\Admin\WebhookManagementController;
use App\Http\Controllers\Admin\WebhookTokenController;
use App\Http\Controllers\Admin\WhatsAppMessageController;
use App\Http\Controllers\Admin\WhatsAppMessageTemplateController;
use App\Http\Controllers\Admin\TelegramGroupController;
use App\Http\Controllers\Admin\TelegramMessageController;
use App\Http\Controllers\Admin\TelegramMessageTemplateController;
use App\Http\Controllers\Admin\TelegramSettingsController;
use App\Http\Controllers\Admin\WhatsAppSettingsController;
use App\Http\Controllers\Admin\WhatsAppWebController;
use App\Http\Controllers\Admin\WhatsAppWebSettingsController;
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
        Route::delete('users/bulk-destroy', [UserController::class, 'bulkDestroy'])->name('users.bulk-destroy');
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::put('users/{user}/change-password', [UserController::class, 'updatePassword'])->name('users.update-password');
        Route::post('users/{user}/send-email/preview', [UserSendEmailController::class, 'preview'])->name('users.send-email.preview');
        Route::post('users/{user}/send-email', [UserSendEmailController::class, 'send'])->name('users.send-email.send');
        Route::post('users/{user}/send-whatsapp/preview', [UserSendWhatsAppController::class, 'preview'])->name('users.send-whatsapp.preview');
        Route::post('users/{user}/send-whatsapp', [UserSendWhatsAppController::class, 'send'])->name('users.send-whatsapp.send');
        Route::post('users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
        Route::get('users/{userId}/courses', [UserController::class, 'showCourses'])->name('admin.users.courses');
        Route::get('users/{user}/student-details', [UserController::class, 'studentDetails'])->name('users.student-details');
        Route::post('users/{user}/add-to-group', [UserController::class, 'addToGroup'])->name('users.add-to-group');
        Route::post('users/{user}/add-to-camp', [UserController::class, 'addToCamp'])->name('users.add-to-camp');
        Route::post('users/{user}/camp-enrollments/{enrollment}/update', [UserController::class, 'updateCampEnrollment'])->name('users.update-camp-enrollment');
        Route::post('users/{user}/camp-enrollments/{enrollment}/remove', [UserController::class, 'removeFromCamp'])->name('users.remove-from-camp');
        Route::post('users/{user}/record-payment', [UserController::class, 'recordPayment'])->name('users.record-payment');
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
            Route::get('/{enrollment}/receipt', [TrainingCampController::class, 'enrollmentReceipt'])->name('receipt');
            Route::delete('/{enrollment}', [TrainingCampController::class, 'destroyEnrollment'])->name('destroy');
            Route::post('/{enrollment}/approve', [TrainingCampController::class, 'approveEnrollment'])->name('approve');
            Route::post('/{enrollment}/reject', [TrainingCampController::class, 'rejectEnrollment'])->name('reject');
            Route::post('/{enrollment}/update-status', [TrainingCampController::class, 'updateEnrollmentStatus'])->name('update-status');
            Route::post('/{enrollment}/update-payment-status', [TrainingCampController::class, 'updateEnrollmentPaymentStatus'])->name('update-payment-status');
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
        Route::post('payments/{id}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
        Route::post('payments/{id}/reject', [PaymentController::class, 'reject'])->name('payments.reject');
        Route::get('payments/{id}/receipt', [PaymentController::class, 'downloadReceipt'])->name('payments.receipt');
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
        Route::get('courses/{course}/restrictions/groups', [AccessRestrictionController::class, 'getCourseRestrictionGroups'])->name('courses.restrictions.groups');
        Route::get('courses/{course}/restrictions/bulk-state', [AccessRestrictionController::class, 'getCourseRestrictionBulkState'])->name('courses.restrictions.bulk-state');
        Route::post('courses/{course}/modules/restrictions/sync-bulk', [AccessRestrictionController::class, 'syncBulkModuleRestrictions'])->name('courses.modules.restrictions.sync-bulk');
        Route::get('courses/{course}/modules/{module}/completions', [ModuleCompletionReportController::class, 'index'])->name('courses.modules.completions');
        Route::post('courses/{course}/modules/{module}/completions/preview-whatsapp', [ModuleCompletionReportController::class, 'previewWhatsApp'])->name('courses.modules.completions.preview-whatsapp');
        Route::post('courses/{course}/modules/{module}/completions/send-whatsapp', [ModuleCompletionReportController::class, 'sendWhatsApp'])->name('courses.modules.completions.send-whatsapp');
        Route::post('courses/{course}/modules/{module}/completions/preview-email', [ModuleCompletionReportController::class, 'previewEmail'])->name('courses.modules.completions.preview-email');
        Route::post('courses/{course}/modules/{module}/completions/send-email', [ModuleCompletionReportController::class, 'sendEmail'])->name('courses.modules.completions.send-email');
        Route::get('courses/{course}/completion-summary', [CourseModuleCompletionSummaryController::class, 'index'])->name('courses.completion-summary');

        Route::get('courses/{course}/documentation-links/search', [CourseDocumentationLinkController::class, 'searchPages'])->name('courses.documentation-links.search');
        Route::get('courses/{course}/documentation-links/categories', [CourseDocumentationLinkController::class, 'categories'])->name('courses.documentation-links.categories');
        Route::get('courses/{course}/documentation-links/sections', [CourseDocumentationLinkController::class, 'sections'])->name('courses.documentation-links.sections');
        Route::get('courses/{course}/documentation-links/lesson-modules', [CourseDocumentationLinkController::class, 'lessonModules'])->name('courses.documentation-links.lesson-modules');
        Route::post('courses/{course}/documentation-links', [CourseDocumentationLinkController::class, 'store'])->name('courses.documentation-links.store');
        Route::delete('documentation-links/{documentation_page_link}', [CourseDocumentationLinkController::class, 'destroy'])->name('documentation-page-links.destroy');
        Route::get('courses/{course}/simulator-links/search', [CourseSimulatorLinkController::class, 'search'])->name('courses.simulator-links.search');
        Route::post('courses/{course}/simulator-links', [CourseSimulatorLinkController::class, 'store'])->name('courses.simulator-links.store');

        // Course Sections routes
        Route::resource('courses.sections', CourseSectionController::class)->except(['index']);
        Route::post('sections/{id}/toggle-visibility', [CourseSectionController::class, 'toggleVisibility'])->name('sections.toggle-visibility');
        Route::post('sections/{id}/toggle-lock', [CourseSectionController::class, 'toggleLock'])->name('sections.toggle-lock');
        Route::post('courses/{course}/sections/reorder', [CourseSectionController::class, 'reorder'])->name('courses.sections.reorder');

        // Section Access Restrictions routes
        Route::get('sections/{section}/restrictions', [AccessRestrictionController::class, 'getSectionRestrictions'])->name('sections.restrictions.get');
        Route::post('sections/{section}/restrictions/sync', [AccessRestrictionController::class, 'syncSectionRestrictions'])->name('sections.restrictions.sync');

        // Module Access Restrictions routes
        Route::get('modules/{module}/restrictions', [AccessRestrictionController::class, 'getModuleRestrictions'])->name('modules.restrictions.get');
        Route::post('modules/{module}/restrictions/sync', [AccessRestrictionController::class, 'syncModuleRestrictions'])->name('modules.restrictions.sync');

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
        Route::post('sections/{section}/modules/reorder', [CourseModuleController::class, 'reorder'])->name('sections.modules.reorder');
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
        Route::post('groups/{groupId}/bulk-deactivate-members', [CourseGroupController::class, 'bulkDeactivateMembers'])->name('groups.bulk-deactivate-members');
        Route::post('groups/{groupId}/bulk-reactivate-members', [CourseGroupController::class, 'bulkReactivateMembers'])->name('groups.bulk-reactivate-members');
        Route::post('groups/{groupId}/update-member-role/{memberId}', [CourseGroupController::class, 'updateMemberRole'])->name('groups.update-member-role');
        Route::post('groups/{groupId}/toggle-visibility', [CourseGroupController::class, 'toggleVisibility'])->name('groups.toggle-visibility');
        Route::post('groups/{groupId}/toggle-active', [CourseGroupController::class, 'toggleActive'])->name('groups.toggle-active');
        Route::post('groups/{groupId}/relink-members', [CourseGroupController::class, 'relinkMembers'])->name('groups.relink-members');
        Route::post('courses/{course}/groups/{group}/relink-members', [CourseGroupController::class, 'relinkMembers'])->name('courses.groups.relink-members');

        // Group Membership Requests routes
        Route::get('courses/{courseId}/groups/{groupId}/membership-requests', [CourseGroupController::class, 'membershipRequests'])->name('courses.groups.membership-requests');
        Route::get('courses/{courseId}/groups/{groupId}/membership-requests/{requestId}', [CourseGroupController::class, 'showMembershipRequest'])->name('courses.groups.membership-requests.show');
        Route::post('courses/{courseId}/groups/{groupId}/membership-requests/{requestId}/approve', [CourseGroupController::class, 'approveRequest'])->name('courses.groups.membership-requests.approve');
        Route::post('courses/{courseId}/groups/{groupId}/membership-requests/{requestId}/reject', [CourseGroupController::class, 'rejectRequest'])->name('courses.groups.membership-requests.reject');
        Route::get('courses/{courseId}/groups/{groupId}/membership-requests/{requestId}/receipt', [CourseGroupController::class, 'membershipRequestReceipt'])->name('courses.groups.membership-requests.receipt');
        Route::delete('courses/{courseId}/groups/{groupId}/membership-requests/{requestId}/delete', [CourseGroupController::class, 'deleteRequest'])->name('courses.groups.membership-requests.delete');
        Route::delete('courses/{courseId}/groups/{groupId}/membership-requests/delete-multiple', [CourseGroupController::class, 'deleteMultipleRequests'])->name('courses.groups.membership-requests.delete-multiple');
        Route::post('courses/{courseId}/groups/{groupId}/membership-requests/approve-multiple', [CourseGroupController::class, 'approveMultipleRequests'])->name('courses.groups.membership-requests.approve-multiple');
        Route::post('courses/{courseId}/groups/{groupId}/membership-requests/approve-all', [CourseGroupController::class, 'approveAllPendingRequests'])->name('courses.groups.membership-requests.approve-all');
        Route::post('courses/{courseId}/groups/{groupId}/membership-requests/preview-whatsapp-invite', [CourseGroupController::class, 'previewMembershipWhatsAppInvite'])->name('courses.groups.membership-requests.preview-whatsapp-invite');
        Route::post('courses/{courseId}/groups/{groupId}/membership-requests/send-whatsapp-invite', [CourseGroupController::class, 'sendMembershipWhatsAppInvite'])->name('courses.groups.membership-requests.send-whatsapp-invite');
        Route::post('courses/{courseId}/groups/{groupId}/membership-requests/preview-telegram-invite', [CourseGroupController::class, 'previewMembershipTelegramInvite'])->name('courses.groups.membership-requests.preview-telegram-invite');
        Route::post('courses/{courseId}/groups/{groupId}/membership-requests/send-telegram-invite', [CourseGroupController::class, 'sendMembershipTelegramInvite'])->name('courses.groups.membership-requests.send-telegram-invite');
        Route::post('courses/{courseId}/groups/{groupId}/membership-requests/preview-email-invite', [CourseGroupController::class, 'previewMembershipEmailInvite'])->name('courses.groups.membership-requests.preview-email-invite');
        Route::post('courses/{courseId}/groups/{groupId}/membership-requests/send-email-invite', [CourseGroupController::class, 'sendMembershipEmailInvite'])->name('courses.groups.membership-requests.send-email-invite');

        // General management routes (all courses)
        Route::get('all-enrollments', [CourseEnrollmentController::class, 'allEnrollments'])->name('enrollments.all');
        Route::post('enrollments/{enrollmentId}/approve', [CourseEnrollmentController::class, 'approve'])->name('enrollments.approve');
        Route::post('enrollments/{enrollmentId}/reject', [CourseEnrollmentController::class, 'reject'])->name('enrollments.reject');

        // Group Registration Routes
        Route::prefix('group-registrations')->name('admin.group-registrations.')->group(function () {
            Route::get('/', [GroupRegistrationController::class, 'index'])->name('index');
            Route::get('/whatsapp-report', [GroupRegistrationController::class, 'whatsappReport'])->name('whatsapp-report');
            Route::get('/{registration}/receipt', [GroupRegistrationController::class, 'receipt'])
                ->middleware('signed')
                ->name('receipt');
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
        Route::resource('assignments', AssignmentController::class);
        Route::post('assignments/{id}/toggle-publish', [AssignmentController::class, 'togglePublish'])->name('assignments.toggle-publish');
        Route::get('assignments/course/{courseId}/lessons', [AssignmentController::class, 'getLessons'])->name('assignments.get-lessons');
        Route::get('assignments/course/{courseId}/groups', [AssignmentController::class, 'getCourseGroups'])->name('assignments.get-groups');
        Route::post('assignments/{id}/delete-attachment', [AssignmentController::class, 'deleteAttachment'])->name('assignments.delete-attachment');
        Route::post('submissions/{submissionId}/grade', [AssignmentController::class, 'gradeSubmission'])->name('submissions.grade');
        Route::post('submissions/{submissionId}/grant-resubmission', [AssignmentController::class, 'grantResubmission'])->name('submissions.grant-resubmission');

        // ========== Quizzes Routes ==========

        // Quizzes Management
        Route::resource('quizzes', QuizController::class);
        Route::post('quizzes/{id}/toggle-publish', [QuizController::class, 'togglePublish'])->name('quizzes.toggle-publish');
        Route::get('quizzes/course/{courseId}/lessons', [QuizController::class, 'getLessons'])->name('quizzes.get-lessons');
        Route::post('quizzes/{id}/recalculate-score', [QuizController::class, 'recalculateScore'])->name('quizzes.recalculate-score');
        Route::post('quizzes/{id}/reconcile-attempts', [QuizController::class, 'reconcileAttempts'])->name('quizzes.reconcile-attempts');
        Route::post('quizzes/{id}/abandon-in-progress-attempts', [QuizController::class, 'abandonInProgressAttempts'])->name('quizzes.abandon-in-progress-attempts');
        Route::post('quizzes/{id}/reset-all-attempts', [QuizController::class, 'resetAllAttempts'])->name('quizzes.reset-all-attempts');

        // Quiz Questions Management
        Route::get('quizzes/{id}/manage-questions', [QuizController::class, 'manageQuestions'])->name('quizzes.manage-questions');
        Route::get('quizzes/{id}/import-questions', [QuizController::class, 'importQuestions'])->name('quizzes.import-questions');
        Route::post('quizzes/{id}/import-questions', [QuizController::class, 'importQuestionsBulk'])->name('quizzes.import-questions.bulk');
        Route::post('quizzes/{id}/add-question', [QuizController::class, 'addQuestion'])->name('quizzes.add-question');
        Route::delete('quizzes/{id}/remove-question/{questionId}', [QuizController::class, 'removeQuestion'])->name('quizzes.remove-question');
        Route::post('quizzes/{id}/remove-multiple-questions', [QuizController::class, 'removeMultipleQuestions'])->name('quizzes.remove-multiple-questions');
        Route::post('quizzes/{id}/reorder-questions', [QuizController::class, 'reorderQuestions'])->name('quizzes.reorder-questions');
        Route::post('quizzes/{id}/attach-question-pool', [QuizController::class, 'attachQuestionPool'])->name('quizzes.attach-question-pool');
        Route::delete('quizzes/{id}/detach-question-pool/{quizQuestionId}', [QuizController::class, 'detachQuestionPool'])->name('quizzes.detach-question-pool');

        // Quiz AI question generation (review flow + auto-attach to quiz)
        Route::prefix('quizzes/{quiz}')->group(function () {
            Route::get('ai-generate', [QuestionBankAiGenerationController::class, 'createForQuiz'])->name('quizzes.ai-generate.create');
            Route::post('ai-generate', [QuestionBankAiGenerationController::class, 'storeForQuiz'])->name('quizzes.ai-generate.store');
            Route::get('ai-generate/{generation}/review', [QuestionBankAiGenerationController::class, 'reviewForQuiz'])->name('quizzes.ai-generate.review');
            Route::post('ai-generate/{generation}/save-all', [QuestionBankAiGenerationController::class, 'saveAllForQuiz'])->name('quizzes.ai-generate.save-all');
            Route::post('ai-generate/{generation}/save-selected', [QuestionBankAiGenerationController::class, 'saveSelectedForQuiz'])->name('quizzes.ai-generate.save-selected');
            Route::post('ai-generate/{generation}/save-one/{index}', [QuestionBankAiGenerationController::class, 'saveOneForQuiz'])->name('quizzes.ai-generate.save-one');
            Route::post('ai-generate/{generation}/regenerate', [QuestionBankAiGenerationController::class, 'regenerateForQuiz'])->name('quizzes.ai-generate.regenerate');
        });

        // Random Pool Quizzes (separate admin section)
        Route::get('random-pool-quizzes', [\App\Http\Controllers\Admin\RandomPoolQuizController::class, 'index'])->name('random-pool-quizzes.index');
        Route::get('random-pool-quizzes/create', [\App\Http\Controllers\Admin\RandomPoolQuizController::class, 'create'])->name('random-pool-quizzes.create');
        Route::post('random-pool-quizzes', [\App\Http\Controllers\Admin\RandomPoolQuizController::class, 'store'])->name('random-pool-quizzes.store');
        Route::get('random-pool-quizzes/{id}', [\App\Http\Controllers\Admin\RandomPoolQuizController::class, 'show'])->name('random-pool-quizzes.show');
        Route::get('random-pool-quizzes/{id}/edit', [\App\Http\Controllers\Admin\RandomPoolQuizController::class, 'edit'])->name('random-pool-quizzes.edit');
        Route::put('random-pool-quizzes/{id}', [\App\Http\Controllers\Admin\RandomPoolQuizController::class, 'update'])->name('random-pool-quizzes.update');
        Route::delete('random-pool-quizzes/{id}', [\App\Http\Controllers\Admin\RandomPoolQuizController::class, 'destroy'])->name('random-pool-quizzes.destroy');
        Route::post('random-pool-quizzes/{id}/toggle-publish', [\App\Http\Controllers\Admin\RandomPoolQuizController::class, 'togglePublish'])->name('random-pool-quizzes.toggle-publish');
        Route::get('random-pool-quizzes/{id}/manage-questions', [\App\Http\Controllers\Admin\RandomPoolQuizController::class, 'manageQuestions'])->name('random-pool-quizzes.manage-questions');
        Route::post('random-pool-quizzes/{id}/attach-question-pool', [\App\Http\Controllers\Admin\RandomPoolQuizController::class, 'attachQuestionPool'])->name('random-pool-quizzes.attach-question-pool');
        Route::delete('random-pool-quizzes/{id}/detach-question-pool/{quizQuestionId}', [\App\Http\Controllers\Admin\RandomPoolQuizController::class, 'detachQuestionPool'])->name('random-pool-quizzes.detach-question-pool');

        // Quiz preview (admin test-taking — separate from student routes)
        Route::post('quizzes/{id}/preview/start', [QuizPreviewController::class, 'start'])->name('quizzes.preview.start');
        Route::get('quizzes/preview/{attemptId}/take', [QuizPreviewController::class, 'take'])->name('quizzes.preview.take');
        Route::post('quizzes/preview/{attemptId}/save-answer', [QuizPreviewController::class, 'saveAnswer'])->name('quizzes.preview.save-answer');
        Route::post('quizzes/preview/{attemptId}/submit', [QuizPreviewController::class, 'submit'])->name('quizzes.preview.submit');
        Route::get('quizzes/preview/{attemptId}/review', [QuizPreviewController::class, 'review'])->name('quizzes.preview.review');

        // Question Bank Management — AI generation (must be before question-bank resource)
        Route::get('question-bank/ai-generate', [QuestionBankAiGenerationController::class, 'create'])->name('question-bank.ai-generate.create');
        Route::post('question-bank/ai-generate', [QuestionBankAiGenerationController::class, 'store'])->name('question-bank.ai-generate.store');
        Route::get('question-bank/ai-generate/{generation}/review', [QuestionBankAiGenerationController::class, 'review'])->name('question-bank.ai-generate.review');
        Route::post('question-bank/ai-generate/{generation}/save-all', [QuestionBankAiGenerationController::class, 'saveAll'])->name('question-bank.ai-generate.save-all');
        Route::post('question-bank/ai-generate/{generation}/save-selected', [QuestionBankAiGenerationController::class, 'saveSelected'])->name('question-bank.ai-generate.save-selected');
        Route::post('question-bank/ai-generate/{generation}/save-one/{index}', [QuestionBankAiGenerationController::class, 'saveOne'])->name('question-bank.ai-generate.save-one');
        Route::post('question-bank/ai-generate/{generation}/regenerate', [QuestionBankAiGenerationController::class, 'regenerate'])->name('question-bank.ai-generate.regenerate');

        Route::get('question-bank/create/{type}', [QuestionBankController::class, 'createByType'])->name('question-bank.create.type');
        Route::resource('question-bank', QuestionBankController::class);
        Route::post('question-bank/{id}/duplicate', [QuestionBankController::class, 'duplicate'])->name('question-bank.duplicate');
        Route::get('question-bank/{id}/preview', [QuestionBankController::class, 'preview'])->name('question-bank.preview');
        Route::get('question-bank/course/{courseId}/questions', [QuestionBankController::class, 'getQuestionsByCourse'])->name('question-bank.by-course');
        Route::get('question-bank/type/{typeId}/questions', [QuestionBankController::class, 'getQuestionsByType'])->name('question-bank.by-type');
        Route::post('question-bank/bulk-action', [QuestionBankController::class, 'bulkAction'])->name('question-bank.bulk-action');
        Route::post('question-bank/delete-multiple', [QuestionBankController::class, 'destroyMultiple'])->name('question-bank.delete-multiple');
        Route::post('question-bank/delete-all', [QuestionBankController::class, 'destroyAll'])->name('question-bank.delete-all');

        // Excel Import/Export
        Route::get('question-bank/import/excel', [QuestionBankController::class, 'showImportForm'])->name('question-bank.import.excel');
        Route::post('question-bank/import/preview', [QuestionBankController::class, 'previewImport'])->name('question-bank.import.preview');
        Route::post('question-bank/import/process', [QuestionBankController::class, 'processImport'])->name('question-bank.import.process');
        Route::get('question-bank/export/template', [QuestionBankController::class, 'downloadTemplate'])->name('question-bank.export.template');
        Route::get('question-bank/export/excel', [QuestionBankExportController::class, 'exportExcel'])->name('question-bank.export.excel');
        Route::get('question-bank/export/type/{format}', [QuestionBankExportController::class, 'selectType'])->name('question-bank.export.type.select');
        Route::get('question-bank/export/type/{format}/{type}', [QuestionBankExportController::class, 'exportByType'])->name('question-bank.export.type.download');

        // Type-specific Import (Excel + JSON)
        Route::prefix('question-bank/import/type')->name('question-bank.import.type.')->group(function () {
            Route::get('{format}', [QuestionBankTypeImportController::class, 'selectType'])->name('select');
            Route::get('{format}/{type}', [QuestionBankTypeImportController::class, 'showImportForm'])->name('show');
            Route::get('{format}/{type}/template', [QuestionBankTypeImportController::class, 'downloadTemplate'])->name('template');
            Route::post('{format}/{type}/preview', [QuestionBankTypeImportController::class, 'previewImport'])->name('preview');
            Route::post('{format}/{type}/process', [QuestionBankTypeImportController::class, 'processImport'])->name('process');
        });

        // Question Pools Management
        Route::resource('question-pools', QuestionPoolController::class);
        Route::post('question-pools/{id}/duplicate', [QuestionPoolController::class, 'duplicate'])->name('question-pools.duplicate');
        Route::post('question-pools/{id}/add-question', [QuestionPoolController::class, 'addQuestion'])->name('question-pools.add-question');
        Route::delete('question-pools/{id}/remove-question/{itemId}', [QuestionPoolController::class, 'removeQuestion'])->name('question-pools.remove-question');
        Route::post('question-pools/{id}/update-order', [QuestionPoolController::class, 'updateOrder'])->name('question-pools.update-order');
        Route::post('question-pools/{id}/generate-questions', [QuestionPoolController::class, 'generateQuestions'])->name('question-pools.generate-questions');
        Route::get('question-pools/{id}/statistics', [QuestionPoolController::class, 'getStatistics'])->name('question-pools.statistics');

        // Quiz Grading
        Route::prefix('grading')->name('grading.')->group(function () {
            Route::get('/', [QuizGradingController::class, 'index'])->name('index');
            Route::get('/{attemptId}/report', [QuizGradingController::class, 'attemptReport'])->name('attempt-report');
            Route::get('/{attemptId}', [QuizGradingController::class, 'show'])->name('show');
            Route::post('/responses/{responseId}/grade', [QuizGradingController::class, 'gradeResponse'])->name('grade-response');
            Route::post('/bulk-grade', [QuizGradingController::class, 'gradeBulk'])->name('bulk-grade');
            Route::post('/{attemptId}/complete', [QuizGradingController::class, 'completeGrading'])->name('complete');
            Route::post('/{attemptId}/regrade', [QuizGradingController::class, 'regradeAttempt'])->name('regrade');
            Route::get('/quiz/{quizId}/stats', [QuizGradingController::class, 'getQuizStats'])->name('quiz-stats');
            Route::post('/export-report', [QuizGradingController::class, 'exportReport'])->name('export-report');
        });

        // Quiz Analytics
        Route::prefix('quiz-analytics')->name('quiz-analytics.')->group(function () {
            Route::get('/', [QuizAnalyticsController::class, 'index'])->name('index');
            Route::get('/quiz/{quizId}', [QuizAnalyticsController::class, 'quiz'])->name('quiz');
            Route::get('/student/{studentId}', [QuizAnalyticsController::class, 'student'])->name('student');
            Route::get('/course/{courseId}', [QuizAnalyticsController::class, 'course'])->name('course');
            Route::post('/compare', [QuizAnalyticsController::class, 'compare'])->name('compare');
            Route::post('/export', [QuizAnalyticsController::class, 'export'])->name('export');
        });

        // ========== Question Modules Routes ==========

        // Question Modules Management
        Route::resource('question-modules', QuestionModuleController::class);
        Route::get('question-modules/{id}/manage-questions', [QuestionModuleController::class, 'manageQuestions'])->name('question-modules.manage-questions');
        Route::get('question-modules/{id}/import-questions', [QuestionModuleController::class, 'importQuestions'])->name('question-modules.import-questions');
        Route::post('question-modules/{id}/add-question', [QuestionModuleController::class, 'addQuestion'])->name('question-modules.add-question');
        Route::delete('question-modules/{id}/remove-question/{questionId}', [QuestionModuleController::class, 'removeQuestion'])->name('question-modules.remove-question');
        Route::put('question-modules/{id}/update-question-settings/{questionId}', [QuestionModuleController::class, 'updateQuestionSettings'])->name('question-modules.update-question-settings');
        Route::post('question-modules/{id}/reorder-questions', [QuestionModuleController::class, 'reorderQuestions'])->name('question-modules.reorder-questions');
        Route::post('question-modules/{id}/toggle-publish', [QuestionModuleController::class, 'togglePublish'])->name('question-modules.toggle-publish');
        Route::post('question-modules/{id}/toggle-visibility', [QuestionModuleController::class, 'toggleVisibility'])->name('question-modules.toggle-visibility');

        // Question Module Grading
        Route::prefix('question-module-grading')->name('admin.question-module-grading.')->group(function () {
            Route::get('/', [QuestionModuleGradingController::class, 'index'])->name('index');
            Route::get('/{attemptId}', [QuestionModuleGradingController::class, 'show'])->name('show');
            Route::post('/responses/{responseId}/grade', [QuestionModuleGradingController::class, 'gradeResponse'])->name('grade-response');
            Route::post('/bulk-grade', [QuestionModuleGradingController::class, 'gradeBulk'])->name('bulk-grade');
            Route::post('/{attemptId}/complete', [QuestionModuleGradingController::class, 'completeGrading'])->name('complete');
        });

        // ========== Programming Challenges Routes ==========

        Route::resource('programming-challenges', ProgrammingChallengeController::class)->except(['show']);
        Route::get('programming-challenges/{id}/languages', [ProgrammingChallengeController::class, 'manageLanguages'])->name('programming-challenges.manage-languages');
        Route::put('programming-challenges/{id}/languages', [ProgrammingChallengeController::class, 'updateLanguages'])->name('programming-challenges.update-languages');
        Route::get('programming-challenges/{id}/starter', [ProgrammingChallengeController::class, 'manageStarter'])->name('programming-challenges.manage-starter');
        Route::put('programming-challenges/{id}/starter', [ProgrammingChallengeController::class, 'updateStarter'])->name('programming-challenges.update-starter');
        Route::get('programming-challenges/{id}/test-cases', [ProgrammingChallengeController::class, 'manageTestCases'])->name('programming-challenges.manage-test-cases');
        Route::put('programming-challenges/{id}/test-cases', [ProgrammingChallengeController::class, 'updateTestCases'])->name('programming-challenges.update-test-cases');
        Route::get('programming-challenges/{id}/attempts', [ProgrammingChallengeController::class, 'attempts'])->name('programming-challenges.attempts');

        // ========== Lesson Simulators ==========
        Route::prefix('lesson-simulators')->name('admin.lesson-simulators.')->group(function () {
            Route::resource('categories', SimulatorCategoryController::class)->except(['show']);

            Route::get('/ai/create', [LessonSimulatorAiController::class, 'create'])->name('ai.create');
            Route::post('/generate-bundle', [LessonSimulatorAiController::class, 'generateSync'])->name('generate-bundle');
            Route::post('/refine-bundle', [LessonSimulatorAiController::class, 'refineBundle'])->name('refine-bundle');
            Route::post('/ai/store', [LessonSimulatorAiController::class, 'storeAsync'])->name('ai.store');
            Route::get('/ai/{lessonSimulator}/review', [LessonSimulatorAiController::class, 'review'])->name('ai.review');
            Route::get('/ai/{lessonSimulator}/status', [LessonSimulatorAiController::class, 'status'])->name('ai.status');
            Route::post('/ai/{lessonSimulator}/regenerate', [LessonSimulatorAiController::class, 'regenerate'])->name('ai.regenerate');

            Route::post('/preview-bundle', [LessonSimulatorController::class, 'previewBundle'])->name('preview-bundle');
            Route::get('/global-assets', [LessonSimulatorController::class, 'globalAssets'])->name('global-assets');
            Route::put('/global-assets', [LessonSimulatorController::class, 'updateGlobalAssets'])->name('global-assets.update');
            Route::get('/', [LessonSimulatorController::class, 'index'])->name('index');
            Route::get('/create', [LessonSimulatorController::class, 'create'])->name('create');
            Route::post('/', [LessonSimulatorController::class, 'store'])->name('store');
            Route::get('/{lessonSimulator}/preview', [LessonSimulatorController::class, 'preview'])->name('preview');
            Route::get('/{lessonSimulator}/play', [LessonSimulatorController::class, 'playDocument'])->name('play-document');
            Route::get('/{lessonSimulator}/assets/{file}', [LessonSimulatorController::class, 'playAsset'])
                ->where('file', 'page.css|simulator.js')
                ->name('play-asset');
            Route::get('/{lessonSimulator}/edit', [LessonSimulatorController::class, 'edit'])->name('edit');
            Route::put('/{lessonSimulator}', [LessonSimulatorController::class, 'update'])->name('update');
            Route::delete('/{lessonSimulator}', [LessonSimulatorController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('challenge-grading')->name('admin.challenge-grading.')->group(function () {
            Route::get('/', [ChallengeGradingController::class, 'index'])->name('index');
            Route::post('/live-preview', [ChallengeGradingController::class, 'storeLivePreview'])->name('live-preview.store');
            Route::get('/live-preview/{token}', [ChallengeGradingController::class, 'showLivePreview'])->name('live-preview.show');
            Route::get('/{attemptId}', [ChallengeGradingController::class, 'show'])->name('show');
            Route::post('/{attemptId}/grade', [ChallengeGradingController::class, 'grade'])->name('grade');
        });

        // ========== Project Challenges Routes ==========

        Route::prefix('project-challenges')->name('admin.project-challenges.')->group(function () {
            Route::get('/', [ProjectChallengeController::class, 'index'])->name('index');
            Route::get('/create', [ProjectChallengeController::class, 'create'])->name('create');
            Route::post('/', [ProjectChallengeController::class, 'store'])->name('store');
            Route::get('/search-students', [ProjectTeamController::class, 'searchStudents'])->name('search-students');
            Route::get('/{id}/edit', [ProjectChallengeController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ProjectChallengeController::class, 'update'])->name('update');
            Route::delete('/{id}', [ProjectChallengeController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/publish', [ProjectChallengeController::class, 'publish'])->name('publish');
            Route::get('/{id}/stages', [ProjectChallengeController::class, 'manageStages'])->name('manage-stages');
            Route::put('/{id}/stages', [ProjectChallengeController::class, 'updateStages'])->name('update-stages');
            Route::get('/{id}/teams', [ProjectChallengeController::class, 'manageTeams'])->name('manage-teams');
            Route::post('/{challengeId}/teams', [ProjectTeamController::class, 'store'])->name('teams.store');
            Route::get('/{challengeId}/teams/{teamId}', [ProjectTeamController::class, 'show'])->name('teams.show');
            Route::put('/{challengeId}/teams/{teamId}', [ProjectTeamController::class, 'update'])->name('teams.update');
            Route::post('/{challengeId}/teams/{teamId}/members', [ProjectTeamController::class, 'addMember'])->name('teams.members.store');
            Route::delete('/{challengeId}/teams/{teamId}/members/{userId}', [ProjectTeamController::class, 'removeMember'])->name('teams.members.destroy');
            Route::put('/{challengeId}/teams/{teamId}/members/{userId}/role', [ProjectTeamController::class, 'updateMemberRole'])->name('teams.members.update-role');
            Route::post('/{challengeId}/teams/{teamId}/stages/{stageId}/unlock', [ProjectTeamController::class, 'unlockStage'])->name('teams.stages.unlock');
            Route::post('/{challengeId}/join-requests/{requestId}/approve', [ProjectChallengeController::class, 'approveJoinRequest'])->name('approve-join-request');
            Route::post('/{challengeId}/join-requests/{requestId}/reject', [ProjectChallengeController::class, 'rejectJoinRequest'])->name('reject-join-request');
            Route::post('/{challengeId}/teams/{teamId}/activate', [ProjectChallengeController::class, 'activateTeam'])->name('activate-team');
        });

        Route::prefix('project-grading')->name('admin.project-grading.')->group(function () {
            Route::get('/', [ProjectGradingController::class, 'index'])->name('index');
            Route::get('/{submissionId}', [ProjectGradingController::class, 'show'])->name('show');
            Route::post('/{submissionId}/grade', [ProjectGradingController::class, 'grade'])->name('grade');
        });

        // ========== Gamification Routes ==========

        Route::prefix('gamification')->name('admin.gamification.')->group(function () {
            // Dashboard
            Route::get('/', [GamificationDashboardController::class, 'index'])->name('dashboard');
            Route::get('/analytics', [GamificationDashboardController::class, 'analytics'])->name('analytics');
            Route::post('/recalculate-all', [GamificationDashboardController::class, 'recalculateAll'])->name('recalculate-all');

            // Points Management
            Route::prefix('points')->name('points.')->group(function () {
                Route::get('/', [AdminPointsController::class, 'index'])->name('index');
                Route::get('/course-groups', [AdminPointsController::class, 'courseGroups'])->name('course-groups');
                Route::get('/create', [AdminPointsController::class, 'create'])->name('create');
                Route::get('/search-students', [AdminPointsController::class, 'searchStudents'])->name('search-students');
                Route::post('/preview-recipients', [AdminPointsController::class, 'previewRecipients'])->name('preview-recipients');
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
                Route::get('/award-manual', [AdminBadgeController::class, 'awardForm'])->name('award.form');
                Route::get('/award-manual/preview', [AdminBadgeController::class, 'previewTargets'])->name('award.preview');
                Route::get('/award-manual/students', [AdminBadgeController::class, 'searchStudents'])->name('award.students');
                Route::post('/award-manual', [AdminBadgeController::class, 'awardManual'])->name('award.store');
                Route::post('/award', [AdminBadgeController::class, 'awardToUser'])->name('award');
                Route::get('/course-groups', [AdminBadgeReportController::class, 'courseGroups'])->name('course-groups');
                Route::prefix('reports')->name('reports.')->group(function () {
                    Route::get('/distribution', [AdminBadgeReportController::class, 'distribution'])->name('distribution');
                    Route::get('/students', [AdminBadgeReportController::class, 'students'])->name('students');
                    Route::get('/students/{user}', [AdminBadgeReportController::class, 'studentDetail'])->name('students.detail');
                });
                Route::get('/statistics/overview', [AdminBadgeReportController::class, 'statistics'])->name('statistics');
                Route::get('/{badge}/award', [AdminBadgeController::class, 'awardFormForBadge'])->name('award.badge');
                Route::get('/{badge}', [AdminBadgeController::class, 'show'])->name('show');
                Route::get('/{badge}/edit', [AdminBadgeController::class, 'edit'])->name('edit');
                Route::put('/{badge}', [AdminBadgeController::class, 'update'])->name('update');
                Route::delete('/{badge}', [AdminBadgeController::class, 'destroy'])->name('destroy');
                Route::post('/{badge}/toggle-active', [AdminBadgeController::class, 'toggleActive'])->name('toggle-active');
            });

            // Achievements Management
            Route::prefix('achievements')->name('achievements.')->group(function () {
                Route::get('/', [AdminAchievementController::class, 'index'])->name('index');
                Route::get('/create', [AdminAchievementController::class, 'create'])->name('create');
                Route::post('/', [AdminAchievementController::class, 'store'])->name('store');
                Route::post('/recalculate-all', [AdminAchievementController::class, 'recalculateAll'])->name('recalculate-all');
                Route::get('/statistics/overview', [AdminAchievementController::class, 'statistics'])->name('statistics');
                Route::get('/{achievement}', [AdminAchievementController::class, 'show'])->name('show');
                Route::get('/{achievement}/edit', [AdminAchievementController::class, 'edit'])->name('edit');
                Route::put('/{achievement}', [AdminAchievementController::class, 'update'])->name('update');
                Route::delete('/{achievement}', [AdminAchievementController::class, 'destroy'])->name('destroy');
                Route::post('/{achievement}/toggle-active', [AdminAchievementController::class, 'toggleActive'])->name('toggle-active');
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
            Route::get('/', [NotificationManagementController::class, 'index'])->name('index');
            Route::get('/history', [NotificationManagementController::class, 'history'])->name('history');
            Route::get('/statistics', [NotificationManagementController::class, 'statistics'])->name('statistics');

            // Send notifications
            Route::post('/send-to-student', [NotificationManagementController::class, 'sendToStudent'])->name('send.student');
            Route::post('/send-to-course', [NotificationManagementController::class, 'sendToCourse'])->name('send.course');
            Route::post('/send-to-group', [NotificationManagementController::class, 'sendToGroup'])->name('send.group');
            Route::post('/send-broadcast', [NotificationManagementController::class, 'sendBroadcast'])->name('send.broadcast');

            // API endpoints
            Route::get('/api/students', [NotificationManagementController::class, 'getStudents'])->name('api.students');
            Route::get('/api/courses', [NotificationManagementController::class, 'getCourses'])->name('api.courses');
            Route::get('/api/groups', [NotificationManagementController::class, 'getGroups'])->name('api.groups');
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
            Route::get('/', [EmailSettingController::class, 'index'])->name('index');
            Route::get('/create', [EmailSettingController::class, 'create'])->name('create');
            Route::post('/', [EmailSettingController::class, 'store'])->name('store');
            Route::post('/test-temp', [EmailSettingController::class, 'testTemp'])->name('test-temp');
            Route::post('/test-connection-temp', [EmailSettingController::class, 'testConnectionTemp'])->name('test-connection-temp');
            Route::get('/{emailSetting}/edit', [EmailSettingController::class, 'edit'])->name('edit');
            Route::put('/{emailSetting}', [EmailSettingController::class, 'update'])->name('update');
            Route::delete('/{emailSetting}', [EmailSettingController::class, 'destroy'])->name('destroy');
            Route::post('/{emailSetting}/activate', [EmailSettingController::class, 'activate'])->name('activate');
            Route::post('/{emailSetting}/test-connection', [EmailSettingController::class, 'testConnection'])->name('test-connection');
            Route::post('/{emailSetting}/test', [EmailSettingController::class, 'test'])->name('test');
            Route::get('/provider/{provider}', [EmailSettingController::class, 'getProviderPreset'])->name('provider.preset');
        });

        Route::get('settings/password-reset-message', [PasswordResetMessageSettingsController::class, 'edit'])->name('admin.settings.password-reset-message.edit');
        Route::put('settings/password-reset-message', [PasswordResetMessageSettingsController::class, 'update'])->name('admin.settings.password-reset-message.update');
        Route::post('settings/password-reset-message/restore-defaults', [PasswordResetMessageSettingsController::class, 'restoreDefaults'])->name('admin.settings.password-reset-message.restore-defaults');

        Route::get('settings/account-created-message', [AccountCreatedMessageSettingsController::class, 'edit'])->name('admin.settings.account-created-message.edit');
        Route::put('settings/account-created-message', [AccountCreatedMessageSettingsController::class, 'update'])->name('admin.settings.account-created-message.update');
        Route::post('settings/account-created-message/restore-defaults', [AccountCreatedMessageSettingsController::class, 'restoreDefaults'])->name('admin.settings.account-created-message.restore-defaults');

        Route::get('settings/payment-whatsapp-message', [PaymentWhatsAppMessageSettingsController::class, 'edit'])->name('admin.settings.payment-whatsapp-message.edit');
        Route::put('settings/payment-whatsapp-message', [PaymentWhatsAppMessageSettingsController::class, 'update'])->name('admin.settings.payment-whatsapp-message.update');
        Route::post('settings/payment-whatsapp-message/restore-defaults', [PaymentWhatsAppMessageSettingsController::class, 'restoreDefaults'])->name('admin.settings.payment-whatsapp-message.restore-defaults');

        Route::get('settings/phone-otp', [PhoneOtpSettingsController::class, 'edit'])->name('admin.settings.phone-otp.edit');
        Route::put('settings/phone-otp', [PhoneOtpSettingsController::class, 'update'])->name('admin.settings.phone-otp.update');
        Route::post('settings/phone-otp/test-send', [PhoneOtpSettingsController::class, 'testSend'])->name('admin.settings.phone-otp.test-send');
        Route::post('settings/phone-otp/restore-defaults', [PhoneOtpSettingsController::class, 'restoreDefaults'])->name('admin.settings.phone-otp.restore-defaults');

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

        Route::prefix('bulk-emails')->name('admin.bulk-emails.')->group(function () {
            Route::get('/', [BulkEmailController::class, 'index'])->name('index');
            Route::get('/create', [BulkEmailController::class, 'create'])->name('create');
            Route::post('/preview-count', [BulkEmailController::class, 'previewCount'])->name('preview-count');
            Route::post('/preview-recipients', [BulkEmailController::class, 'previewRecipients'])->name('preview-recipients');
            Route::post('/preview-content', [BulkEmailController::class, 'previewContent'])->name('preview-content');
            Route::post('/', [BulkEmailController::class, 'store'])->name('store');

            Route::prefix('settings')->name('settings.')->group(function () {
                Route::get('/', [BulkEmailSettingsController::class, 'index'])->name('index');
                Route::put('/', [BulkEmailSettingsController::class, 'update'])->name('update');
            });

            Route::get('/{campaign}', [BulkEmailController::class, 'show'])->name('show');
            Route::post('/{campaign}/retry-failed', [BulkEmailController::class, 'retryFailed'])->name('retry-failed');
        });

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

        Route::prefix('student-profile-cards')->name('admin.student-profile-cards.')->group(function () {
            Route::get('/', [StudentProfileCardController::class, 'index'])->name('index');
            Route::get('/settings', [StudentProfileCardSettingController::class, 'edit'])->name('settings');
            Route::post('/settings', [StudentProfileCardSettingController::class, 'update'])->name('settings.update');
            Route::post('/{studentProfileCard}/toggle-admin-enabled', [StudentProfileCardController::class, 'toggleAdminEnabled'])->name('toggle-admin-enabled');
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
            Route::get('/', [FrontendReviewController::class, 'index'])->name('index');
            Route::get('/{review}', [FrontendReviewController::class, 'show'])->name('show');
            Route::post('/{review}/approve', [FrontendReviewController::class, 'approve'])->name('approve');
            Route::post('/{review}/reject', [FrontendReviewController::class, 'reject'])->name('reject');
            Route::post('/{review}/toggle-featured', [FrontendReviewController::class, 'toggleFeatured'])->name('toggle-featured');
            Route::delete('/{review}', [FrontendReviewController::class, 'destroy'])->name('destroy');
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
                Route::get('/', [WebhookTokenController::class, 'index'])->name('index');
                Route::get('/create', [WebhookTokenController::class, 'create'])->name('create');
                Route::post('/', [WebhookTokenController::class, 'store'])->name('store');
                Route::get('/{token}', [WebhookTokenController::class, 'show'])->name('show');
                Route::get('/{token}/edit', [WebhookTokenController::class, 'edit'])->name('edit');
                Route::put('/{token}', [WebhookTokenController::class, 'update'])->name('update');
                Route::delete('/{token}', [WebhookTokenController::class, 'destroy'])->name('destroy');
                Route::post('/{token}/toggle', [WebhookTokenController::class, 'toggleActive'])->name('toggle');
                Route::get('/generate/token', [WebhookTokenController::class, 'generateToken'])->name('generate');
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
                ->except([])
                ->names([
                    'index' => 'categories.index',
                    'create' => 'categories.create',
                    'store' => 'categories.store',
                    'show' => 'categories.show',
                    'edit' => 'categories.edit',
                    'update' => 'categories.update',
                    'destroy' => 'categories.destroy',
                ]);
            Route::post('categories/{documentation_category}/toggle-active', [DocumentationCategoryController::class, 'toggleActive'])
                ->name('categories.toggle-active');

            Route::get('ai-pages/create', [AIDocumentationPageController::class, 'create'])->name('ai-pages.create');
            Route::get('ai-pages/improve', [AIDocumentationPageController::class, 'improve'])->name('ai-pages.improve');
            Route::get('ai-pages/enhance', [AIDocumentationPageController::class, 'enhance'])->name('ai-pages.enhance');
            Route::post('ai-pages/generate', [AIDocumentationPageController::class, 'generate'])->name('ai-pages.generate');
            Route::post('ai-pages/refine', [AIDocumentationPageController::class, 'refine'])->name('ai-pages.refine');
            Route::get('ai-pages/jobs/{uuid}', [AIDocumentationPageController::class, 'jobStatus'])->name('ai-pages.jobs.show');
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
            Route::post('pages/{documentation_page}/documentation-links', [DocumentationPageDocumentationLinkController::class, 'store'])
                ->name('pages.documentation-links.store');
            Route::get('pages/{documentation_page}/pdf', [DocumentationPageController::class, 'exportPdf'])
                ->name('pages.pdf');
            Route::get('pages/{documentation_page}/ai-source', [DocumentationPageController::class, 'aiSourceJson'])
                ->name('pages.ai-source');
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

        // ========== Student Gifts Routes ==========
        Route::prefix('gifts')->name('admin.gifts.')->group(function () {
            Route::get('/', [StudentGiftController::class, 'index'])->name('index');
            Route::get('/create', [StudentGiftController::class, 'create'])->name('create');
            Route::post('/', [StudentGiftController::class, 'store'])->name('store');
            Route::post('/preview-recipients', [StudentGiftController::class, 'previewRecipients'])->name('preview-recipients');
            Route::get('/search-students', [StudentGiftController::class, 'searchStudents'])->name('search-students');
            Route::get('/{gift}', [StudentGiftController::class, 'show'])->name('show');
            Route::get('/{gift}/edit', [StudentGiftController::class, 'edit'])->name('edit');
            Route::put('/{gift}', [StudentGiftController::class, 'update'])->name('update');
            Route::delete('/{gift}', [StudentGiftController::class, 'destroy'])->name('destroy');
            Route::post('/{gift}/grant', [StudentGiftController::class, 'grant'])->name('grant');
            Route::post('/{gift}/regrant', [StudentGiftController::class, 'regrant'])->name('regrant');
            Route::post('/{gift}/revoke', [StudentGiftController::class, 'revoke'])->name('revoke');
        });

        // ========== Contact Settings Routes ==========
        Route::get('contact-settings/edit', [ContactSettingController::class, 'edit'])->name('admin.contact-settings.edit');
        Route::put('contact-settings', [ContactSettingController::class, 'update'])->name('admin.contact-settings.update');

        // ========== Google Settings Routes ==========
        Route::get('google-settings/edit', [GoogleSettingController::class, 'edit'])->name('admin.google-settings.edit');
        Route::put('google-settings', [GoogleSettingController::class, 'update'])->name('admin.google-settings.update');
        Route::post('google-settings/test-api', [GoogleSettingController::class, 'testApi'])->name('admin.google-settings.test-api');
        Route::get('marketing-analytics', [MarketingAnalyticsController::class, 'index'])->name('admin.marketing-analytics.index');
        Route::get('marketing-analytics/data', [MarketingAnalyticsController::class, 'data'])->name('admin.marketing-analytics.data');
        Route::get('meta-pixel-settings/edit', [MetaPixelSettingController::class, 'edit'])->name('admin.meta-pixel-settings.edit');
        Route::put('meta-pixel-settings', [MetaPixelSettingController::class, 'update'])->name('admin.meta-pixel-settings.update');
        Route::post('meta-pixel-settings/test-capi', [MetaPixelSettingController::class, 'testCapi'])->name('admin.meta-pixel-settings.test-capi');

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
            Route::get('question-creation/create', [AIQuestionCreationController::class, 'create'])->name('question-creation.create');
            Route::post('question-creation', [AIQuestionCreationController::class, 'store'])->name('question-creation.store');

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
            Route::get('/', [WhatsAppSettingsController::class, 'index'])->name('index');
            Route::post('/', [WhatsAppSettingsController::class, 'update'])->name('update');
            Route::post('/test-connection', [WhatsAppSettingsController::class, 'testConnection'])->name('test-connection');
            Route::post('/auto-reply/preview', [WhatsAppSettingsController::class, 'autoReplyPreview'])->name('auto-reply.preview');
            Route::post('/auto-reply/test-send', [WhatsAppSettingsController::class, 'autoReplyTestSend'])->name('auto-reply.test-send');
            Route::get('/queue-worker/status', [WhatsAppSettingsController::class, 'queueWorkerStatus'])->name('queue-worker.status');
            Route::post('/queue-worker/start', [WhatsAppSettingsController::class, 'queueWorkerStart'])->name('queue-worker.start');
            Route::post('/queue-worker/stop', [WhatsAppSettingsController::class, 'queueWorkerStop'])->name('queue-worker.stop');
        });

        Route::prefix('whatsapp-messages')->name('admin.whatsapp-messages.')->group(function () {
            Route::get('/', [WhatsAppMessageController::class, 'index'])->name('index');
            Route::get('/send', [WhatsAppMessageController::class, 'create'])->name('create');
            Route::get('/search-students', [WhatsAppMessageController::class, 'searchStudents'])->name('search-students');
            Route::post('/send', [WhatsAppMessageController::class, 'send'])->name('send');
            Route::post('/broadcast', [WhatsAppMessageController::class, 'broadcast'])->name('broadcast');
            Route::get('/broadcast/students-count', [WhatsAppMessageController::class, 'getStudentsCount'])->name('broadcast.students-count');
            Route::get('/broadcasts', [WhatsAppMessageController::class, 'broadcastsIndex'])->name('broadcasts.index');
            Route::get('/broadcasts/{broadcast}', [WhatsAppMessageController::class, 'showBroadcast'])->name('broadcasts.show');
            Route::post('/{message}/retry', [WhatsAppMessageController::class, 'retry'])->name('retry');
            Route::get('/{message}', [WhatsAppMessageController::class, 'show'])->name('show');
        });

        // WhatsApp Message Templates (قوالب رسائل واتساب)
        Route::prefix('whatsapp-templates')->name('admin.whatsapp-templates.')->group(function () {
            Route::get('/', [WhatsAppMessageTemplateController::class, 'index'])->name('index');
            Route::get('/create', [WhatsAppMessageTemplateController::class, 'create'])->name('create');
            Route::post('/', [WhatsAppMessageTemplateController::class, 'store'])->name('store');
            Route::get('/{whatsapp_template}/edit', [WhatsAppMessageTemplateController::class, 'edit'])->name('edit');
            Route::put('/{whatsapp_template}', [WhatsAppMessageTemplateController::class, 'update'])->name('update');
            Route::post('/{whatsapp_template}/test/preview', [WhatsAppMessageTemplateController::class, 'previewTest'])->name('test.preview');
            Route::post('/{whatsapp_template}/test/send', [WhatsAppMessageTemplateController::class, 'sendTest'])->name('test.send');
            Route::delete('/{whatsapp_template}', [WhatsAppMessageTemplateController::class, 'destroy'])->name('destroy');
            Route::get('/{whatsapp_template}/json', [WhatsAppMessageTemplateController::class, 'getTemplate'])->name('get');
        });

        // Flaxxa WAPI (مزود wapi.flaxxa.com)
        Route::prefix('flaxxa-wapi')->name('admin.flaxxa-wapi.')->group(function () {
            Route::get('/', function () {
                return redirect()->route('admin.flaxxa-wapi.messages.index');
            })->name('home');
            Route::get('settings', [FlaxxaWapiSettingsController::class, 'index'])->name('settings.index');
            Route::post('settings', [FlaxxaWapiSettingsController::class, 'update'])->name('settings.update');
            Route::post('settings/test-connection', [FlaxxaWapiSettingsController::class, 'testConnection'])->name('settings.test-connection');
            Route::get('settings/phone-otp', fn () => redirect()->route('admin.settings.phone-otp.edit'))->name('settings.phone-otp');
            Route::get('messages', [FlaxxaWapiController::class, 'messagesIndex'])->name('messages.index');
            Route::get('messages/{wapiMessage}', [FlaxxaWapiController::class, 'messageShow'])->name('messages.show');
            Route::post('messages/{wapiMessage}/check-status', [FlaxxaWapiController::class, 'messageCheckStatus'])->name('messages.check-status');
            Route::get('send/message', [FlaxxaWapiController::class, 'sendMessageForm'])->name('send.message');
            Route::post('send/message', [FlaxxaWapiController::class, 'sendMessage'])->name('send.message.store');
            Route::get('send/template', [FlaxxaWapiController::class, 'sendTemplateForm'])->name('send.template');
            Route::post('send/template', [FlaxxaWapiController::class, 'sendTemplate'])->name('send.template.store');
            Route::get('send/campaign', [FlaxxaWapiController::class, 'sendCampaignForm'])->name('send.campaign');
            Route::post('send/campaign', [FlaxxaWapiController::class, 'sendCampaign'])->name('send.campaign.store');
            Route::prefix('automation')->name('automation.')->group(function () {
                Route::get('/', [WapiAutomationRuleController::class, 'index'])->name('index');
                Route::get('/create', [WapiAutomationRuleController::class, 'create'])->name('create');
                Route::post('/', [WapiAutomationRuleController::class, 'store'])->name('store');
                Route::get('/{wapiAutomationRule}/edit', [WapiAutomationRuleController::class, 'edit'])->name('edit');
                Route::put('/{wapiAutomationRule}', [WapiAutomationRuleController::class, 'update'])->name('update');
                Route::delete('/{wapiAutomationRule}', [WapiAutomationRuleController::class, 'destroy'])->name('destroy');
                Route::post('/{wapiAutomationRule}/test', [WapiAutomationRuleController::class, 'testSend'])->name('test');
            });
            Route::post('templates/sync-from-provider', [WapiTemplateController::class, 'syncFromProvider'])->name('templates.sync');
            Route::resource('templates', WapiTemplateController::class)->except(['show'])->parameters(['templates' => 'wapiTemplate']);
        });

        // WhatsApp Web Routes
        Route::prefix('whatsapp-web')->name('admin.whatsapp-web.')->group(function () {
            Route::get('/connect', [WhatsAppWebController::class, 'connect'])->name('connect');
            Route::post('/start-connection', [WhatsAppWebController::class, 'startConnection'])->name('start-connection');
            Route::get('/qr/{sessionId}', [WhatsAppWebController::class, 'getQrCode'])->name('qr');
            Route::get('/status/{sessionId}', [WhatsAppWebController::class, 'getStatus'])->name('status');
            Route::post('/disconnect/{sessionId}', [WhatsAppWebController::class, 'disconnect'])->name('disconnect');
        });

        // WhatsApp Web Settings Routes
        Route::prefix('whatsapp-web-settings')->name('admin.whatsapp-web-settings.')->group(function () {
            Route::get('/', [WhatsAppWebSettingsController::class, 'index'])->name('index');
            Route::post('/', [WhatsAppWebSettingsController::class, 'update'])->name('update');
            Route::post('/test-connection', [WhatsAppWebSettingsController::class, 'testConnection'])->name('test-connection');
        });

        // Telegram
        Route::prefix('telegram')->name('admin.telegram.')->group(function () {
            Route::get('/', fn () => redirect()->route('admin.telegram.settings.index'))->name('home');
            Route::get('settings', [TelegramSettingsController::class, 'index'])->name('settings.index');
            Route::post('settings', [TelegramSettingsController::class, 'update'])->name('settings.update');
            Route::post('settings/test-connection', [TelegramSettingsController::class, 'testConnection'])->name('settings.test-connection');
            Route::post('settings/test-bridge', [TelegramSettingsController::class, 'testBridge'])->name('settings.test-bridge');
            Route::post('settings/activate-webhook', [TelegramSettingsController::class, 'activateWebhook'])->name('settings.activate-webhook');

            Route::get('send', [TelegramMessageController::class, 'sendForm'])->name('send');
            Route::post('send', [TelegramMessageController::class, 'send'])->name('send.store');
            Route::get('broadcast', [TelegramMessageController::class, 'broadcastForm'])->name('broadcast');
            Route::post('broadcast', [TelegramMessageController::class, 'broadcast'])->name('broadcast.store');
            Route::get('broadcast/students-count', [TelegramMessageController::class, 'studentsCount'])->name('broadcast.students-count');
            Route::get('broadcasts', [TelegramMessageController::class, 'broadcastsIndex'])->name('broadcasts.index');
            Route::get('broadcasts/{broadcast}', [TelegramMessageController::class, 'showBroadcast'])->name('broadcasts.show');

            Route::resource('templates', TelegramMessageTemplateController::class)->except(['show'])->parameters(['templates' => 'telegram_template']);

            Route::get('groups/link', [TelegramGroupController::class, 'linkForm'])->name('groups.link');
            Route::post('groups/link', [TelegramGroupController::class, 'linkStore'])->name('groups.link.store');
            Route::post('groups/prepare-link', [TelegramGroupController::class, 'prepareLink'])->name('groups.prepare-link');
            Route::post('groups/auto-create', [TelegramGroupController::class, 'autoCreate'])->name('groups.auto-create');
            Route::get('groups/post', [TelegramGroupController::class, 'postForm'])->name('groups.post');
            Route::post('groups/post', [TelegramGroupController::class, 'post'])->name('groups.post.store');
            Route::get('groups/compare', [TelegramGroupController::class, 'compareForm'])->name('groups.compare');
            Route::post('groups/compare', [TelegramGroupController::class, 'compareRun'])->name('groups.compare.run');
        });

        // Evolution API
        Route::prefix('evolution-api')->name('admin.evolution-api.')->group(function () {
            Route::get('/', fn () => redirect()->route('admin.evolution-api.settings.index'))->name('home');
            Route::get('settings', [EvolutionSettingsController::class, 'index'])->name('settings.index');
            Route::post('settings', [EvolutionSettingsController::class, 'update'])->name('settings.update');
            Route::post('settings/test-connection', [EvolutionSettingsController::class, 'testConnection'])->name('settings.test-connection');

            Route::get('instances', [EvolutionInstanceController::class, 'index'])->name('instances.index');
            Route::post('instances/test-connection', [EvolutionInstanceController::class, 'testConnection'])->name('instances.test-connection');
            Route::post('instances/connection', [EvolutionInstanceController::class, 'saveConnection'])->name('instances.connection');
            Route::post('instances/register-manual', [EvolutionInstanceController::class, 'registerManual'])->name('instances.register-manual');
            Route::post('instances/register-bulk', [EvolutionInstanceController::class, 'registerBulk'])->name('instances.register-bulk');
            Route::post('instances', [EvolutionInstanceController::class, 'store'])->name('instances.store');
            Route::post('instances/link', [EvolutionInstanceController::class, 'link'])->name('instances.link');
            Route::post('instances/sync', [EvolutionInstanceController::class, 'sync'])->name('instances.sync');
            Route::get('instances/{instanceName}/connect', [EvolutionInstanceController::class, 'connect'])->name('instances.connect');
            Route::get('instances/{instanceName}/qr', [EvolutionInstanceController::class, 'fetchQr'])->name('instances.qr');
            Route::get('instances/{instanceName}/status', [EvolutionInstanceController::class, 'status'])->name('instances.status');
            Route::post('instances/{instanceName}/restart', [EvolutionInstanceController::class, 'restart'])->name('instances.restart');
            Route::post('instances/{instanceName}/toggle-rotation', [EvolutionInstanceController::class, 'toggleRotation'])->name('instances.toggle-rotation');
            Route::post('instances/{instanceName}/set-default', [EvolutionInstanceController::class, 'setDefault'])->name('instances.set-default');
            Route::post('instances/{instanceName}/logout', [EvolutionInstanceController::class, 'logout'])->name('instances.logout');
            Route::delete('instances/{instanceName}', [EvolutionInstanceController::class, 'destroy'])->name('instances.destroy');

            Route::get('send/text', [EvolutionSendController::class, 'textForm'])->name('send.text');
            Route::post('send/text', [EvolutionSendController::class, 'sendText'])->name('send.text.store');
            Route::get('send/media', [EvolutionSendController::class, 'mediaForm'])->name('send.media');
            Route::post('send/media', [EvolutionSendController::class, 'sendMedia'])->name('send.media.store');
            Route::get('send/{type}', [EvolutionSendController::class, 'advancedForm'])->name('send.advanced');
            Route::post('send/{type}', [EvolutionSendController::class, 'sendAdvanced'])->name('send.advanced.store');

            Route::get('groups', [EvolutionGroupsController::class, 'index'])->name('groups.index');
            Route::get('groups/show', [EvolutionGroupsController::class, 'show'])->name('groups.show');
            Route::get('groups/members', [EvolutionGroupsController::class, 'members'])->name('groups.members');
            Route::get('groups/compare', [EvolutionGroupCompareController::class, 'index'])->name('groups.compare');
            Route::get('groups/compare/export', [EvolutionGroupCompareController::class, 'export'])->name('groups.compare.export');
            Route::post('groups/compare/message-missing', [EvolutionGroupCompareController::class, 'messageMissing'])->name('groups.compare.message-missing');
            Route::get('groups/compare/campaigns', [EvolutionGroupCompareController::class, 'campaigns'])->name('groups.compare.campaigns');
            Route::get('groups/compare/campaigns/{broadcast}', [EvolutionGroupCompareController::class, 'showCampaign'])->name('groups.compare.campaigns.show');
            Route::post('groups/send', [EvolutionGroupsController::class, 'sendMessage'])->name('groups.send');
            Route::post('groups/send-member', [EvolutionGroupsController::class, 'sendMemberMessage'])->name('groups.send-member');

            Route::get('contacts', [EvolutionContactsController::class, 'index'])->name('contacts.index');
            Route::post('contacts/sync', [EvolutionContactsController::class, 'sync'])->name('contacts.sync');

            Route::get('chats', [EvolutionChatsController::class, 'index'])->name('chats.index');

            Route::get('messages', [EvolutionSendController::class, 'messagesIndex'])->name('messages.index');

            Route::get('webhook', [EvolutionWebhookAdminController::class, 'index'])->name('webhook.index');
            Route::post('webhook/url', [EvolutionWebhookAdminController::class, 'saveUrl'])->name('webhook.save-url');
            Route::post('webhook/activate', [EvolutionWebhookAdminController::class, 'activate'])->name('webhook.activate');
        });

        // ========== Activity Log (Audit Trail) ==========
        Route::prefix('activity-logs')->name('admin.activity-logs.')->group(function () {
            Route::get('/', [ActivityLogController::class, 'index'])->name('index');
            Route::get('/{activity}', [ActivityLogController::class, 'show'])->name('show');
        });

        // ========== User Sessions Routes ==========
        Route::prefix('user-sessions')->name('admin.user-sessions.')->group(function () {
            Route::get('/', [UserSessionController::class, 'index'])->name('index');
            Route::get('/active', [UserSessionController::class, 'activeSessions'])->name('active');
            Route::get('/statistics', [UserSessionController::class, 'statistics'])->name('statistics');
            Route::get('/user/{userId}', [UserSessionController::class, 'userSessions'])->name('user');
            Route::get('/{id}', [UserSessionController::class, 'show'])->name('show');
            Route::post('/bulk-delete', [UserSessionController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/delete-all', [UserSessionController::class, 'deleteAll'])->name('delete-all');
            Route::post('/delete-completed', [UserSessionController::class, 'deleteCompleted'])->name('delete-completed');
            Route::post('/delete-disconnected', [UserSessionController::class, 'deleteDisconnected'])->name('delete-disconnected');
        });

        // ========== Admin header notifications (database) ==========
        Route::prefix('header-notifications')->name('admin.header-notifications.')->group(function () {
            Route::get('/', [AdminHeaderNotificationController::class, 'index'])->name('index');
            Route::post('/mark-all-read', [AdminHeaderNotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::post('/{id}/mark-read', [AdminHeaderNotificationController::class, 'markAsRead'])->name('mark-read');
        });

        // ========== User Devices Routes ==========
        Route::prefix('user-devices')->name('admin.user-devices.')->group(function () {
            Route::get('/', [UserDeviceController::class, 'index'])->name('index');
            Route::get('/security-settings', [DeviceSecuritySettingsController::class, 'edit'])->name('security-settings');
            Route::post('/security-settings', [DeviceSecuritySettingsController::class, 'update'])->name('security-settings.update');
            Route::get('/user/{userId}', [UserDeviceController::class, 'userDevices'])->name('user');
            Route::get('/{id}', [UserDeviceController::class, 'show'])->name('show');
            Route::post('/{id}/block', [UserDeviceController::class, 'block'])->name('block');
            Route::post('/{id}/unblock', [UserDeviceController::class, 'unblock'])->name('unblock');
            Route::post('/{id}/trust', [UserDeviceController::class, 'trust'])->name('trust');
            Route::post('/{id}/untrust', [UserDeviceController::class, 'untrust'])->name('untrust');
            Route::put('/{id}/device-name', [UserDeviceController::class, 'updateDeviceName'])->name('update-name');
            Route::post('/bulk-delete', [UserDeviceController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/delete-all', [UserDeviceController::class, 'deleteAll'])->name('delete-all');
            Route::post('/delete-old', [UserDeviceController::class, 'deleteOld'])->name('delete-old');
            Route::post('/delete-inactive', [UserDeviceController::class, 'deleteInactive'])->name('delete-inactive');
        });

        // ===============================================
        // نظام النسخ الاحتياطي
        // ===============================================
        Route::prefix('backups')->name('backups.')->group(function () {
            Route::get('/', [BackupController::class, 'index'])->name('index');
            Route::get('/create', [BackupController::class, 'create'])->name('create');
            Route::post('/', [BackupController::class, 'store'])->name('store');
            Route::get('/{backup}', [BackupController::class, 'show'])->name('show');
            Route::get('/{backup}/status', [BackupController::class, 'status'])->name('status');
            Route::post('/{backup}/run', [BackupController::class, 'run'])->name('run');
            Route::post('/{backup}/restore', [BackupController::class, 'restore'])->name('restore');
            Route::get('/{backup}/download', [BackupController::class, 'download'])->name('download');
            Route::delete('/{backup}', [BackupController::class, 'destroy'])->name('destroy');
            Route::get('/stats/overview', [BackupController::class, 'stats'])->name('stats');
        });

        Route::prefix('backup-schedules')->name('backup-schedules.')->group(function () {
            Route::get('/', [BackupScheduleController::class, 'index'])->name('index');
            Route::get('/create', [BackupScheduleController::class, 'create'])->name('create');
            Route::post('/', [BackupScheduleController::class, 'store'])->name('store');
            Route::get('/{schedule}', [BackupScheduleController::class, 'show'])->name('show');
            Route::get('/{schedule}/edit', [BackupScheduleController::class, 'edit'])->name('edit');
            Route::put('/{schedule}', [BackupScheduleController::class, 'update'])->name('update');
            Route::delete('/{schedule}', [BackupScheduleController::class, 'destroy'])->name('destroy');
            Route::post('/{schedule}/execute', [BackupScheduleController::class, 'execute'])->name('execute');
            Route::post('/{schedule}/toggle-active', [BackupScheduleController::class, 'toggleActive'])->name('toggle-active');
        });

        Route::prefix('backup-storage')->name('backup-storage.')->group(function () {
            Route::get('/', [BackupStorageController::class, 'index'])->name('index');
            Route::get('/create', [BackupStorageController::class, 'create'])->name('create');
            Route::post('/', [BackupStorageController::class, 'store'])->name('store');
            Route::get('/{config}/edit', [BackupStorageController::class, 'edit'])->name('edit');
            Route::put('/{config}', [BackupStorageController::class, 'update'])->name('update');
            Route::delete('/{config}', [BackupStorageController::class, 'destroy'])->name('destroy');
            Route::post('/{config}/test', [BackupStorageController::class, 'test'])->name('test');
            Route::post('/test-connection', [BackupStorageController::class, 'testConnection'])->name('test-connection');
            Route::get('/analytics', [BackupStorageAnalyticsController::class, 'index'])->name('analytics');
        });

        // App Storage
        Route::prefix('app-storage')->name('app-storage.')->group(function () {
            Route::get('/configs', [AppStorageController::class, 'index'])->name('configs.index');
            Route::get('/configs/create', [AppStorageController::class, 'create'])->name('configs.create');
            Route::post('/configs', [AppStorageController::class, 'store'])->name('configs.store');
            Route::get('/configs/{config}', [AppStorageController::class, 'show'])->name('configs.show');
            Route::get('/configs/{config}/edit', [AppStorageController::class, 'edit'])->name('configs.edit');
            Route::put('/configs/{config}', [AppStorageController::class, 'update'])->name('configs.update');
            Route::delete('/configs/{config}', [AppStorageController::class, 'destroy'])->name('configs.destroy');
            Route::post('/configs/test-connection', [AppStorageController::class, 'testConnection'])->name('configs.test-connection');
            Route::post('/configs/{config}/test', [AppStorageController::class, 'test'])->name('configs.test');
            Route::get('/analytics', [AppStorageAnalyticsController::class, 'index'])->name('analytics');

            Route::get('/inventory', [StorageInventoryController::class, 'index'])->name('inventory.index');
            Route::post('/inventory/scan', [StorageInventoryController::class, 'scan'])->name('inventory.scan');
            Route::post('/inventory/migrate', [StorageInventoryController::class, 'migrate'])->name('inventory.migrate');
            Route::post('/inventory/verify', [StorageInventoryController::class, 'verify'])->name('inventory.verify');
            Route::post('/inventory/cleanup-local', [StorageInventoryController::class, 'cleanupLocal'])->name('inventory.cleanup-local');
            Route::get('/inventory/local-files', [StorageInventoryController::class, 'localFiles'])->name('inventory.local-files');
            Route::post('/inventory/local-files/delete', [StorageInventoryController::class, 'deleteLocalFiles'])->name('inventory.local-files.delete');
            Route::get('/inventory/cloud-files', [StorageInventoryController::class, 'cloudFiles'])->name('inventory.cloud-files');
            Route::get('/inventory/browse-local', [StorageInventoryController::class, 'browseLocal'])->name('inventory.browse-local');
            Route::post('/inventory/refresh-capacity', [StorageInventoryController::class, 'refreshCapacity'])->name('inventory.refresh-capacity');
            Route::get('/inventory/export', [StorageInventoryController::class, 'export'])->name('inventory.export');
            Route::get('/inventory/progress', [StorageInventoryController::class, 'progress'])->name('inventory.progress');
        });

        Route::prefix('storage-disk-mappings')->name('storage-disk-mappings.')->group(function () {
            Route::get('/', [StorageDiskMappingController::class, 'index'])->name('index');
            Route::get('/create', [StorageDiskMappingController::class, 'create'])->name('create');
            Route::post('/', [StorageDiskMappingController::class, 'store'])->name('store');
            Route::get('/{mapping}', [StorageDiskMappingController::class, 'show'])->name('show');
            Route::get('/{mapping}/edit', [StorageDiskMappingController::class, 'edit'])->name('edit');
            Route::put('/{mapping}', [StorageDiskMappingController::class, 'update'])->name('update');
            Route::delete('/{mapping}', [StorageDiskMappingController::class, 'destroy'])->name('destroy');
        });

        // Weekly Student Reports
        Route::prefix('weekly-reports')->name('admin.weekly-reports.')->group(function () {
            Route::get('/schedules/list', [AdminStudentWeeklyReportScheduleController::class, 'index'])->name('schedules.index');
            Route::post('/schedules', [AdminStudentWeeklyReportScheduleController::class, 'store'])->name('schedules.store');
            Route::post('/schedules/{schedule}/toggle', [AdminStudentWeeklyReportScheduleController::class, 'toggle'])->name('schedules.toggle');
            Route::get('/groups-overview', [AdminStudentWeeklyReportController::class, 'groupsOverview'])->name('groups-overview');
            Route::get('/pending', [AdminStudentWeeklyReportController::class, 'pendingReports'])->name('pending');
            Route::get('/all', [AdminStudentWeeklyReportController::class, 'allReports'])->name('all');
            Route::get('/created/batch/edit', [AdminStudentWeeklyReportController::class, 'editCreatedBatch'])->name('created.batch.edit');
            Route::put('/created/batch', [AdminStudentWeeklyReportController::class, 'updateCreatedBatch'])->name('created.batch.update');
            Route::delete('/created/batch', [AdminStudentWeeklyReportController::class, 'destroyCreatedBatch'])->name('created.batch.destroy');
            Route::get('/created/batch', [AdminStudentWeeklyReportController::class, 'showCreatedBatch'])->name('created.batch');
            Route::get('/created', [AdminStudentWeeklyReportController::class, 'createdReports'])->name('created');

            Route::get('/', [AdminStudentWeeklyReportController::class, 'index'])->name('index');
            Route::get('/create', [AdminStudentWeeklyReportController::class, 'create'])->name('create');
            Route::post('/', [AdminStudentWeeklyReportController::class, 'store'])->name('store');
            Route::get('/{weeklyReport}', [AdminStudentWeeklyReportController::class, 'show'])->name('show');
            Route::put('/{weeklyReport}/feedback', [AdminStudentWeeklyReportController::class, 'feedback'])->name('feedback');
        });

        Route::prefix('database-info')->name('admin.database-info.')->group(function () {
            Route::get('/', [DatabaseInfoController::class, 'index'])->name('index');
            Route::post('/optimize/{table}', [DatabaseInfoController::class, 'optimize'])->name('optimize');
            Route::post('/analyze/{table}', [DatabaseInfoController::class, 'analyze'])->name('analyze');
        });

    });
