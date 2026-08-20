<?php

namespace Tests\Unit\Backup;

use App\Models\BackupSchedule;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BackupScheduleNextRunTest extends TestCase
{
    #[Test]
    public function daily_uses_today_when_time_has_not_passed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-22 01:00:00'));

        $schedule = new BackupSchedule([
            'frequency' => 'daily',
            'time' => '02:00:00',
        ]);

        $this->assertSame(
            '2026-07-22 02:00:00',
            $schedule->calculateNextRun()->format('Y-m-d H:i:s')
        );

        Carbon::setTestNow();
    }

    #[Test]
    public function daily_uses_tomorrow_when_time_has_passed(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-22 03:00:00'));

        $schedule = new BackupSchedule([
            'frequency' => 'daily',
            'time' => '02:00:00',
        ]);

        $this->assertSame(
            '2026-07-23 02:00:00',
            $schedule->calculateNextRun()->format('Y-m-d H:i:s')
        );

        Carbon::setTestNow();
    }

    #[Test]
    public function should_run_when_next_run_is_due(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-22 02:05:00'));

        $schedule = new BackupSchedule([
            'is_active' => true,
            'next_run_at' => Carbon::parse('2026-07-22 02:00:00'),
        ]);

        $this->assertTrue($schedule->shouldRun());

        Carbon::setTestNow();
    }

    #[Test]
    public function schedule_timezone_defaults_to_the_app_timezone(): void
    {
        $schedule = new BackupSchedule(['frequency' => 'daily', 'time' => '02:00:00']);

        $this->assertSame(config('app.timezone'), $schedule->scheduleTimezone());
    }

    #[Test]
    public function daily_time_is_interpreted_in_the_schedule_timezone(): void
    {
        config(['app.timezone' => 'UTC']);
        Carbon::setTestNow(Carbon::parse('2026-08-20 12:00:00', 'UTC'));

        $schedule = new BackupSchedule([
            'frequency' => 'daily',
            'time' => '02:00:00',
            'timezone' => 'Asia/Damascus',
        ]);

        $next = $schedule->calculateNextRun();

        // 02:00 بدمشق (UTC+3) = 23:00 UTC في اليوم السابق
        $this->assertSame('2026-08-20 23:00:00', $next->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $next->timezoneName);
        $this->assertSame('02:00', $next->copy()->setTimezone('Asia/Damascus')->format('H:i'));

        Carbon::setTestNow();
    }

    #[Test]
    public function daily_stays_today_when_the_local_time_has_not_passed_yet(): void
    {
        config(['app.timezone' => 'UTC']);
        Carbon::setTestNow(Carbon::parse('2026-08-20 12:00:00', 'UTC')); // 15:00 بدمشق

        $schedule = new BackupSchedule([
            'frequency' => 'daily',
            'time' => '18:00:00',
            'timezone' => 'Asia/Damascus',
        ]);

        $this->assertSame(
            '2026-08-20 15:00:00',
            $schedule->calculateNextRun()->format('Y-m-d H:i:s')
        );

        Carbon::setTestNow();
    }

    #[Test]
    public function weekly_respects_the_schedule_timezone(): void
    {
        config(['app.timezone' => 'UTC']);
        Carbon::setTestNow(Carbon::parse('2026-08-20 12:00:00', 'UTC')); // الخميس

        $schedule = new BackupSchedule([
            'frequency' => 'weekly',
            'time' => '09:00:00',
            'timezone' => 'Asia/Damascus',
            'days_of_week' => [0], // الأحد
        ]);

        $next = $schedule->calculateNextRun();

        $this->assertSame('2026-08-23 06:00:00', $next->format('Y-m-d H:i:s'));
        $this->assertSame('09:00', $next->copy()->setTimezone('Asia/Damascus')->format('H:i'));

        Carbon::setTestNow();
    }

    #[Test]
    public function monthly_day_31_clamps_instead_of_rolling_into_the_next_month(): void
    {
        config(['app.timezone' => 'UTC']);
        Carbon::setTestNow(Carbon::parse('2026-09-15 12:00:00', 'UTC')); // سبتمبر = 30 يوماً

        $schedule = new BackupSchedule([
            'frequency' => 'monthly',
            'time' => '02:00:00',
            'timezone' => 'UTC',
            'day_of_month' => 31,
        ]);

        $next = $schedule->calculateNextRun();

        // قبل الإصلاح كان day(31) ينزلق إلى 1 أكتوبر
        $this->assertSame('2026-09-30 02:00:00', $next->format('Y-m-d H:i:s'));

        Carbon::setTestNow();
    }

    #[Test]
    public function legacy_custom_frequency_still_computes_a_next_run(): void
    {
        config(['app.timezone' => 'UTC']);
        Carbon::setTestNow(Carbon::parse('2026-08-20 12:00:00', 'UTC'));

        // "custom" أُزيل من قائمة الواجهة لكن صفوفاً قديمة قد تحمله
        $schedule = new BackupSchedule(['frequency' => 'custom', 'time' => '02:00:00']);

        $this->assertSame(
            '2026-08-21 02:00:00',
            $schedule->calculateNextRun()->format('Y-m-d H:i:s')
        );

        Carbon::setTestNow();
    }
}
