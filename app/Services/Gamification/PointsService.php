<?php

namespace App\Services\Gamification;

use App\Events\Gamification\PointsEarned;
use App\Models\PointsTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PointsService
{
    /**
     * Sources that may only be awarded once per related entity (or once per day for daily_login).
     */
    protected array $duplicateOnceSources = [
        'lesson_completion',
        'video_watch',
        'quiz_completion',
        'perfect_score',
        'assignment_submission',
        'course_completion',
        'course_share',
        'referral',
        'streak_milestone',
        'badge_earned',
        'achievement_completed',
        'level_up',
        'challenge_completed',
        'daily_login',
    ];

    public function awardPoints(
        User $user,
        int $points,
        string $source,
        ?string $description = null,
        ?string $relatedType = null,
        ?int $relatedId = null,
        float $multiplier = 1.0,
        array $options = []
    ): ?PointsTransaction {
        if ($points <= 0) {
            return null;
        }

        $skipDuplicate = $options['skip_duplicate'] ?? false;
        $skipDailyLimit = $options['skip_daily_limit'] ?? false;

        if (! $skipDuplicate && ! $this->canAward($user, $source, $relatedType, $relatedId)) {
            Log::info('Points award skipped: duplicate', [
                'user_id' => $user->id,
                'source' => $source,
                'related_type' => $relatedType,
                'related_id' => $relatedId,
            ]);

            return null;
        }

        $finalPoints = (int) ($points * $multiplier);

        if (! $skipDailyLimit && ! $this->withinDailyLimit($user, $source, $finalPoints)) {
            Log::info('Points award skipped: daily limit', [
                'user_id' => $user->id,
                'source' => $source,
                'points' => $finalPoints,
            ]);

            return null;
        }

        try {
            $transaction = DB::transaction(function () use (
                $user,
                $points,
                $source,
                $description,
                $relatedType,
                $relatedId,
                $multiplier,
                $finalPoints
            ) {
                $stats = $user->stats()->firstOrCreate(['user_id' => $user->id]);

                $balanceBefore = $stats->available_points;
                $balanceAfter = $balanceBefore + $finalPoints;

                $transaction = PointsTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'earn',
                    'points' => $finalPoints,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'source' => $source,
                    'description' => $description,
                    'related_type' => $relatedType ?? User::class,
                    'related_id' => $relatedId ?? $user->id,
                    'multiplier' => $multiplier,
                ]);

                $stats->update([
                    'total_points' => $stats->total_points + $finalPoints,
                    'available_points' => $balanceAfter,
                ]);

                return $transaction;
            });
        } catch (\Exception $e) {
            Log::error('Failed to award points: '.$e->getMessage(), [
                'user_id' => $user->id,
                'points' => $points,
                'source' => $source,
            ]);

            return null;
        }

        if ($transaction) {
            try {
                PointsEarned::dispatch(
                    $user,
                    $finalPoints,
                    $source,
                    $relatedType,
                    $relatedId
                );
            } catch (\Exception $e) {
                Log::warning('PointsEarned listener failed: '.$e->getMessage(), [
                    'user_id' => $user->id,
                    'source' => $source,
                ]);
            }
        }

        return $transaction;
    }

    public function deductPoints(
        User $user,
        int $points,
        string $source,
        ?string $description = null,
        ?string $relatedType = null,
        ?int $relatedId = null
    ): ?PointsTransaction {
        try {
            return DB::transaction(function () use (
                $user,
                $points,
                $source,
                $description,
                $relatedType,
                $relatedId
            ) {
                $stats = $user->stats()->firstOrCreate(['user_id' => $user->id]);

                if ($stats->available_points < $points) {
                    throw new \Exception('Insufficient points');
                }

                $balanceBefore = $stats->available_points;
                $balanceAfter = $balanceBefore - $points;

                $transaction = PointsTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'spend',
                    'points' => -$points,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'source' => $source,
                    'description' => $description,
                    'related_type' => $relatedType ?? User::class,
                    'related_id' => $relatedId ?? $user->id,
                ]);

                $stats->update([
                    'available_points' => $balanceAfter,
                    'spent_points' => $stats->spent_points + $points,
                ]);

                return $transaction;
            });
        } catch (\Exception $e) {
            Log::error('Failed to deduct points: '.$e->getMessage(), [
                'user_id' => $user->id,
                'points' => $points,
                'source' => $source,
            ]);

            return null;
        }
    }

    public function canAward(User $user, string $source, ?string $relatedType = null, ?int $relatedId = null): bool
    {
        if ($source === 'daily_login') {
            return ! PointsTransaction::query()
                ->where('user_id', $user->id)
                ->where('source', 'daily_login')
                ->whereDate('created_at', today())
                ->exists();
        }

        if (! in_array($source, $this->duplicateOnceSources, true)) {
            return true;
        }

        if ($relatedType === null || $relatedId === null) {
            return true;
        }

        return ! PointsTransaction::query()
            ->where('user_id', $user->id)
            ->where('source', $source)
            ->where('related_type', $relatedType)
            ->where('related_id', $relatedId)
            ->whereIn('type', ['earn', 'bonus'])
            ->exists();
    }

    public function withinDailyLimit(User $user, string $source, int $pointsToAward): bool
    {
        $limits = config('gamification.daily_limits', []);
        $maxPerDay = (int) ($limits['max_points_per_day'] ?? 0);

        if ($maxPerDay > 0) {
            $earnedToday = (int) PointsTransaction::query()
                ->where('user_id', $user->id)
                ->where('points', '>', 0)
                ->whereDate('created_at', today())
                ->sum('points');

            if ($earnedToday + $pointsToAward > $maxPerDay) {
                return false;
            }
        }

        $activityLimit = $limits['specific_activities'][$source] ?? null;

        if ($activityLimit !== null) {
            $countToday = PointsTransaction::query()
                ->where('user_id', $user->id)
                ->where('source', $source)
                ->whereIn('type', ['earn', 'bonus'])
                ->whereDate('created_at', today())
                ->count();

            if ($countToday >= (int) $activityLimit) {
                return false;
            }
        }

        return true;
    }

    public function getAvailablePoints(User $user): int
    {
        return $user->stats?->available_points ?? 0;
    }

    public function getTotalPoints(User $user): int
    {
        return $user->stats?->total_points ?? 0;
    }

    public function getPointsHistory(User $user, int $limit = 20)
    {
        return PointsTransaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getStreakMultiplier(User $user): float
    {
        $streak = $user->stats?->current_streak ?? 0;

        return app(StreakService::class)->getStreakMultiplier($streak);
    }

    public function awardBonus(
        User $user,
        int $points,
        string $reason,
        ?User $admin = null
    ): ?PointsTransaction {
        if ($points <= 0) {
            return null;
        }

        try {
            $transaction = DB::transaction(function () use ($user, $points, $reason, $admin) {
                $stats = $user->stats()->firstOrCreate(['user_id' => $user->id]);

                $balanceBefore = $stats->available_points;
                $balanceAfter = $balanceBefore + $points;

                $transaction = PointsTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'bonus',
                    'points' => $points,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'source' => 'bonus',
                    'description' => $reason,
                    'related_type' => User::class,
                    'related_id' => $user->id,
                    'admin_id' => $admin?->id,
                ]);

                $stats->update([
                    'total_points' => $stats->total_points + $points,
                    'available_points' => $balanceAfter,
                ]);

                return $transaction;
            });
        } catch (\Exception $e) {
            Log::error('Failed to award bonus: '.$e->getMessage());

            return null;
        }

        if ($transaction) {
            try {
                PointsEarned::dispatch($user, $points, 'bonus');
            } catch (\Exception $e) {
                Log::warning('PointsEarned listener failed for bonus: '.$e->getMessage());
            }
        }

        return $transaction;
    }
}
