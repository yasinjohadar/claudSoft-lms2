<?php

use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\CourseController;
use App\Http\Controllers\Frontend\DocumentationController;
use App\Http\Controllers\Frontend\DocumentationExportController;
use App\Http\Controllers\Frontend\GroupRegistrationController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ProfileCardController;
use App\Http\Controllers\Frontend\ReviewController;
use App\Http\Controllers\Frontend\SimulatorPlayerController;
use App\Http\Controllers\Frontend\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('frontend.home');
Route::get('/about', [HomeController::class, 'about'])->name('frontend.about');
Route::get('/yasin-jokhadar', [HomeController::class, 'yasinJokhadar'])->name('frontend.yasin-jokhadar');
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
Route::get('/card/{slug}', [ProfileCardController::class, 'show'])->name('frontend.profile-card.show');
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

// Signed render for Browsershot PDF export (no auth — temporary signature only)
Route::get('/docs/export-render/{documentation_page}/{context}', [DocumentationExportController::class, 'render'])
    ->middleware('signed')
    ->whereIn('context', ['public', 'admin'])
    ->name('frontend.docs.export-render');

// Documentation — للمستخدمين المسجّلين فقط؛ نفس تصميم public/docs/css/style.css
// auth.query_token: ?token= للـ WebView (Flutter) يحقن Authorization: Bearer قبل auth:sanctum (نفس توكن Sanctum من التطبيق).
// الجلسة عبر الكوكيز ما زالت مدعومة للمتصفح؛ طلبات Bearer لا تحتاج SANCTUM_STATEFUL_DOMAINS.
Route::middleware(['auth.query_token', 'auth:sanctum'])->group(function () {
    Route::get('/simulator/{slug}', [SimulatorPlayerController::class, 'show'])
        ->name('frontend.simulator.show');
    Route::get('/simulator/{slug}/play', [SimulatorPlayerController::class, 'play'])
        ->name('frontend.simulator.play');
    Route::get('/simulator/{slug}/assets/{file}', [SimulatorPlayerController::class, 'asset'])
        ->where('file', 'page.css|simulator.js')
        ->name('frontend.simulator.asset');

    Route::prefix('docs')->name('frontend.docs.')->group(function () {
        Route::get('/', [DocumentationController::class, 'index'])->name('index');
        Route::get('/export/{documentation_page}/pdf', [DocumentationExportController::class, 'download'])
            ->name('pdf');
        Route::get('/{categorySlug}', [DocumentationController::class, 'category'])->name('category');
        Route::get('/{categorySlug}/{pagePath}', [DocumentationController::class, 'show'])
            ->where('pagePath', '.+')
            ->name('show');
    });
});
// Sitemap Route
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('frontend.sitemap');

// robots.txt is served as a static file at public/robots.txt — standard
// Nginx/Apache config serves it directly without reaching the router, so a
// dynamic route here would be dead code (it used to exist and never ran).
