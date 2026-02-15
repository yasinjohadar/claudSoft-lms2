<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\RequestGuard;
use App\Models\ContactSetting;
use App\Services\WhatsApp\WhatsAppSettingsService;

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

        // Initialize WhatsApp settings defaults
        try {
            $whatsappSettingsService = app(WhatsAppSettingsService::class);
            $whatsappSettingsService->initializeDefaults();
        } catch (\Exception $e) {
            // Silently fail if table doesn't exist yet (migration not run)
        }

        // WhatsApp Event Listeners
        Event::listen(\App\Events\WhatsAppMessageReceived::class, \App\Listeners\AutoReplyWhatsAppListener::class);

    }

    /**
     * تسجيل Auth driver [sanctum] يدوياً إن وُجدت كلاسات Sanctum (يعمل حتى لو لم يُحمّل SanctumServiceProvider على السيرفر).
     */
    protected function registerSanctumDriverIfNeeded(): void
    {
        if (!class_exists(\Laravel\Sanctum\Guard::class)) {
            return;
        }

        Auth::extend('sanctum', function ($app, $name, array $config) {
            $auth = $app['auth'];
            return new RequestGuard(
                new \Laravel\Sanctum\Guard(
                    $auth,
                    config('sanctum.expiration'),
                    $config['provider'] ?? null,
                    config('sanctum.last_used_at', true)
                ),
                $app['request'],
                $auth->createUserProvider($config['provider'] ?? null)
            );
        });
    }
}