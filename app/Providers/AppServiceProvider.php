<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\ContactSetting;

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
    }
}