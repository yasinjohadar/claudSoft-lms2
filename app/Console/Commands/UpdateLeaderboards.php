<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Gamification\LeaderboardService;

class UpdateLeaderboards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leaderboards:update {--type=all : Type of leaderboard to update (all, global, weekly, monthly)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تحديث لوحات المتصدرين';

    protected LeaderboardService $leaderboardService;

    /**
     * Create a new command instance.
     */
    public function __construct(LeaderboardService $leaderboardService)
    {
        parent::__construct();
        $this->leaderboardService = $leaderboardService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 بدء تحديث لوحات المتصدرين...');

        $updated = $this->leaderboardService->updateAllLeaderboards();

        $this->info("✅ تم تحديث " . count($updated) . " لوحة متصدرين بنجاح!");

        return Command::SUCCESS;
    }
}
