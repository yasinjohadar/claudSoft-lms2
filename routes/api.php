<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\N8nWebhookController;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use App\Http\Controllers\Api\WhatsAppWebWebhookController;
use App\Http\Controllers\Api\Student\AuthController as StudentAuthController;
use App\Http\Controllers\Api\Student\CourseController as StudentCourseController;
use App\Http\Controllers\Api\Student\CourseProgressApiController as StudentCourseProgressApiController;
use App\Http\Controllers\Api\Student\ExternalResourceApiController as StudentExternalResourceApiController;
use App\Http\Controllers\Api\Student\InvoiceController as StudentInvoiceApiController;
use App\Http\Controllers\Api\Student\NotificationController as StudentNotificationApiController;
use App\Http\Controllers\Api\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Api\Student\QuizApiController as StudentQuizApiController;

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
        Route::get('courses/{courseId}/certificate', [StudentCourseProgressApiController::class, 'certificate'])->name('courses.certificate');
        Route::get('courses/{courseId}/progress-report', [StudentCourseProgressApiController::class, 'exportReport'])->name('courses.progress-report');
        Route::get('external-resources/{resource}/open', [StudentExternalResourceApiController::class, 'open'])->name('external-resources.open');
    });

    Route::middleware(['log.student.api', 'auth:sanctum', 'role:student'])->group(function () {
        Route::get('profile', [StudentProfileController::class, 'show'])->name('profile.show');
        Route::put('profile', [StudentProfileController::class, 'update'])->name('profile.update');
        Route::get('nationalities', [StudentProfileController::class, 'nationalities'])->name('nationalities');

        Route::get('courses', [StudentCourseController::class, 'index'])->name('courses.index');
        Route::get('courses/{courseId}/progress', [StudentCourseProgressApiController::class, 'progress'])->name('courses.progress');
        Route::post('courses/{id}/enroll', [StudentCourseController::class, 'enroll'])->name('courses.enroll');
        Route::delete('courses/{id}/enroll', [StudentCourseController::class, 'unenroll'])->name('courses.unenroll');

        Route::get('external-resources', [StudentExternalResourceApiController::class, 'index'])->name('external-resources.index');

        Route::get('invoices', [StudentInvoiceApiController::class, 'invoices'])->name('invoices.index');
        Route::get('invoices/{id}', [StudentInvoiceApiController::class, 'invoice'])->name('invoices.show');
        Route::get('payments', [StudentInvoiceApiController::class, 'payments'])->name('payments.index');
        Route::get('payments/{id}', [StudentInvoiceApiController::class, 'payment'])->name('payments.show');

        Route::get('notifications/unread-count', [StudentNotificationApiController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::post('notifications/mark-all-read', [StudentNotificationApiController::class, 'markAllRead'])->name('notifications.mark-all-read');
        Route::get('notifications', [StudentNotificationApiController::class, 'index'])->name('notifications.index');
        Route::post('notifications/{id}/mark-read', [StudentNotificationApiController::class, 'markRead'])->name('notifications.mark-read');

        Route::prefix('quizzes')->name('quizzes.')->group(function () {
            Route::get('{id}/preview', [StudentQuizApiController::class, 'preview'])->name('preview');
            Route::post('{id}/start', [StudentQuizApiController::class, 'start'])->name('start');
            Route::get('attempts/{attempt}', [StudentQuizApiController::class, 'showAttempt'])->name('attempts.show');
            Route::post('attempts/{attempt}/answer', [StudentQuizApiController::class, 'saveAnswer'])->name('attempts.answer');
            Route::post('attempts/{attempt}/submit', [StudentQuizApiController::class, 'submit'])->name('attempts.submit');
        });
    });
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
});
