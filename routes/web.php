<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\PhoneCountryAjaxValidationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::post('/ajax/validate-phone-country', PhoneCountryAjaxValidationController::class)
    ->middleware('throttle:120,1')
    ->name('validate.phone-country');

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();

    // إذا كان أدمن → لوحة تحكم الأدمن
    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    // إذا كان طالب → لوحة تحكم الطالب
    if ($user->hasRole('student')) {
        return redirect()->route('student.dashboard');
    }

    // أي دور آخر أو بدون دور واضح → رجوع لصفحة تسجيل الدخول
    return redirect()->route('login');
})->middleware('auth');

Route::get('/dashboard', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();

    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('student')) {
        return redirect()->route('student.dashboard');
    }

    return redirect()->route('login');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'check.user.active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

// Impersonation routes
// Route للدخول باستخدام token (لا يحتاج auth لأنه سيسجل الدخول)
Route::get('/impersonate/{token}', [\App\Http\Controllers\Admin\ImpersonationController::class, 'loginWithToken'])
    ->name('impersonate.login');

// Route لإيقاف Impersonation - يجب أن يكون متاحاً حتى أثناء Impersonation
Route::middleware('auth')->group(function () {
    Route::post('/admin/stop-impersonate', [\App\Http\Controllers\Admin\ImpersonationController::class, 'stop'])
        ->name('admin.stop-impersonate');
});

// مسار toggle-status بدون middleware check.user.active
Route::middleware(['auth'])->group(function () {
    Route::post('users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
});

// مسار بديل للتجربة
Route::post('toggle-user-status/{id}', [UserController::class, 'toggleStatus'])->name('users.toggle-status-alt');

// Route لعرض صور الكورسات المصغرة (thumbnails) - يخدم من S3 أو التخزين المحلي
Route::get('/storage/courses/thumbnails/{filename}', function ($filename) {
    // أولاً: محاولة جلب من التخزين الديناميكي (S3)
    try {
        $storageHelper = app(\App\Services\Storage\StorageHelperService::class);
        $disk = $storageHelper->getDisk('course_thumbnails');
        $filePath = 'courses/thumbnails/' . $filename;
        
        if ($disk->exists($filePath)) {
            $content = $disk->get($filePath);
            $mimeType = $disk->mimeType($filePath) ?: 'image/png';
            
            return response($content, 200, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=31536000',
            ]);
        }
    } catch (\Exception $e) {
        // Fallback إلى التخزين المحلي
    }
    
    // Fallback: التخزين المحلي
    $path = storage_path('app/public/courses/thumbnails/'.$filename);

    if (! file_exists($path)) {
        abort(404, 'الصورة غير موجودة');
    }

    $mimeType = mime_content_type($path);
    if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'])) {
        abort(403, 'نوع الملف غير مسموح');
    }

    return response()->file($path, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('filename', '[a-zA-Z0-9._-]+')->name('course.thumbnail');

// Route لعرض صور الكورسات - يخدم من S3 أو التخزين المحلي
Route::get('/storage/courses/images/{filename}', function ($filename) {
    // أولاً: محاولة جلب من التخزين الديناميكي (S3)
    try {
        $storageHelper = app(\App\Services\Storage\StorageHelperService::class);
        $disk = $storageHelper->getDisk('course_thumbnails');
        $filePath = 'courses/images/' . $filename;
        
        if ($disk->exists($filePath)) {
            $content = $disk->get($filePath);
            $mimeType = $disk->mimeType($filePath) ?: 'image/png';
            
            return response($content, 200, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=31536000',
            ]);
        }
    } catch (\Exception $e) {
        // Fallback إلى التخزين المحلي
    }
    
    // Fallback: التخزين المحلي
    $path = storage_path('app/public/courses/images/'.$filename);

    if (! file_exists($path)) {
        abort(404, 'الصورة غير موجودة');
    }

    $mimeType = mime_content_type($path);
    if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'])) {
        abort(403, 'نوع الملف غير مسموح');
    }

    return response()->file($path, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('filename', '[a-zA-Z0-9._-]+')->name('course.image');

// Route لعرض صور المدونة - يخدم من S3 أو التخزين المحلي
Route::get('/storage/blog/images/{filename}', function ($filename) {
    // أولاً: محاولة جلب من التخزين الديناميكي (S3)
    try {
        $storageHelper = app(\App\Services\Storage\StorageHelperService::class);
        $disk = $storageHelper->getDisk('blog_images');
        $filePath = 'blog/images/' . $filename;
        
        if ($disk->exists($filePath)) {
            $content = $disk->get($filePath);
            $mimeType = $disk->mimeType($filePath) ?: 'image/png';
            
            return response($content, 200, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=31536000',
            ]);
        }
    } catch (\Exception $e) {
        // Fallback إلى التخزين المحلي
    }
    
    // Fallback: التخزين المحلي
    $path = storage_path('app/public/blog/images/'.$filename);

    if (! file_exists($path)) {
        abort(404, 'الصورة غير موجودة');
    }

    $mimeType = mime_content_type($path);
    if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'])) {
        abort(403, 'نوع الملف غير مسموح');
    }

    return response()->file($path, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('filename', '[a-zA-Z0-9._-]+')->name('blog.image');

// Session tracking routes
Route::middleware('auth')->group(function () {
    Route::post('/api/session/track', [\App\Http\Controllers\SessionActivityController::class, 'track'])->name('session.track');
    Route::post('/api/session/heartbeat', [\App\Http\Controllers\SessionActivityController::class, 'heartbeat'])->name('session.heartbeat');
});

require __DIR__.'/auth.php';
require __DIR__.'/student.php';
require __DIR__.'/admin.php';
require __DIR__.'/frontend.php';
require __DIR__.'/certificates.php';
