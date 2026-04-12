<?php

namespace App\Providers;

use App\Models\ContactSetting;
use App\Notifications\Channels\WhatsAppChannel;
use App\Services\WhatsApp\WhatsAppSettingsService;
use Illuminate\Auth\RequestGuard;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        require_once app_path('Helpers/MixedBidiHelper.php');

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
