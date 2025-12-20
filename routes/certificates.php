<?php

use App\Http\Controllers\Admin\CertificateController as AdminCertificateController;
use App\Http\Controllers\Admin\CertificateTemplateController;
use App\Http\Controllers\Student\CertificateController as StudentCertificateController;
use App\Http\Controllers\CertificateVerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Certificate Routes
|--------------------------------------------------------------------------
|
| ملف Routes منفصل لنظام الشهادات
| يمكن تضمينه في routes/web.php بإضافة:
| require __DIR__.'/certificates.php';
|
*/

// ========================================
// Admin Certificate Routes
// ========================================
Route::prefix('admin')
    ->middleware(['auth', 'role:admin'])
    ->name('admin.')
    ->group(function () {
        // Certificates Management
        Route::prefix('certificates')->name('certificates.')->group(function () {
            Route::get('/', [AdminCertificateController::class, 'index'])->name('index');
            Route::get('/create', [AdminCertificateController::class, 'create'])->name('create');
            Route::post('/', [AdminCertificateController::class, 'store'])->name('store');
            Route::get('/{certificate}', [AdminCertificateController::class, 'show'])->name('show');
            Route::get('/{certificate}/download', [AdminCertificateController::class, 'download'])->name('download');
            Route::post('/{certificate}/revoke', [AdminCertificateController::class, 'revoke'])->name('revoke');
            Route::post('/{certificate}/reissue', [AdminCertificateController::class, 'reissue'])->name('reissue');
            Route::delete('/{certificate}', [AdminCertificateController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-issue', [AdminCertificateController::class, 'bulkIssue'])->name('bulk-issue');
        });

        // Certificate Templates Management
        Route::prefix('certificate-templates')->name('certificate-templates.')->group(function () {
            Route::get('/', [CertificateTemplateController::class, 'index'])->name('index');
            Route::get('/create', [CertificateTemplateController::class, 'create'])->name('create');
            Route::post('/', [CertificateTemplateController::class, 'store'])->name('store');
            Route::get('/{certificateTemplate}', [CertificateTemplateController::class, 'show'])->name('show');
            Route::get('/{certificateTemplate}/edit', [CertificateTemplateController::class, 'edit'])->name('edit');
            Route::get('/{certificateTemplate}/preview', [CertificateTemplateController::class, 'preview'])->name('preview');
            Route::put('/{certificateTemplate}', [CertificateTemplateController::class, 'update'])->name('update');
            Route::delete('/{certificateTemplate}', [CertificateTemplateController::class, 'destroy'])->name('destroy');
            Route::post('/{certificateTemplate}/set-default', [CertificateTemplateController::class, 'setDefault'])->name('set-default');
        });
    });

// ========================================
// Student Certificate Routes
// ========================================
Route::prefix('student')
    ->middleware(['auth', 'role:student'])
    ->name('student.')
    ->group(function () {
        Route::prefix('certificates')->name('certificates.')->group(function () {
            Route::get('/', [StudentCertificateController::class, 'index'])->name('index');
            Route::get('/{certificate}', [StudentCertificateController::class, 'show'])->name('show');
            Route::get('/{certificate}/download', [StudentCertificateController::class, 'download'])->name('download');
        });
    });

// ========================================
// Public Certificate Verification Routes
// ========================================
Route::prefix('verify-certificate')->name('certificate.verify.')->group(function () {
    Route::get('/', [CertificateVerificationController::class, 'index'])->name('index');
    Route::post('/verify', [CertificateVerificationController::class, 'verify'])->name('verify');
    Route::get('/{code}', [CertificateVerificationController::class, 'show'])->name('show');
});
