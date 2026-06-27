<?php

namespace App\Console\Commands;

use App\Services\Marketing\MarketingAnalyticsService;
use Illuminate\Console\Command;

class GoogleAnalyticsSyncCommand extends Command
{
    protected $signature = 'google-analytics:sync {--test : Test API connection only} {--period=30d : Period key (7d, 30d, 90d)}';

    protected $description = 'Sync Google Analytics & Search Console data into cache';

    public function handle(MarketingAnalyticsService $analytics): int
    {
        if ($this->option('test')) {
            $result = $analytics->testConnection();
            $this->line($result['message']);

            return $result['success'] ? self::SUCCESS : self::FAILURE;
        }

        $period = $this->option('period');

        if ($this->option('period') && $this->input->getOption('period') !== '30d') {
            $analytics->getDashboardData($period, true);
            $this->info("Synced period: {$period}");

            return self::SUCCESS;
        }

        $this->info('Pre-warming all analytics periods...');
        $analytics->syncAll(true);
        $this->info('Analytics cache sync completed.');

        return self::SUCCESS;
    }
}
