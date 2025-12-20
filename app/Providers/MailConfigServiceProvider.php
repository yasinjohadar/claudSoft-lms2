<?php

namespace App\Providers;

use App\Models\EmailSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load active email settings on application boot
        try {
            $activeSettings = EmailSetting::getActive();

            if ($activeSettings) {
                $activeSettings->applyToConfig();
            }
        } catch (\Exception $e) {
            // Database might not be ready during migration/install
            // Silently fail to prevent breaking the application
        }
    }
}
