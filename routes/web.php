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
    return serve_storage_image_response(
        ['course_thumbnails', 'public'],
        'courses/thumbnails/' . $filename,
        'courses/thumbnails/' . $filename
    );
})->where('filename', '[a-zA-Z0-9._-]+')->name('course.thumbnail');

// Route لعرض صور الكورسات - يخدم من S3 أو التخزين المحلي
Route::get('/storage/courses/images/{filename}', function ($filename) {
    return serve_storage_image_response(
        ['public', 'course_thumbnails'],
        'courses/images/' . $filename,
        'courses/images/' . $filename
    );
})->where('filename', '[a-zA-Z0-9._-]+')->name('course.image');

// Route لعرض صور المدونة - يخدم من S3 أو التخزين المحلي
Route::get('/storage/blog/images/{filename}', function ($filename) {
    return serve_storage_image_response(
        ['blog_images', 'public'],
        'blog/images/' . $filename,
        'blog/images/' . $filename
    );
})->where('filename', '[a-zA-Z0-9._-]+')->name('blog.image');

// Route لعرض صور الهدايا - يخدم من S3 أو التخزين المحلي
Route::get('/storage/gifts/images/{filename}', function ($filename) {
    return serve_storage_image_response(
        ['gift_images', 'public'],
        'gifts/images/' . $filename,
        'gifts/images/' . $filename
    );
})->where('filename', '[a-zA-Z0-9._-]+')->name('gift.image');

// Route لعرض صور الملف الشخصي للطلاب - يخدم من S3 أو التخزين المحلي
Route::get('/storage/profile-photos/{filename}', function ($filename) {
    return serve_storage_image_response(
        ['public'],
        'profile-photos/' . $filename,
        'profile-photos/' . $filename
    );
})->where('filename', '[a-zA-Z0-9._-]+')->name('profile.photo');

Route::get('/storage/users/photos/{filename}', function ($filename) {
    return serve_storage_image_response(
        ['public'],
        'users/photos/' . $filename,
        'users/photos/' . $filename
    );
})->where('filename', '[a-zA-Z0-9._-]+')->name('user.photo');

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
