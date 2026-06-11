<?php

namespace App\Services\Gamification;

use App\Events\Gamification\LeaderboardRankChanged;
use App\Models\Leaderboard;
use App\Models\LeaderboardEntry;
use App\Models\PointsTransaction;
use App\Models\User;
use App\Models\UserStat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaderboardService
{
    public function __construct(
        protected LeaderboardCatalog $catalog
    ) {}

    public function updateAllLeaderboards(): array
    {
        $updated = [];

        foreach (Leaderboard::where('is_active', true)->get() as $leaderboard) {
            try {
                if ($this->updateLeaderboard($leaderboard)) {
                    $updated[] = $leaderboard->id;
                }
            } catch (\Exception $e) {
                Log::error('Failed to update leaderboard', [
                    'leaderboard_id' => $leaderboard->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $updated;
    }

    public function updateLeaderboard(Leaderboard $leaderboard): bool
    {
        try {
            return DB::transaction(function () use ($leaderboard) {
                $previousEntries = $leaderboard->entries()
                    ->get(['user_id', 'rank', 'score', 'division'])
                    ->keyBy('user_id');

                $leaderboard->entries()->delete();

                $users = $this->getUsersForLeaderboard($leaderboard);

                $rank = 1;
                $previousScore = null;
                $actualRank = 1;

                foreach ($users as $user) {
                    $score = (int) $user->score;

                    if ($score < ($leaderboard->min_score ?? 0)) {
                        continue;
                    }

                    if ($previousScore !== null && $score < $previousScore) {
                        $rank = $actualRank;
                    }

                    $division = $leaderboard->has_divisions
                        ? $this->calculateDivision($score, $leaderboard)
                        : 'bronze';

                    $prev = $previousEntries->get($user->id);
                    $previousRank = $prev?->rank;
                    $previousScoreVal = $prev?->score ?? 0;
                    $rankChange = $previousRank ? ($previousRank - $rank) : 0;

                    $entry = LeaderboardEntry::create([
                        'leaderboard_id' => $leaderboard->id,
                        'user_id' => $user->id,
                        'rank' => $rank,
                        'previous_rank' => $previousRank,
                        'rank_change' => $rankChange,
                        'score' => $score,
                        'previous_score' => $previousScoreVal,
                        'division' => $division,
                        'previous_division' => $prev?->division,
                        'metrics' => $this->getEntryMetrics($user),
                        'is_qualified' => true,
                        'last_activity_at' => now(),
                        'is_top_1' => $rank === 1,
                        'is_top_3' => $rank <= 3,
                        'is_top_10' => $rank <= 10,
                    ]);

                    if ($previousRank && $previousRank !== $rank) {
                        $rankUser = User::find($user->id);
                        if ($rankUser) {
                            event(new LeaderboardRankChanged(
                                $rankUser,
                                $leaderboard,
                                $previousRank,
                                $rank
                            ));
                        }
                    }

                    $previousScore = $score;
                    $actualRank++;
                }

                $leaderboard->update(['last_updated_at' => now()]);
                Cache::forget("leaderboard_{$leaderboard->id}");

                return true;
            });
        } catch (\Exception $e) {
            Log::error('Failed to update leaderboard', [
                'leaderboard_id' => $leaderboard->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function getUsersForLeaderboard(Leaderboard $leaderboard): Collection
    {
        $metric = $this->catalog->resolveMetric($leaderboard);
        $direction = $leaderboard->sort_direction === 'asc' ? 'asc' : 'desc';
        $limit = $leaderboard->max_entries ?? 100;

        if ($this->catalog->usesTransactionPeriod($leaderboard)) {
            return $this->getUsersFromTransactionPeriod($leaderboard, $direction, $limit);
        }

        $column = $this->statColumnForMetric($metric);

        $query = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'student'))
            ->where('is_active', true)
            ->whereHas('stats')
            ->join('user_stats', 'users.id', '=', 'user_stats.user_id');

        if ($metric === 'current_level') {
            $query->selectRaw('users.*, user_stats.current_level as score, user_stats.total_xp as tie_break')
                ->orderBy('score', $direction)
                ->orderByDesc('tie_break');
        } else {
            $query->selectRaw("users.*, user_stats.{$column} as score")
                ->orderBy('score', $direction);
        }

        return $query->limit($limit)->get();
    }

    protected function getUsersFromTransactionPeriod(Leaderboard $leaderboard, string $direction, int $limit): Collection
    {
        $since = match ($leaderboard->period) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            default => null,
        };

        $subquery = PointsTransaction::query()
            ->selectRaw('user_id, COALESCE(SUM(CASE WHEN points > 0 THEN points ELSE 0 END), 0) as score')
            ->when($since, fn ($q) => $q->where('created_at', '>=', $since))
            ->groupBy('user_id');

        return User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'student'))
            ->where('is_active', true)
            ->joinSub($subquery, 'period_scores', fn ($join) => $join->on('users.id', '=', 'period_scores.user_id'))
            ->selectRaw('users.*, period_scores.score as score')
            ->where('period_scores.score', '>', 0)
            ->orderBy('score', $direction)
            ->limit($limit)
            ->get();
    }

    protected function statColumnForMetric(string $metric): string
    {
        $allowed = array_keys(LeaderboardCatalog::METRICS);

        return in_array($metric, $allowed, true) ? $metric : 'total_points';
    }

    protected function calculateDivision(int $score, Leaderboard $leaderboard): string
    {
        $divisions = $leaderboard->division_thresholds
            ?? config('gamification.leaderboard.divisions', [
                'diamond' => ['min_points' => 50001],
                'platinum' => ['min_points' => 15001],
                'gold' => ['min_points' => 5001],
                'silver' => ['min_points' => 1001],
                'bronze' => ['min_points' => 0],
            ]);

        uasort($divisions, fn ($a, $b) => ($b['min_points'] ?? 0) <=> ($a['min_points'] ?? 0));

        foreach ($divisions as $division => $config) {
            if ($score >= ($config['min_points'] ?? 0)) {
                return $division;
            }
        }

        return 'bronze';
    }

    protected function getEntryMetrics($user): array
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

    public function getUserRank(User $user, Leaderboard $leaderboard): ?array
    {
        $entry = LeaderboardEntry::where('leaderboard_id', $leaderboard->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $entry) {
            return null;
        }

        $totalEntries = LeaderboardEntry::where('leaderboard_id', $leaderboard->id)->count();

        return [
            'rank' => $entry->rank,
            'score' => $entry->score,
            'division' => $entry->division,
            'rank_change' => $entry->rank_change,
            'total_participants' => $totalEntries,
            'percentile' => $totalEntries > 0
                ? round((($totalEntries - $entry->rank + 1) / $totalEntries) * 100, 2)
                : 0,
        ];
    }

    public function getLeaderboard(Leaderboard $leaderboard, int $limit = 50)
    {
        return Cache::remember("leaderboard_{$leaderboard->id}", 3600, function () use ($leaderboard, $limit) {
            return LeaderboardEntry::where('leaderboard_id', $leaderboard->id)
                ->with('user:id,name,email,avatar,name_ar,is_profile_public')
                ->orderBy('rank')
                ->limit($limit)
                ->get();
        });
    }

    public function getSurroundingUsers(User $user, Leaderboard $leaderboard, int $range = 5)
    {
        $userEntry = LeaderboardEntry::where('leaderboard_id', $leaderboard->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $userEntry) {
            return collect();
        }

        $minRank = max(1, $userEntry->rank - $range);
        $maxRank = $userEntry->rank + $range;

        return LeaderboardEntry::where('leaderboard_id', $leaderboard->id)
            ->whereBetween('rank', [$minRank, $maxRank])
            ->with('user:id,name,email,avatar,name_ar,is_profile_public')
            ->orderBy('rank')
            ->get();
    }

    public function getTopByDivision(Leaderboard $leaderboard, string $division, int $limit = 10)
    {
        return LeaderboardEntry::where('leaderboard_id', $leaderboard->id)
            ->where('division', $division)
            ->with('user:id,name,email,avatar,name_ar,is_profile_public')
            ->orderBy('rank')
            ->limit($limit)
            ->get();
    }

    public function awardLeaderboardRewards(Leaderboard $leaderboard): int
    {
        $awarded = 0;

        if (! $leaderboard->rewards || ! is_array($leaderboard->rewards)) {
            return $awarded;
        }

        $pointsService = app(PointsService::class);

        foreach ($leaderboard->rewards as $rankRange => $reward) {
            if (is_numeric($rankRange)) {
                $entry = LeaderboardEntry::where('leaderboard_id', $leaderboard->id)
                    ->where('rank', (int) $rankRange)
                    ->first();

                if ($entry && isset($reward['points'])) {
                    $pointsService->awardBonus(
                        $entry->user,
                        (int) $reward['points'],
                        "مكافأة المركز {$rankRange} في {$leaderboard->name}"
                    );
                    $awarded++;
                }
            } elseif ($rankRange === 'top_10') {
                $entries = LeaderboardEntry::where('leaderboard_id', $leaderboard->id)
                    ->whereBetween('rank', [1, 10])
                    ->get();

                foreach ($entries as $entry) {
                    if (isset($reward['points'])) {
                        $pointsService->awardBonus(
                            $entry->user,
                            (int) $reward['points'],
                            "مكافأة أفضل 10 في {$leaderboard->name}"
                        );
                        $awarded++;
                    }
                }
            }
        }

        return $awarded;
    }

    public function getLeaderboardStats(Leaderboard $leaderboard): array
    {
        $entries = LeaderboardEntry::where('leaderboard_id', $leaderboard->id)->get();

        return [
            'total_participants' => $entries->count(),
            'by_division' => $entries->groupBy('division')->map->count()->toArray(),
            'average_score' => $entries->avg('score'),
            'highest_score' => $entries->max('score'),
            'lowest_score' => $entries->min('score'),
            'last_updated' => $leaderboard->last_updated_at,
        ];
    }

    public function updateUserRanks(User $user): void
    {
        $stats = $user->stats;

        if (! $stats) {
            return;
        }

        $globalRank = UserStat::where('total_points', '>', $stats->total_points)->count() + 1;

        $stats->update(['global_rank' => $globalRank]);
    }
}
