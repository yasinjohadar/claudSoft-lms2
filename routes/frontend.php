<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\CourseController;
use App\Http\Controllers\Frontend\ReviewController;
use App\Http\Controllers\Frontend\StudentController;
use App\Http\Controllers\Frontend\BlogController;



Route::get('/', [HomeController::class, 'index'])->name('frontend.home');
Route::get('/courses', [CourseController::class, 'index'])->name('frontend.courses.index');
Route::get('/courses/{slug}', [CourseController::class, 'show'])->name('frontend.courses.show');
Route::get('/reviews', [ReviewController::class, 'index'])->name('frontend.reviews.index');
Route::get('/reviews/create', [ReviewController::class, 'create'])->name('frontend.reviews.create')->middleware('auth');
Route::post('/reviews', [ReviewController::class, 'store'])->name('frontend.reviews.store')->middleware('auth');
Route::get('/students', [StudentController::class, 'index'])->name('frontend.students.index');
Route::get('/students/{id}', [StudentController::class, 'show'])->name('frontend.students.show');
Route::get('/contact', [HomeController::class, 'contact'])->name('frontend.contact');
Route::post('/contact', [HomeController::class, 'sendContact'])->name('frontend.contact.send');

// Blog Routes
Route::prefix('blog')->name('frontend.blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/search', [BlogController::class, 'search'])->name('search');
    Route::get('/category/{slug}', [BlogController::class, 'category'])->name('category');
    Route::get('/tag/{slug}', [BlogController::class, 'tag'])->name('tag');
    Route::get('/{slug}', [BlogController::class, 'show'])->name('show');
});

// Sitemap Route
Route::get('/sitemap.xml', [\App\Http\Controllers\Frontend\SitemapController::class, 'index'])->name('frontend.sitemap');

// Robots.txt Route (dynamic)
Route::get('/robots.txt', function() {
    $content = "# robots.txt\n\n";
    $content .= "# Allow all search engines\n";
    $content .= "User-agent: *\n";
    $content .= "Allow: /\n";
    $content .= "Allow: /courses\n";
    $content .= "Allow: /blog\n\n";
    $content .= "# Disallow admin and student panels\n";
    $content .= "Disallow: /admin/\n";
    $content .= "Disallow: /student/\n";
    $content .= "Disallow: /api/\n\n";
    $content .= "# Disallow private files\n";
    $content .= "Disallow: /storage/private/\n\n";
    $content .= "# Sitemap location\n";
    $content .= "Sitemap: " . url('/sitemap.xml') . "\n";
    
    return response($content, 200)
        ->header('Content-Type', 'text/plain; charset=utf-8');
})->name('frontend.robots');
