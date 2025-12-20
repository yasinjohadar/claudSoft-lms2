<?php

namespace App\Http\Controllers\Student\Gamification;

use App\Http\Controllers\Controller;
use App\Services\Gamification\GamificationService;
use App\Services\Gamification\PointsService;
use App\Services\Gamification\LevelService;
use App\Services\Gamification\StreakService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected GamificationService $gamificationService;
    protected PointsService $pointsService;
    protected LevelService $levelService;
    protected StreakService $streakService;

    public function __construct(
        GamificationService $gamificationService,
        PointsService $pointsService,
        LevelService $levelService,
        StreakService $streakService
    ) {
        $this->gamificationService = $gamificationService;
        $this->pointsService = $pointsService;
        $this->levelService = $levelService;
        $this->streakService = $streakService;
    }

    /**
     * عرض لوحة التحكم الرئيسية للطالب
     */
    public function index()
    {
        $user = auth()->user();

        // الحصول على ملخص كامل
        $dashboard = $this->gamificationService->getUserDashboard($user);

        // معلومات المستوى
        $levelInfo = $this->levelService->getUserLevelInfo($user);

        // معلومات السلسلة
        $streakInfo = $this->streakService->getStreakInfo($user);

        // النشاط الأخير
        $recentActivity = $this->gamificationService->getRecentActivity($user, 10);

        // أحدث الشارات
        $latestBadges = $user->userBadges()
            ->with('badge')
            ->latest('awarded_at')
            ->limit(6)
            ->get();

        // الإنجازات قيد التقدم
        $inProgressAchievements = $user->userAchievements()
            ->with('achievement')
            ->where('status', 'in_progress')
            ->orderByDesc('progress_percentage')
            ->limit(5)
            ->get();

        // التحديات النشطة
        $activeChallenges = $user->userChallenges()
            ->with('challenge')
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->get();

        return view('student.pages.gamification.dashboard', compact(
            'dashboard',
            'levelInfo',
            'streakInfo',
            'recentActivity',
            'latestBadges',
            'inProgressAchievements',
            'activeChallenges'
        ));
    }

    /**
     * عرض صفحة الملف الشخصي للألعاب
     */
    public function profile()
    {
        $user = auth()->user();
        $stats = $user->stats;

        // الشارات حسب النوع
        $badgesByType = $user->userBadges()
            ->with('badge')
            ->get()
            ->groupBy('badge.type');

        // الإنجازات حسب المرتبة
        $achievementsByTier = $user->userAchievements()
            ->with('achievement')
            ->where('status', 'completed')
            ->get()
            ->groupBy('achievement.tier');

        // إحصائيات الشهر الحالي
        $monthlyStats = $this->streakService->getMonthlyStreakStats($user);

        // الوقت المتوقع للمستوى التالي
        $timeToNextLevel = $this->levelService->estimateTimeToNextLevel($user);

        return view('student.pages.gamification.profile', compact(
            'user',
            'stats',
            'badgesByType',
            'achievementsByTier',
            'monthlyStats',
            'timeToNextLevel'
        ));
    }

    /**
     * عرض صفحة إحصائياتي
     */
    public function statistics()
    {
        $user = auth()->user();
        $stats = $user->stats;

        // معلومات المستوى
        $levelInfo = $this->levelService->getUserLevelInfo($user);

        // معلومات السلسلة
        $streakInfo = $this->streakService->getStreakInfo($user);

        // تاريخ النقاط (آخر 30 يوم)
        $pointsHistory = $user->pointsTransactions()
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, SUM(CASE WHEN points > 0 THEN points ELSE 0 END) as earned')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // النقاط حسب المصدر
        $pointsBySource = $user->pointsTransactions()
            ->where('points', '>', 0)
            ->selectRaw('source, SUM(points) as total, COUNT(*) as count')
            ->groupBy('source')
            ->orderByDesc('total')
            ->get();

        // نشاط آخر 30 يوم
        $monthlyActivity = \DB::table('daily_streaks')
            ->where('user_id', $user->id)
            ->where('date', '>=', now()->subDays(30))
            ->orderBy('date')
            ->get();

        return view('student.pages.gamification.statistics', compact(
            'stats',
            'levelInfo',
            'streakInfo',
            'pointsHistory',
            'pointsBySource',
            'monthlyActivity'
        ));
    }
}
