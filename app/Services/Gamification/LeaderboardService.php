<?php

namespace App\Services\Gamification;

use App\Models\User;
use App\Models\Leaderboard;
use App\Models\LeaderboardEntry;
use App\Models\UserStat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LeaderboardService
{
    /**
     * تحديث جميع اللوحات
     */
    public function updateAllLeaderboards(): array
    {
        $updated = [];

        $leaderboards = Leaderboard::where('is_active', true)->get();

        foreach ($leaderboards as $leaderboard) {
            try {
                $this->updateLeaderboard($leaderboard);
                $updated[] = $leaderboard->id;
            } catch (\Exception $e) {
                Log::error("Failed to update leaderboard", [
                    'leaderboard_id' => $leaderboard->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $updated;
    }

    /**
     * تحديث لوحة متصدرين واحدة
     */
    public function updateLeaderboard(Leaderboard $leaderboard): bool
    {
        try {
            return DB::transaction(function () use ($leaderboard) {
                // حذف الإدخالات القديمة
                $leaderboard->entries()->delete();

                // الحصول على البيانات بناءً على النوع
                $users = $this->getUsersForLeaderboard($leaderboard);

                $rank = 1;
                $previousScore = null;
                $actualRank = 1;

                foreach ($users as $user) {
                    // حساب التقسيم (Division)
                    $division = $this->calculateDivision($user->score);

                    // التعامل مع التعادل
                    if ($previousScore !== null && $user->score < $previousScore) {
                        $rank = $actualRank;
                    }

                    LeaderboardEntry::create([
                        'leaderboard_id' => $leaderboard->id,
                        'user_id' => $user->id,
                        'rank' => $rank,
                        'score' => $user->score,
                        'division' => $division,
                        'metadata' => $this->getEntryMetadata($user, $leaderboard),
                    ]);

                    $previousScore = $user->score;
                    $actualRank++;
                }

                // تحديث وقت آخر تحديث
                $leaderboard->update(['last_updated_at' => now()]);

                // تنظيف الكاش
                Cache::forget("leaderboard_{$leaderboard->id}");

                Log::info("Leaderboard updated", [
                    'leaderboard_id' => $leaderboard->id,
                    'entries_count' => $actualRank - 1,
                ]);

                return true;
            });
        } catch (\Exception $e) {
            Log::error("Failed to update leaderboard", [
                'leaderboard_id' => $leaderboard->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * الحصول على المستخدمين للوحة حسب النوع
     */
    protected function getUsersForLeaderboard(Leaderboard $leaderboard)
    {
        $query = User::whereHas('stats')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id')
            ->where('users.role', 'student')
            ->where('users.is_active', true);

        // فلترة حسب النطاق الزمني
        $this->applyTimeScope($query, $leaderboard);

        // الترتيب حسب النوع
        switch ($leaderboard->type) {
            case 'global':
            case 'weekly':
            case 'monthly':
                $query->selectRaw('users.*, user_stats.total_points as score')
                    ->orderByDesc('score');
                break;

            case 'courses':
                $query->selectRaw('users.*, user_stats.courses_completed as score')
                    ->orderByDesc('score');
                break;

            case 'quizzes':
                $query->selectRaw('users.*, user_stats.quizzes_completed as score')
                    ->orderByDesc('score');
                break;

            case 'streaks':
                $query->selectRaw('users.*, user_stats.longest_streak as score')
                    ->orderByDesc('score');
                break;

            case 'badges':
                $query->selectRaw('users.*, user_stats.total_badges as score')
                    ->orderByDesc('score');
                break;

            case 'level':
                $query->selectRaw('users.*, user_stats.current_level as score, user_stats.total_xp as xp')
                    ->orderByDesc('score')
                    ->orderByDesc('xp');
                break;

            default:
                $query->selectRaw('users.*, user_stats.total_points as score')
                    ->orderByDesc('score');
                break;
        }

        return $query->limit($leaderboard->max_entries ?? 100)->get();
    }

    /**
     * تطبيق النطاق الزمني
     */
    protected function applyTimeScope($query, Leaderboard $leaderboard): void
    {
        if (!in_array($leaderboard->type, ['weekly', 'monthly'])) {
            return;
        }

        if ($leaderboard->type === 'weekly') {
            // النقاط الأسبوعية - نحتاج لحساب من جدول المعاملات
            // لكن للتبسيط سنستخدم الإحصائيات العامة الآن
        } elseif ($leaderboard->type === 'monthly') {
            // النقاط الشهرية
        }
    }

    /**
     * حساب التقسيم بناءً على النقاط
     */
    protected function calculateDivision(int $score): string
    {
        $divisions = config('gamification.leaderboard.divisions', [
            'diamond' => ['min_points' => 50001],
            'platinum' => ['min_points' => 15001],
            'gold' => ['min_points' => 5001],
            'silver' => ['min_points' => 1001],
            'bronze' => ['min_points' => 0],
        ]);

        foreach ($divisions as $division => $config) {
            if ($score >= $config['min_points']) {
                return $division;
            }
        }

        return 'bronze';
    }

    /**
     * الحصول على metadata للإدخال
     */
    protected function getEntryMetadata($user, Leaderboard $leaderboard): array
    {
        $stats = $user->stats ?? UserStat::where('user_id', $user->id)->first();

        return [
            'total_points' => $stats->total_points ?? 0,
            'current_level' => $stats->current_level ?? 1,
            'total_badges' => $stats->total_badges ?? 0,
            'current_streak' => $stats->current_streak ?? 0,
            'courses_completed' => $stats->courses_completed ?? 0,
        ];
    }

    /**
     * الحصول على ترتيب المستخدم في لوحة
     */
    public function getUserRank(User $user, Leaderboard $leaderboard): ?array
    {
        $entry = LeaderboardEntry::where('leaderboard_id', $leaderboard->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$entry) {
            return null;
        }

        $totalEntries = LeaderboardEntry::where('leaderboard_id', $leaderboard->id)->count();

        return [
            'rank' => $entry->rank,
            'score' => $entry->score,
            'division' => $entry->division,
            'total_participants' => $totalEntries,
            'percentile' => $totalEntries > 0 ? round((($totalEntries - $entry->rank + 1) / $totalEntries) * 100, 2) : 0,
        ];
    }

    /**
     * الحصول على اللوحة مع الإدخالات
     */
    public function getLeaderboard(Leaderboard $leaderboard, int $limit = 50)
    {
        return Cache::remember("leaderboard_{$leaderboard->id}", 3600, function () use ($leaderboard, $limit) {
            return LeaderboardEntry::where('leaderboard_id', $leaderboard->id)
                ->with('user:id,name,email,avatar')
                ->orderBy('rank')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * الحصول على المستخدمين المحيطين بمستخدم معين
     */
    public function getSurroundingUsers(User $user, Leaderboard $leaderboard, int $range = 5)
    {
        $userEntry = LeaderboardEntry::where('leaderboard_id', $leaderboard->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$userEntry) {
            return collect();
        }

        $minRank = max(1, $userEntry->rank - $range);
        $maxRank = $userEntry->rank + $range;

        return LeaderboardEntry::where('leaderboard_id', $leaderboard->id)
            ->whereBetween('rank', [$minRank, $maxRank])
            ->with('user:id,name,email,avatar')
            ->orderBy('rank')
            ->get();
    }

    /**
     * الحصول على أفضل المستخدمين في تقسيم معين
     */
    public function getTopByDivision(Leaderboard $leaderboard, string $division, int $limit = 10)
    {
        return LeaderboardEntry::where('leaderboard_id', $leaderboard->id)
            ->where('division', $division)
            ->with('user:id,name,email,avatar')
            ->orderBy('rank')
            ->limit($limit)
            ->get();
    }

    /**
     * منح مكافآت اللوحة للفائزين
     */
    public function awardLeaderboardRewards(Leaderboard $leaderboard): int
    {
        $awarded = 0;

        if (!$leaderboard->rewards || !is_array($leaderboard->rewards)) {
            return $awarded;
        }

        $pointsService = app(PointsService::class);

        foreach ($leaderboard->rewards as $rankRange => $reward) {
            if (is_numeric($rankRange)) {
                // ترتيب محدد (مثل 1، 2، 3)
                $entry = LeaderboardEntry::where('leaderboard_id', $leaderboard->id)
                    ->where('rank', $rankRange)
                    ->first();

                if ($entry && isset($reward['points'])) {
                    $pointsService->awardPoints(
                        $entry->user,
                        $reward['points'],
                        'leaderboard_reward',
                        "مكافأة المركز {$rankRange} في {$leaderboard->name}",
                        'App\Models\Leaderboard',
                        $leaderboard->id
                    );

                    if (isset($reward['gems'])) {
                        $entry->user->stats->increment('available_gems', $reward['gems']);
                    }

                    $awarded++;
                }
            } elseif ($rankRange === 'top_10') {
                // أفضل 10
                $entries = LeaderboardEntry::where('leaderboard_id', $leaderboard->id)
                    ->whereBetween('rank', [1, 10])
                    ->get();

                foreach ($entries as $entry) {
                    if (isset($reward['points'])) {
                        $pointsService->awardPoints(
                            $entry->user,
                            $reward['points'],
                            'leaderboard_reward',
                            "مكافأة أفضل 10 في {$leaderboard->name}",
                            'App\Models\Leaderboard',
                            $leaderboard->id
                        );

                        if (isset($reward['gems'])) {
                            $entry->user->stats->increment('available_gems', $reward['gems']);
                        }

                        $awarded++;
                    }
                }
            }
        }

        Log::info("Leaderboard rewards awarded", [
            'leaderboard_id' => $leaderboard->id,
            'users_awarded' => $awarded,
        ]);

        return $awarded;
    }

    /**
     * إحصائيات اللوحة
     */
    public function getLeaderboardStats(Leaderboard $leaderboard): array
    {
        $entries = LeaderboardEntry::where('leaderboard_id', $leaderboard->id)->get();

        $byDivision = $entries->groupBy('division')
            ->map(fn($group) => $group->count())
            ->toArray();

        return [
            'total_participants' => $entries->count(),
            'by_division' => $byDivision,
            'average_score' => $entries->avg('score'),
            'highest_score' => $entries->max('score'),
            'lowest_score' => $entries->min('score'),
            'last_updated' => $leaderboard->last_updated_at,
        ];
    }

    /**
     * تحديث ترتيب المستخدم في الإحصائيات
     */
    public function updateUserRanks(User $user): void
    {
        $stats = $user->stats;

        if (!$stats) {
            return;
        }

        // الترتيب العام
        $globalRank = UserStat::where('total_points', '>', $stats->total_points)->count() + 1;

        // الترتيب الأسبوعي (مبسط - يحتاج تطوير)
        $weeklyRank = $globalRank;

        // الترتيب الشهري (مبسط - يحتاج تطوير)
        $monthlyRank = $globalRank;

        $stats->update([
            'global_rank' => $globalRank,
            'weekly_rank' => $weeklyRank,
            'monthly_rank' => $monthlyRank,
        ]);
    }
}
