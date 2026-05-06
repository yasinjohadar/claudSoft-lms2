<?php

namespace App\Console\Commands;

use App\Services\Reports\StudentWeeklyReportService;
use Illuminate\Console\Command;

class CloseOverdueWeeklyReportsCommand extends Command
{
    protected $signature = 'reports:weekly-close-overdue';
    protected $description = 'Close overdue weekly student reports';

    public function handle(StudentWeeklyReportService $service): int
    {
        $count = $service->closeOverdueReports();
        $this->info("Closed {$count} overdue weekly reports.");
        return self::SUCCESS;
    }
}

