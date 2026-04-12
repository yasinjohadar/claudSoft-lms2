<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\CourseController;
use App\Http\Controllers\Frontend\ReviewController;
use App\Http\Controllers\Frontend\StudentController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\GroupRegistrationController;
use App\Http\Controllers\Frontend\DocumentationController;



Route::get('/', [HomeController::class, 'index'])->name('frontend.home');
Route::get('/about', [HomeController::class, 'about'])->name('frontend.about');
Route::get('/courses', [CourseController::class, 'index'])->name('frontend.courses.index');
// تفاصيل الكورس للواجهة الجديدة (frontend2) — اسم الراوت ثابت ليتم تحديث الروابط تلقائياً
Route::get('/course/{slug}', [CourseController::class, 'show'])->name('frontend.courses.show');
Route::redirect('/courses/{slug}', '/course/{slug}', 301);

// Group Registration Routes
Route::prefix('group-registration')->name('frontend.group-registration.')->group(function () {
    Route::get('/{group}/create', [GroupRegistrationController::class, 'create'])->name('create');
    Route::post('/{group}/store', [GroupRegistrationController::class, 'store'])->name('store');
    Route::get('/{registration}/success', [GroupRegistrationController::class, 'success'])->name('success');
});
Route::get('/reviews', [ReviewController::class, 'index'])->name('frontend.reviews.index');
Route::get('/reviews/create', [ReviewController::class, 'create'])->name('frontend.reviews.create')->middleware('auth');
Route::post('/reviews', [ReviewController::class, 'store'])->name('frontend.reviews.store')->middleware('auth');
Route::get('/students', [StudentController::class, 'index'])->name('frontend.students.index');
Route::get('/students/{id}', [StudentController::class, 'show'])->name('frontend.students.show');
Route::get('/contact', [HomeController::class, 'contact'])->name('frontend.contact');
Route::post('/contact', [HomeController::class, 'sendContact'])->name('frontend.contact.send');

// Legacy /skills/* URLs -> /services/* (301)
Route::redirect('skills/{segment}', '/services/{segment}', 301)
    ->whereIn('segment', ['web', 'servers', 'security', 'mobile', 'devops', 'consultation']);

// Service pages (public offerings)
Route::prefix('services')->name('frontend.services.')->group(function () {
    Route::get('/web', [HomeController::class, 'skillWeb'])->name('web');
    Route::get('/servers', [HomeController::class, 'skillServers'])->name('servers');
    Route::get('/security', [HomeController::class, 'skillSecurity'])->name('security');
    Route::get('/mobile', [HomeController::class, 'skillMobile'])->name('mobile');
    Route::get('/devops', [HomeController::class, 'skillDevops'])->name('devops');
    Route::get('/consultation', [HomeController::class, 'skillConsultation'])->name('consultation');
});

// Blog Routes
Route::prefix('blog')->name('frontend.blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/search', [BlogController::class, 'search'])->name('search');
    Route::get('/category/{slug}', [BlogController::class, 'category'])->name('category');
    Route::get('/tag/{slug}', [BlogController::class, 'tag'])->name('tag');
    Route::get('/{slug}', [BlogController::class, 'show'])->name('show');
});


// Documentation — للمستخدمين المسجّلين فقط؛ نفس تصميم public/docs/css/style.css
// auth.query_token: ?token= للـ WebView (Flutter) يحقن Bearer قبل Sanctum؛ الجلسة تبقى مدعومة عبر auth:sanctum + guard web
Route::middleware(['auth.query_token', 'auth:sanctum'])->group(function () {
    Route::prefix('docs')->name('frontend.docs.')->group(function () {
        Route::get('/', [DocumentationController::class, 'index'])->name('index');
        Route::get('/{categorySlug}/{pagePath?}', [DocumentationController::class, 'show'])
            ->where('pagePath', '.*')
            ->name('show');
    });
});
// Sitemap Route
Route::get('/sitemap.xml', [\App\Http\Controllers\Frontend\SitemapController::class, 'index'])->name('frontend.sitemap');

// Robots.txt Route (dynamic)
Route::get('/robots.txt', function() {
    $content = "# robots.txt\n";
    $content .= "# Generated automatically for " . config('app.name') . "\n\n";
    
    $content .= "# Allow all search engines\n";
    $content .= "User-agent: *\n";
    $content .= "Allow: /\n";
    $content .= "Allow: /courses\n";
    $content .= "Allow: /course\n";
    $content .= "Allow: /blog\n";
    $content .= "Allow: /reviews\n";
    $content .= "Allow: /students\n";
    $content .= "Allow: /contact\n\n";
    
    $content .= "# Disallow admin and student panels\n";
    $content .= "Disallow: /admin/\n";
    $content .= "Disallow: /student/\n";
    $content .= "Disallow: /api/\n";
    $content .= "Disallow: /docs\n\n";
    
    $content .= "# Disallow private files\n";
    $content .= "Disallow: /storage/private/\n";
    $content .= "Disallow: /storage/temp/\n\n";
    
    $content .= "# Crawl-delay (optional, helps with server load)\n";
    $content .= "# Crawl-delay: 1\n\n";
    
    $content .= "# Sitemap location\n";
    $content .= "Sitemap: " . url('/sitemap.xml') . "\n";
    
    return response($content, 200)
        ->header('Content-Type', 'text/plain; charset=utf-8');
})->name('frontend.robots');
