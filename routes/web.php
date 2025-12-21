<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;

Route::get('/', function () {
    if (!auth()->check()) {
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
    if (!auth()->check()) {
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



// مسار toggle-status بدون middleware check.user.active
Route::middleware(['auth'])->group(function () {
    Route::post('users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
});

// مسار بديل للتجربة
Route::post('toggle-user-status/{id}', [UserController::class, 'toggleStatus'])->name('users.toggle-status-alt');

// Route لعرض صور المدونة (حل بديل إذا لم يعمل storage link)
Route::get('/storage/blog/images/{filename}', function ($filename) {
    $path = storage_path('app/public/blog/images/' . $filename);
    
    if (!file_exists($path)) {
        abort(404, 'الصورة غير موجودة');
    }
    
    $mimeType = mime_content_type($path);
    if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'])) {
        abort(403, 'نوع الملف غير مسموح');
    }
    
    return response()->file($path, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('filename', '[a-zA-Z0-9._-]+')->name('blog.image');

// Route لعرض صور الكورسات (حل بديل إذا لم يعمل storage link)
Route::get('/storage/courses/images/{filename}', function ($filename) {
    $path = storage_path('app/public/courses/images/' . $filename);
    
    if (!file_exists($path)) {
        abort(404, 'الصورة غير موجودة');
    }
    
    $mimeType = mime_content_type($path);
    if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'])) {
        abort(403, 'نوع الملف غير مسموح');
    }
    
    return response()->file($path, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('filename', '[a-zA-Z0-9._-]+')->name('course.image');

require __DIR__.'/auth.php';
require __DIR__.'/student.php';
require __DIR__.'/admin.php';
require __DIR__.'/frontend.php';
require __DIR__.'/certificates.php';
