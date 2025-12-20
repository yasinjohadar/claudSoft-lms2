<?php

namespace App\Services\Gamification;

use App\Models\User;
use App\Models\Gamification\UserStat;
use App\Models\Gamification\PointTransaction;
use App\Models\Gamification\Badge;
use App\Models\Gamification\Achievement;
use App\Models\Gamification\Challenge;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * لوحة تحكم الإحصائيات الرئيسية
     */
    public function getDashboardStats(): array
    {
        return Cache::remember('gamification_dashboard_stats', 300, function () {
            return [
                'overview' => $this->getOverviewStats(),
                'points' => $this->getPointsStats(),
                'levels' => $this->getLevelsStats(),
                'badges' => $this->getBadgesStats(),
                'achievements' => $this->getAchievementsStats(),
                'challenges' => $this->getChallengesStats(),
                'shop' => $this->getShopStats(),
                'social' => $this->getSocialStats(),
                'engagement' => $this->getEngagementStats(),
            ];
        });
    }

    /**
     * إحصائيات عامة
     */
    protected function getOverviewStats(): array
    {
        $totalStudents = User::whereHas('roles', function($q) {
            $q->where('name', 'student');
        })->count();
        $activeStudents = User::whereHas('roles', function($q) {
            $q->where('name', 'student');
        })->where('is_active', true)
            ->count();

        $studentsWithStats = UserStat::count();

        $totalPoints = UserStat::sum('total_points');
        $totalXP = UserStat::sum('total_xp');

        return [
            'total_students' => $totalStudents,
            'active_students' => $activeStudents,
            'students_with_stats' => $studentsWithStats,
            'total_points_distributed' => $totalPoints,
            'total_xp_distributed' => $totalXP,
        ];
    }

    /**
     * إحصائيات النقاط
     */
    protected function getPointsStats(): array
    {
        $totalTransactions = PointTransaction::count();
        $totalPointsAwarded = PointTransaction::where('type', 'earned')->sum('points');
        $totalPointsSpent = PointTransaction::where('type', 'spent')->sum('points');

        $pointsByAction = PointTransaction::selectRaw('source, SUM(points) as total, COUNT(*) as count')
            ->where('type', 'earned')
            ->groupBy('source')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $dailyPoints = PointTransaction::selectRaw('DATE(created_at) as date, SUM(points) as total')
            ->where('type', 'earned')
            ->whereBetween('created_at', [now()->subDays(30), now()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topEarners = UserStat::orderByDesc('total_points')
            ->with('user:id,name,email')
            ->limit(10)
            ->get(['user_id', 'total_points']);

        return [
            'total_transactions' => $totalTransactions,
            'total_awarded' => $totalPointsAwarded,
            'total_spent' => $totalPointsSpent,
            'by_action' => $pointsByAction,
            'daily_points' => $dailyPoints,
            'top_earners' => $topEarners,
        ];
    }

    /**
     * إحصائيات المستويات
     */
    protected function getLevelsStats(): array
    {
        $levelDistribution = UserStat::selectRaw('current_level, COUNT(*) as count')
            ->groupBy('current_level')
            ->orderBy('current_level')
            ->get();

        $averageLevel = UserStat::avg('current_level');
        $maxLevel = UserStat::max('current_level');

        $recentLevelUps = UserStat::where('updated_at', '>=', now()->subDays(7))
            ->orderByDesc('current_level')
            ->with('user:id,name,email')
            ->limit(10)
            ->get(['user_id', 'current_level', 'total_xp']);

        return [
            'level_distribution' => $levelDistribution,
            'average_level' => round($averageLevel, 2),
            'max_level' => $maxLevel,
            'recent_level_ups' => $recentLevelUps,
        ];
    }

    /**
     * إحصائيات الشارات
     */
    protected function getBadgesStats(): array
    {
        $totalBadgesAwarded = DB::table('gamification_user_badges')->count();

        $badgeDistribution = DB::table('gamification_user_badges')
            ->join('gamification_badges', 'gamification_user_badges.badge_id', '=', 'gamification_badges.id')
            ->selectRaw('gamification_badges.type, COUNT(*) as count')
            ->groupBy('gamification_badges.type')
            ->get();

        $mostEarnedBadges = DB::table('gamification_user_badges')
            ->selectRaw('badge_id, COUNT(*) as count')
            ->groupBy('badge_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $rarestBadges = DB::table('gamification_user_badges')
            ->selectRaw('badge_id, COUNT(*) as count')
            ->groupBy('badge_id')
            ->orderBy('count')
            ->limit(10)
            ->get();

        return [
            'total_awarded' => $totalBadgesAwarded,
            'by_rarity' => $badgeDistribution,
            'most_earned' => $mostEarnedBadges,
            'rarest' => $rarestBadges,
        ];
    }

    /**
     * إحصائيات الإنجازات
     */
    protected function getAchievementsStats(): array
    {
        $totalCompleted = DB::table('gamification_user_achievements')->where('status', 'completed')->count();
        $totalInProgress = DB::table('gamification_user_achievements')->where('status', 'in_progress')->count();

        $completionRate = $totalCompleted + $totalInProgress > 0
            ? round(($totalCompleted / ($totalCompleted + $totalInProgress)) * 100, 2)
            : 0;

        $byTier = DB::table('gamification_user_achievements')
            ->join('gamification_achievements', 'gamification_user_achievements.achievement_id', '=', 'gamification_achievements.id')
            ->where('gamification_user_achievements.status', 'completed')
            ->selectRaw('gamification_achievements.tier, COUNT(*) as count')
            ->groupBy('gamification_achievements.tier')
            ->get();

        return [
            'total_completed' => $totalCompleted,
            'total_in_progress' => $totalInProgress,
            'completion_rate' => $completionRate,
            'by_tier' => $byTier,
        ];
    }

    /**
     * إحصائيات التحديات
     */
    protected function getChallengesStats(): array
    {
        // لا توجد جداول user_challenges حالياً، نستخدم فقط التحديات المتاحة
        $totalChallenges = Challenge::count();
        $activeChallenges = Challenge::where('is_active', true)->count();

        return [
            'total_assigned' => 0,
            'total_completed' => 0,
            'total_expired' => 0,
            'total_active' => $activeChallenges,
            'completion_rate' => 0,
            'by_type' => [],
        ];
    }

    /**
     * إحصائيات المتجر
     */
    protected function getShopStats(): array
    {
        // لا توجد جداول user_purchases حالياً
        return [
            'total_purchases' => 0,
            'total_revenue' => [
                'points' => 0,
                'gems' => 0,
            ],
            'purchases_today' => 0,
            'purchases_this_week' => 0,
            'purchases_this_month' => 0,
        ];
    }

    /**
     * إحصائيات اجتماعية
     */
    protected function getSocialStats(): array
    {
        // لا توجد جداول social_activities أو competitions حالياً
        return [
            'total_activities' => 0,
            'total_likes' => 0,
            'total_comments' => 0,
            'total_competitions' => 0,
            'active_competitions' => 0,
        ];
    }

    /**
     * إحصائيات التفاعل
     */
    protected function getEngagementStats(): array
    {
        // الطلاب النشطون اليوم
        $activeToday = UserStat::whereDate('last_activity_date', today())->count();

        // الطلاب النشطون هذا الأسبوع
        $activeThisWeek = UserStat::whereBetween('last_activity_date', [now()->startOfWeek(), now()])->count();

        // متوسط السلسلة
        $averageStreak = UserStat::avg('current_streak');

        // أطول سلسلة
        $longestStreak = UserStat::max('longest_streak');

        // معدل الاحتفاظ
        $retentionRate = $this->calculateRetentionRate();

        return [
            'active_today' => $activeToday,
            'active_this_week' => $activeThisWeek,
            'average_streak' => round($averageStreak, 2),
            'longest_streak' => $longestStreak,
            'retention_rate' => $retentionRate,
        ];
    }

    /**
     * حساب معدل الاحتفاظ
     */
    protected function calculateRetentionRate(): float
    {
        $totalStudents = User::whereHas('roles', function($q) {
            $q->where('name', 'student');
        })->count();

        if ($totalStudents === 0) {
            return 0;
        }

        $activeLastWeek = UserStat::where('last_activity_date', '>=', now()->subDays(7))->count();

        return round(($activeLastWeek / $totalStudents) * 100, 2);
    }

    /**
     * تقرير الطالب الشخصي
     */
    public function getStudentReport(User $user): array
    {
        $stats = $user->stats;

        return [
            'overview' => [
                'level' => $stats->current_level,
                'total_xp' => $stats->total_xp,
                'total_points' => $stats->total_points,
                'available_points' => $stats->available_points,
                'available_gems' => $stats->available_gems,
                'current_streak' => $stats->current_streak,
                'longest_streak' => $stats->longest_streak,
            ],
            'progress' => [
                'lessons_completed' => $stats->lessons_completed,
                'courses_completed' => $stats->courses_completed,
                'quizzes_completed' => $stats->quizzes_completed,
                'perfect_scores' => $stats->perfect_scores,
                'assignments_completed' => $stats->assignments_completed,
            ],
            'achievements' => [
                'total_badges' => $stats->total_badges,
                'total_achievements' => $stats->total_achievements,
                'challenges_completed' => $stats->challenges_completed,
                'competitions_won' => $stats->competitions_won,
            ],
            'social' => [
                'total_friends' => $stats->total_friends,
            ],
            'rankings' => [
                'global_rank' => $stats->global_rank,
                'weekly_rank' => $stats->weekly_rank,
                'monthly_rank' => $stats->monthly_rank,
            ],
        ];
    }

    /**
     * مقارنة الطالب بالمتوسط
     */
    public function compareToAverage(User $user): array
    {
        $stats = $user->stats;

        $averages = [
            'level' => UserStat::avg('current_level'),
            'points' => UserStat::avg('total_points'),
            'xp' => UserStat::avg('total_xp'),
            'badges' => UserStat::avg('total_badges'),
            'streak' => UserStat::avg('current_streak'),
            'lessons' => UserStat::avg('lessons_completed'),
        ];

        return [
            'level' => [
                'user' => $stats->current_level,
                'average' => round($averages['level'], 2),
                'difference' => round($stats->current_level - $averages['level'], 2),
            ],
            'points' => [
                'user' => $stats->total_points,
                'average' => round($averages['points'], 2),
                'difference' => round($stats->total_points - $averages['points'], 2),
            ],
            'badges' => [
                'user' => $stats->total_badges,
                'average' => round($averages['badges'], 2),
                'difference' => round($stats->total_badges - $averages['badges'], 2),
            ],
            'streak' => [
                'user' => $stats->current_streak,
                'average' => round($averages['streak'], 2),
                'difference' => round($stats->current_streak - $averages['streak'], 2),
            ],
        ];
    }

    /**
     * مسح الكاش
     */
    public function clearCache(): void
    {
        Cache::forget('gamification_dashboard_stats');
    }
}
