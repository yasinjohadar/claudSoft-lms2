<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\N8nWebhookController;

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
});
