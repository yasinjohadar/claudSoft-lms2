<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Gamification\LeaderboardService;
use App\Services\Gamification\CompetitionService;
use App\Services\Gamification\AnalyticsService;

class UpdateGamificationStats extends Command
{
    protected $signature = 'gamification:update-stats';
    protected $description = 'تحديث إحصائيات نظام الـ Gamification';

    protected LeaderboardService $leaderboardService;
    protected CompetitionService $competitionService;
    protected AnalyticsService $analyticsService;

    public function __construct(
        LeaderboardService $leaderboardService,
        CompetitionService $competitionService,
        AnalyticsService $analyticsService
    ) {
        parent::__construct();
        $this->leaderboardService = $leaderboardService;
        $this->competitionService = $competitionService;
        $this->analyticsService = $analyticsService;
    }

    public function handle()
    {
        $this->info('📊 تحديث الإحصائيات...');

        // 1. تحديث لوحات المتصدرين
        $this->info('🏆 تحديث لوحات المتصدرين...');
        $updated = $this->leaderboardService->updateAllLeaderboards();
        $this->info("   ✓ تم تحديث " . count($updated) . " لوحة");

        // 2. فحص المنافسات المنتهية
        $this->info('⚔️ فحص المنافسات المنتهية...');
        $expired = $this->competitionService->checkExpiredCompetitions();
        $this->info("   ✓ تم إنهاء {$expired} منافسة");

        // 3. مسح كاش التحليلات
        $this->info('🔄 تحديث كاش التحليلات...');
        $this->analyticsService->clearCache();
        $this->info("   ✓ تم مسح الكاش");

        $this->info('✅ تم تحديث الإحصائيات بنجاح!');

        return Command::SUCCESS;
    }
}
