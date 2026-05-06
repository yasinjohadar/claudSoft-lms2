<?php

namespace App\Console\Commands;

use App\Services\Reports\StudentWeeklyReportScheduleService;
use Illuminate\Console\Command;

class GenerateWeeklyReportsCommand extends Command
{
    protected $signature = 'reports:weekly-generate';
    protected $description = 'Generate weekly student reports from active schedules';

    public function handle(StudentWeeklyReportScheduleService $service): int
    {
        $count = $service->runDueSchedules();
        $this->info("Generated {$count} weekly reports.");
        return self::SUCCESS;
    }
}

