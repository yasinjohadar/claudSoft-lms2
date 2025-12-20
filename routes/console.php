<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Gamification Scheduled Tasks
|--------------------------------------------------------------------------
*/

// المهام اليومية - تعمل كل يوم الساعة 00:00
Schedule::command('gamification:daily-tasks')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->runInBackground();

// تحديث الإحصائيات - كل ساعة
Schedule::command('gamification:update-stats')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// الملخص الأسبوعي - كل أحد الساعة 09:00
Schedule::command('gamification:weekly-summary')
    ->weeklyOn(0, '09:00')
    ->withoutOverlapping()
    ->runInBackground();
