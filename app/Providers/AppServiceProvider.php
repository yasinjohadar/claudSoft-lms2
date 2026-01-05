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
        
        // Route Model Binding for QuestionModule
        \Illuminate\Support\Facades\Route::bind('questionModule', function ($value) {
            // Try to find by ID first
            $questionModule = \App\Models\QuestionModule::find($value);
            
            if (!$questionModule) {
                // Log the failure
                \Illuminate\Support\Facades\Log::error('QuestionModule route binding failed', [
                    'value' => $value,
                    'type' => gettype($value),
                ]);
                
                // Throw 404
                abort(404, 'Question Module not found');
            }
            
            return $questionModule;
        });
        
        // Route Model Binding for QuestionModuleAttempt
        \Illuminate\Support\Facades\Route::bind('attempt', function ($value) {
            // Try to find by ID first
            $attempt = \App\Models\QuestionModuleAttempt::find($value);
            
            if (!$attempt) {
                // Log the failure
                \Illuminate\Support\Facades\Log::error('QuestionModuleAttempt route binding failed', [
                    'value' => $value,
                    'type' => gettype($value),
                ]);
                
                // Throw 404
                abort(404, 'Attempt not found');
            }
            
            return $attempt;
        });
    }
}