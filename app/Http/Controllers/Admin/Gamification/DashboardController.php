<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Gamification\Badge;
use App\Models\Gamification\Achievement;
use App\Models\Gamification\PointTransaction as PointsTransaction;
use App\Models\Gamification\UserStat;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * عرض لوحة التحكم الرئيسية للتلعيب
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'week'); // today, week, month, year

        $startDate = match($period) {
            'today' => now()->startOfDay(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfWeek(),
        };

        // إحصائيات عامة
        $stats = [
            // النقاط
            'total_points_awarded' => PointsTransaction::where('points', '>', 0)->sum('points'),
            'total_points_spent' => abs(PointsTransaction::where('points', '<', 0)->sum('points')),
            'points_this_period' => PointsTransaction::where('points', '>', 0)
                ->where('created_at', '>=', $startDate)
                ->sum('points'),

            // المستخدمين النشطين
            'active_users' => UserStat::where('last_activity_date', '>=', $startDate)->count(),
            'total_users_with_points' => UserStat::where('total_points', '>', 0)->count(),

            // الشارات
            'total_badges' => Badge::count(),
            'active_badges' => Badge::where('is_active', true)->count(),
            'badges_awarded' => UserStat::sum('total_badges'),
            'badges_this_period' => \DB::table('gamification_user_badges')
                ->where('awarded_at', '>=', $startDate)
                ->count(),

            // الإنجازات
            'total_achievements' => Achievement::count(),
            'achievements_completed' => \DB::table('gamification_user_achievements')
                ->where('status', 'completed')
                ->count(),
            'achievements_this_period' => \DB::table('gamification_user_achievements')
                ->where('status', 'completed')
                ->where('completed_at', '>=', $startDate)
                ->count(),

            // المستويات
            'avg_user_level' => UserStat::avg('current_level'),
            'max_level_users' => UserStat::where('current_level', '>=', 50)->count(),

            // السلاسل
            'avg_streak' => UserStat::where('current_streak', '>', 0)->avg('current_streak'),
            'longest_streak' => UserStat::max('longest_streak'),
        ];

        // أكثر المصادر منحاً للنقاط
        $topPointSources = PointsTransaction::where('points', '>', 0)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('source, COUNT(*) as count, SUM(points) as total_points')
            ->groupBy('source')
            ->orderByDesc('total_points')
            ->limit(5)
            ->get();

        // أكثر الشارات منحاً
        $topBadges = Badge::withCount(['userBadges' => function ($query) use ($startDate) {
                $query->where('awarded_at', '>=', $startDate);
            }])
            ->orderByDesc('user_badges_count')
            ->limit(5)
            ->get();

        // أكثر المستخدمين نشاطاً
        $topUsers = User::whereHas('stats')
            ->with('stats')
            ->withCount(['pointsTransactions as period_points' => function ($query) use ($startDate) {
                $query->where('points', '>', 0)
                    ->where('created_at', '>=', $startDate)
                    ->selectRaw('COALESCE(SUM(points), 0)');
            }])
            ->orderByDesc('period_points')
            ->limit(10)
            ->get();

        // رسم بياني للنقاط اليومية
        $dailyPointsChart = PointsTransaction::where('created_at', '>=', $startDate)
            ->where('points', '>', 0)
            ->selectRaw('DATE(created_at) as date, SUM(points) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // رسم بياني للمستخدمين النشطين يومياً
        $dailyActiveUsersChart = UserStat::where('last_activity_date', '>=', $startDate)
            ->selectRaw('DATE(last_activity_date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.pages.gamification.dashboard', compact(
            'stats',
            'topPointSources',
            'topBadges',
            'topUsers',
            'dailyPointsChart',
            'dailyActiveUsersChart',
            'period'
        ));
    }

    /**
     * عرض إحصائيات متقدمة
     */
    public function analytics(Request $request)
    {
        $totalStudents = User::whereHas('roles', function($q) {
            $q->where('name', 'student');
        })->count();

        $activeStudents = User::whereHas('roles', function($q) {
            $q->where('name', 'student');
        })->where('is_active', true)->count();

        // إحصائيات النقاط
        $totalPointsAwarded = PointsTransaction::where('points', '>', 0)->sum('points');
        $totalPointsSpent = abs(PointsTransaction::where('points', '<', 0)->sum('points'));
        $avgPoints = UserStat::avg('total_points');
        $maxPoints = UserStat::max('total_points');

        // إحصائيات المستويات
        $avgLevel = UserStat::avg('current_level');
        $maxLevel = UserStat::max('current_level');

        // إحصائيات الشارات
        $totalBadges = Badge::count();
        $totalBadgesEarned = \DB::table('gamification_user_badges')->count();
        $avgBadgesPerUser = $totalStudents > 0 ? $totalBadgesEarned / $totalStudents : 0;

        // إحصائيات التحديات
        $activeChallenges = \DB::table('gamification_challenges')->where('is_active', true)->count();

        // معدل الاحتفاظ
        $activeLastWeek = UserStat::where('last_activity_date', '>=', now()->subDays(7))->count();
        $retentionRate = $totalStudents > 0 ? ($activeLastWeek / $totalStudents) * 100 : 0;

        $stats = [
            'overview' => [
                'total_students' => $totalStudents,
                'active_students' => $activeStudents,
            ],
            'points' => [
                'total' => $totalPointsAwarded,
                'spent' => $totalPointsSpent,
                'average' => $avgPoints,
                'highest' => $maxPoints,
                'today' => PointsTransaction::where('points', '>', 0)->whereDate('created_at', today())->sum('points'),
            ],
            'levels' => [
                'average' => $avgLevel,
                'highest' => $maxLevel,
                'level_ups_today' => 0,
                'level_ups_week' => 0,
            ],
            'badges' => [
                'total' => $totalBadges,
                'total_earned' => $totalBadgesEarned,
                'average_per_user' => $avgBadgesPerUser,
            ],
            'challenges' => [
                'active' => $activeChallenges,
                'completed' => 0,
                'completion_rate' => 0,
            ],
            'engagement' => [
                'retention_rate' => $retentionRate,
            ],
        ];

        return view('admin.pages.gamification.analytics.dashboard', compact('stats'));
    }
}
