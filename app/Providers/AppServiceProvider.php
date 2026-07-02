<?php

namespace App\Providers;

use App\Models\ContactSetting;
use App\Models\Payment;
use App\Observers\PaymentObserver;
use App\Services\Admin\ActivityLogService;
use App\Services\Finance\StudentDueInvoicesAlertService;
use App\Notifications\Channels\WhatsAppChannel;
use App\Services\WhatsApp\WhatsAppSettingsService;
use Illuminate\Auth\RequestGuard;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\Marketing\MetaPixelService::class);
        $this->app->singleton(\App\Services\Marketing\GoogleDataLayerService::class);
        $this->app->singleton(\App\Services\Marketing\MarketingAnalyticsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        require_once app_path('Helpers/MixedBidiHelper.php');

        Payment::observe(PaymentObserver::class);

        if (class_exists(\Spatie\Activitylog\CauserResolver::class)) {
            app(\Spatie\Activitylog\CauserResolver::class)->resolveUsing(
                fn () => ActivityLogService::resolveCauser()
            );
        }

        Event::subscribe(\App\Listeners\LogAuthenticationActivity::class);

        RateLimiter::for('wapi-send', function (Request $request) {
            $perMinute = (int) config('services.whatsapp.rate_limit_per_minute', 30);

            return Limit::perMinute(max(1, $perMinute))->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip()));
        });

        $this->registerSanctumDriverIfNeeded();

        Paginator::useBootstrapFive();

        // تسجيل PermissionServiceProvider
        $this->app->register(PermissionServiceProvider::class);

        // تسجيل GamificationServiceProvider
        $this->app->register(GamificationServiceProvider::class);

        // مشاركة إعدادات الاتصال مع جميع صفحات الواجهة الأمامية
        View::composer('frontend.layouts.footer', function ($view) {
            $contactSettings = ContactSetting::getSettings();
            $view->with('contactSettings', $contactSettings);
        });
        View::composer('frontend2.layouts.footer', function ($view) {
            $contactSettings = ContactSetting::getSettings();
            $view->with('contactSettings', $contactSettings);
        });

        View::composer('student.layouts.master', function ($view) {
            if (! auth()->check()) {
                return;
            }

            $alert = app(StudentDueInvoicesAlertService::class)
                ->forUser(auth()->user());

            $view->with('dueInvoicesAlert', $alert);
        });

        $metaPixelViews = [
            'frontend2.layouts.master',
            'frontend.group-registration.layout',
            'frontend.layouts.master',
            'frontend.layouts.standalone',
        ];

        View::composer($metaPixelViews, \App\View\Composers\MetaPixelComposer::class);
        View::composer($metaPixelViews, \App\View\Composers\GoogleTagComposer::class);

        View::composer('frontend2.pages.about', function ($view) {
            app(\App\Services\Marketing\MetaPixelService::class)->trackViewContent(
                'من نحن',
                'about'
            );
        });

        $servicePages = [
            'frontend2.pages.service-detail' => 'تطوير الويب',
            'frontend2.pages.service-detail-servers' => 'الخوادم',
            'frontend2.pages.service-detail-security' => 'الأمن السيبراني',
            'frontend2.pages.service-detail-mobile' => 'تطبيقات الموبايل',
            'frontend2.pages.service-detail-devops' => 'DevOps',
            'frontend2.pages.consultation' => 'الاستشارات',
        ];

        foreach ($servicePages as $viewName => $title) {
            View::composer($viewName, function ($view) use ($title) {
                app(\App\Services\Marketing\MetaPixelService::class)->trackViewContent(
                    $title,
                    'service'
                );
            });
        }

        // Initialize WhatsApp settings defaults
        try {
            $whatsappSettingsService = app(WhatsAppSettingsService::class);
            $whatsappSettingsService->initializeDefaults();
        } catch (\Exception $e) {
            // Silently fail if table doesn't exist yet (migration not run)
        }

        // WhatsApp Event Listeners
        Event::listen(\App\Events\WhatsAppMessageReceived::class, \App\Listeners\AutoReplyWhatsAppListener::class);

        Notification::extend('whatsapp', function ($app) {
            return $app->make(WhatsAppChannel::class);
        });

        $this->configureAiHttpSslVerify();
    }

    /**
     * SSL verify for Laravel AI SDK (Prism uses Http / Guzzle).
     * Priority: AI_HTTP_VERIFY (.env) → storage/cacert.pem if present → default PHP/cURL.
     */
    protected function configureAiHttpSslVerify(): void
    {
        $raw = config('ai.http.verify');

        if ($raw !== null && $raw !== '') {
            if (is_string($raw) && in_array(strtolower($raw), ['false', '0', 'no'], true)) {
                Http::globalOptions(['verify' => false]);

                return;
            }

            if (is_string($raw) && is_file($raw)) {
                Http::globalOptions(['verify' => $raw]);

                return;
            }

            if ($raw === true || (is_string($raw) && strtolower($raw) === 'true')) {
                Http::globalOptions(['verify' => true]);

                return;
            }
        }

        $fallback = storage_path('cacert.pem');
        if (is_file($fallback)) {
            Http::globalOptions(['verify' => $fallback]);
        }
    }

    /**
     * تسجيل Auth driver [sanctum] يدوياً إن وُجدت كلاسات Sanctum (يعمل حتى لو لم يُحمّل SanctumServiceProvider على السيرفر).
     */
    protected function registerSanctumDriverIfNeeded(): void
    {
        $guardClass = \Laravel\Sanctum\Guard::class;
        if (! class_exists($guardClass)) {
            // عند رفع vendor يدوياً قد لا يُحدّث الـ autoload — تحميل الملف يدوياً
            $guardFile = base_path('vendor/laravel/sanctum/src/Guard.php');
            if (file_exists($guardFile)) {
                require_once $guardFile;
            }
            if (! class_exists($guardClass)) {
                return;
            }
        }

        Auth::extend('sanctum', function ($app, $name, array $config) {
            $auth = $app['auth'];
            $expiration = config('sanctum.expiration');
            $lastUsedAt = config('sanctum.last_used_at', true);
            $provider = $config['provider'] ?? null;

            // مطابق لـ SanctumServiceProvider: تحديث مرجع الطلب على كل طلب حتى يُقرأ Bearer بعد حقن الوسيط.
            return tap(
                new RequestGuard(
                    new \Laravel\Sanctum\Guard($auth, $expiration, $provider, $lastUsedAt),
                    $app['request'],
                    $auth->createUserProvider($provider)
                ),
                static function (RequestGuard $guard) use ($app): void {
                    $app->refresh('request', $guard, 'setRequest');
                }
            );
        });
    }
}
