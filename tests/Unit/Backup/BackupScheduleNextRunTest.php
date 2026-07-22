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
}
