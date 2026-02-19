<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\N8nWebhookController;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use App\Http\Controllers\Api\WhatsAppWebWebhookController;
use App\Http\Controllers\Api\Student\AuthController as StudentAuthController;
use App\Http\Controllers\Api\Student\CourseController as StudentCourseController;
use App\Http\Controllers\Api\Student\ProfileController as StudentProfileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ========== Student API (للتطبيقات مثل Flutter) ==========
Route::prefix('student')->name('api.student.')->group(function () {
    // مصادقة (بدون توكن)
    Route::post('login', [StudentAuthController::class, 'login'])->name('login');
    Route::post('logout', [StudentAuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
    Route::get('me', [StudentAuthController::class, 'me'])->middleware('auth:sanctum')->name('me');

    // محمية بتوكن Sanctum + دور طالب فقط (مع تسجيل الطلبات للتشخيص)
    Route::middleware(['log.student.api', 'auth:sanctum', 'role:student'])->group(function () {
        // بروفايل الطالب الكامل (كل البيانات لعرضها في Flutter)
        Route::get('profile', [StudentProfileController::class, 'show'])->name('profile.show');
        Route::put('profile', [StudentProfileController::class, 'update'])->name('profile.update');
        Route::get('nationalities', [StudentProfileController::class, 'nationalities'])->name('nationalities');
        // كتالوج كل الكورسات المنشورة (للعرض في التطبيق حتى بدون تسجيل)
        Route::get('catalog', [StudentCourseController::class, 'catalog'])->name('catalog');
        // الكورسات المرتبطة بالطالب مع الأقسام والدروس/الفيديوهات
        Route::get('courses', [StudentCourseController::class, 'index'])->name('courses.index');
    });
});

// Webhook Routes (Public - no auth required, but signature verification)
Route::prefix('webhooks')->name('api.webhooks.')->group(function () {

    // Test endpoint (no authentication for testing)
    Route::get('/test', [WebhookController::class, 'test'])->name('test');

    // WPForms webhook endpoint
    Route::post('/wpforms', [WebhookController::class, 'wpforms'])
        ->middleware('webhook.verify:wpforms')
        ->name('wpforms');

    // n8n webhook endpoints
    Route::prefix('n8n')->name('n8n.')->group(function () {
        // Incoming webhook from n8n
        Route::post('/incoming', [N8nWebhookController::class, 'incoming'])
            ->middleware('webhook.verify:n8n')
            ->name('incoming');

        // Get available handlers documentation
        Route::get('/handlers', [N8nWebhookController::class, 'handlers'])
            ->name('handlers');

        // Get specific handler documentation
        Route::get('/handlers/{handlerType}', [N8nWebhookController::class, 'handlerDocs'])
            ->name('handler.docs');
    });

    // WhatsApp webhook endpoints (Meta Cloud API)
    Route::prefix('whatsapp')
        ->name('whatsapp.')
        ->middleware(['throttle:60,1'])
        ->group(function () {
            Route::get('/', [WhatsAppWebhookController::class, 'verify'])->name('verify');
            Route::post('/', [WhatsAppWebhookController::class, 'handle'])->name('handle');
        });

    // WhatsApp Web webhook endpoints (Node.js / whatsapp-web.js)
    Route::prefix('whatsapp-web')
        ->name('whatsapp-web.')
        ->middleware(['throttle:120,1'])
        ->group(function () {
            // استقبال الرسائل الواردة من خدمة الواتساب ويب
            Route::post('/incoming', [WhatsAppWebWebhookController::class, 'handleIncoming'])->name('incoming');
        });
});
