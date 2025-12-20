<?php

namespace App\Http\Controllers\Student\Gamification;

use App\Http\Controllers\Controller;
use App\Services\Gamification\StreakService;
use Illuminate\Http\Request;

class StreakController extends Controller
{
    protected StreakService $streakService;

    public function __construct(StreakService $streakService)
    {
        $this->streakService = $streakService;
    }

    /**
     * عرض صفحة السلسلة اليومية
     */
    public function index()
    {
        $user = auth()->user();

        // معلومات السلسلة
        $streakInfo = $this->streakService->getStreakInfo($user);

        // إحصائيات الشهر الحالي
        $monthlyStats = $this->streakService->getMonthlyStreakStats($user);

        // مكافآت السلاسل
        $streakRewards = config('gamification.points.streak_milestones');

        return view('student.pages.gamification.streak', compact(
            'streakInfo',
            'monthlyStats',
            'streakRewards'
        ));
    }

    /**
     * عرض التقويم الشهري
     */
    public function calendar(Request $request)
    {
        $user = auth()->user();

        // الشهر المطلوب (افتراضياً الشهر الحالي)
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // الحصول على جميع أيام النشاط في الشهر
        $activityDays = \DB::table('daily_streaks')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy('date');

        // بناء التقويم
        $calendar = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $calendar[$dateKey] = [
                'date' => $currentDate->copy(),
                'is_active' => isset($activityDays[$dateKey]),
                'activities_count' => $activityDays[$dateKey]->activities_count ?? 0,
                'points_earned' => $activityDays[$dateKey]->points_earned ?? 0,
                'xp_earned' => $activityDays[$dateKey]->xp_earned ?? 0,
            ];
            $currentDate->addDay();
        }

        return view('student.pages.gamification.streak.calendar', compact(
            'calendar',
            'year',
            'month'
        ));
    }

    /**
     * عرض سجل السلاسل
     */
    public function history()
    {
        $user = auth()->user();

        // آخر 90 يوم
        $history = \DB::table('daily_streaks')
            ->where('user_id', $user->id)
            ->where('date', '>=', now()->subDays(90))
            ->orderByDesc('date')
            ->paginate(30);

        return view('student.pages.gamification.streak.history', compact('history'));
    }
}
