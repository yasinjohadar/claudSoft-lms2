<?php

namespace App\Services\Gamification;

use App\Models\User;
use App\Models\DailyStreak;
use App\Models\UserStat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StreakService
{
    /**
     * تسجيل نشاط المستخدم اليومي
     */
    public function recordDailyActivity(User $user): array
    {
        try {
            return DB::transaction(function () use ($user) {
                $today = Carbon::today();
                $stats = $user->stats()->firstOrCreate(['user_id' => $user->id]);

                // التحقق من وجود سجل اليوم
                $todayStreak = DailyStreak::where('user_id', $user->id)
                    ->whereDate('date', $today)
                    ->first();

                if ($todayStreak) {
                    // تحديث نشاط اليوم فقط
                    $todayStreak->increment('activities_count');

                    return [
                        'already_recorded' => true,
                        'current_streak' => $stats->current_streak,
                        'is_new_streak_day' => false,
                    ];
                }

                // إنشاء سجل جديد لليوم
                $streak = DailyStreak::create([
                    'user_id' => $user->id,
                    'date' => $today,
                    'activities_count' => 1,
                    'points_earned' => 0,
                    'xp_earned' => 0,
                ]);

                // التحقق من استمرار السلسلة
                $yesterday = Carbon::yesterday();
                $yesterdayStreak = DailyStreak::where('user_id', $user->id)
                    ->whereDate('date', $yesterday)
                    ->exists();

                if ($yesterdayStreak) {
                    // استمرار السلسلة
                    $newStreak = $stats->current_streak + 1;
                } else {
                    // بداية سلسلة جديدة
                    $newStreak = 1;
                }

                // تحديث الإحصائيات
                $stats->update([
                    'current_streak' => $newStreak,
                    'longest_streak' => max($stats->longest_streak, $newStreak),
                    'total_active_days' => $stats->total_active_days + 1,
                    'last_activity_date' => now(),
                ]);

                // منح مكافآت السلسلة
                $this->awardStreakRewards($user, $newStreak);

                Log::info("Daily activity recorded", [
                    'user_id' => $user->id,
                    'current_streak' => $newStreak,
                    'is_new_record' => $newStreak == $stats->longest_streak,
                ]);

                return [
                    'already_recorded' => false,
                    'current_streak' => $newStreak,
                    'longest_streak' => $stats->longest_streak,
                    'is_new_record' => $newStreak == $stats->longest_streak,
                    'multiplier' => $this->getStreakMultiplier($newStreak),
                    'is_new_streak_day' => true,
                ];
            });
        } catch (\Exception $e) {
            Log::error("Failed to record daily activity", [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'already_recorded' => false,
                'current_streak' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * حساب مضاعف النقاط بناءً على السلسلة
     */
    public function getStreakMultiplier(int $streak): float
    {
        if ($streak < 3) return 1.0;
        if ($streak < 7) return 1.1;      // +10%
        if ($streak < 14) return 1.2;     // +20%
        if ($streak < 30) return 1.3;     // +30%
        if ($streak < 60) return 1.5;     // +50%
        if ($streak < 90) return 1.7;     // +70%
        if ($streak < 180) return 1.9;    // +90%

        return 2.0; // +100% for 180+ days
    }

    /**
     * منح مكافآت السلسلة
     */
    protected function awardStreakRewards(User $user, int $streak): void
    {
        $pointsService = app(PointsService::class);

        // مكافآت السلاسل المميزة
        $milestones = [
            7 => ['points' => 100, 'description' => 'إنجاز: أسبوع كامل من النشاط!'],
            14 => ['points' => 250, 'description' => 'إنجاز: أسبوعين متتاليين!'],
            30 => ['points' => 500, 'description' => 'إنجاز: شهر كامل من النشاط!'],
            60 => ['points' => 1000, 'description' => 'إنجاز: شهرين متتاليين!'],
            90 => ['points' => 2000, 'description' => 'إنجاز: 3 أشهر متتالية!'],
            180 => ['points' => 5000, 'description' => 'إنجاز: نصف سنة من النشاط المستمر!'],
            365 => ['points' => 10000, 'description' => 'إنجاز أسطوري: سنة كاملة!'],
        ];

        if (isset($milestones[$streak])) {
            $reward = $milestones[$streak];

            $pointsService->awardPoints(
                $user,
                $reward['points'],
                'streak_milestone',
                $reward['description'],
                'App\Models\DailyStreak',
                null
            );

            Log::info("Streak milestone reached", [
                'user_id' => $user->id,
                'streak' => $streak,
                'points_awarded' => $reward['points'],
            ]);
        }
    }

    /**
     * التحقق من كسر السلسلة
     */
    public function checkStreakBreak(User $user): bool
    {
        $stats = $user->stats;

        if (!$stats || $stats->current_streak == 0) {
            return false;
        }

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // التحقق من وجود نشاط أمس
        $yesterdayActivity = DailyStreak::where('user_id', $user->id)
            ->whereDate('date', $yesterday)
            ->exists();

        // التحقق من وجود نشاط اليوم
        $todayActivity = DailyStreak::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->exists();

        // إذا لم يكن هناك نشاط اليوم أو أمس
        if (!$yesterdayActivity && !$todayActivity) {
            // كسر السلسلة
            $this->breakStreak($user);
            return true;
        }

        return false;
    }

    /**
     * كسر السلسلة
     */
    protected function breakStreak(User $user): void
    {
        $stats = $user->stats;

        if (!$stats || $stats->current_streak == 0) {
            return;
        }

        Log::info("Streak broken", [
            'user_id' => $user->id,
            'broken_streak' => $stats->current_streak,
            'longest_streak' => $stats->longest_streak,
        ]);

        $stats->update([
            'current_streak' => 0,
        ]);
    }

    /**
     * الحصول على معلومات السلسلة للمستخدم
     */
    public function getStreakInfo(User $user): array
    {
        $stats = $user->stats()->firstOrCreate(['user_id' => $user->id]);

        $currentStreak = $stats->current_streak;
        $longestStreak = $stats->longest_streak;

        // الحصول على سجل آخر 7 أيام
        $last7Days = DailyStreak::where('user_id', $user->id)
            ->whereBetween('date', [Carbon::today()->subDays(6), Carbon::today()])
            ->orderBy('date', 'desc')
            ->get();

        // حساب المعلومات الإضافية
        $nextMilestone = $this->getNextMilestone($currentStreak);
        $currentMultiplier = $this->getStreakMultiplier($currentStreak);

        return [
            'current_streak' => $currentStreak,
            'longest_streak' => $longestStreak,
            'total_active_days' => $stats->total_active_days,
            'last_activity_date' => $stats->last_activity_date,
            'current_multiplier' => $currentMultiplier,
            'next_milestone' => $nextMilestone,
            'days_to_next_milestone' => $nextMilestone ? $nextMilestone - $currentStreak : null,
            'last_7_days' => $last7Days,
            'is_active_today' => $last7Days->where('date', Carbon::today()->format('Y-m-d'))->isNotEmpty(),
        ];
    }

    /**
     * الحصول على المعلم التالي
     */
    protected function getNextMilestone(int $currentStreak): ?int
    {
        $milestones = [7, 14, 30, 60, 90, 180, 365];

        foreach ($milestones as $milestone) {
            if ($currentStreak < $milestone) {
                return $milestone;
            }
        }

        return null; // وصل لكل المعالم
    }

    /**
     * الحصول على إحصائيات السلسلة للشهر الحالي
     */
    public function getMonthlyStreakStats(User $user): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $monthlyStreaks = DailyStreak::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date')
            ->get();

        $activeDays = $monthlyStreaks->count();
        $totalPoints = $monthlyStreaks->sum('points_earned');
        $totalXP = $monthlyStreaks->sum('xp_earned');
        $totalActivities = $monthlyStreaks->sum('activities_count');

        return [
            'month' => $startOfMonth->format('Y-m'),
            'active_days' => $activeDays,
            'total_points_earned' => $totalPoints,
            'total_xp_earned' => $totalXP,
            'total_activities' => $totalActivities,
            'streaks' => $monthlyStreaks,
            'completion_rate' => round(($activeDays / $endOfMonth->day) * 100, 2),
        ];
    }

    /**
     * تحديث نقاط وXP المكتسبة في اليوم
     */
    public function updateDailyEarnings(User $user, int $points, int $xp): void
    {
        $today = Carbon::today();

        DailyStreak::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->increment('points_earned', $points);

        DailyStreak::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->increment('xp_earned', $xp);
    }

    /**
     * إعادة تعيين السلاسل المنتهية (يتم تشغيله يومياً عبر Scheduler)
     */
    public function resetExpiredStreaks(): void
    {
        $twoDaysAgo = Carbon::today()->subDays(2);

        // الحصول على جميع المستخدمين الذين لديهم سلسلة نشطة
        $usersWithActiveStreaks = UserStat::where('current_streak', '>', 0)->get();

        foreach ($usersWithActiveStreaks as $stat) {
            $lastActivity = DailyStreak::where('user_id', $stat->user_id)
                ->whereDate('date', '>=', $twoDaysAgo)
                ->exists();

            if (!$lastActivity) {
                // كسر السلسلة
                $stat->update(['current_streak' => 0]);

                Log::info("Auto-reset expired streak", [
                    'user_id' => $stat->user_id,
                    'last_recorded_activity' => $stat->last_activity_date,
                ]);
            }
        }
    }
}
