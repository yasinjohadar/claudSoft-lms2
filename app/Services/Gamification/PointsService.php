<?php

namespace App\Services\Gamification;

use App\Models\User;
use App\Models\PointsTransaction;
use App\Models\UserStat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PointsService
{
    /**
     * Award points to a user
     *
     * @param User $user
     * @param int $points
     * @param string $source
     * @param string|null $description
     * @param string|null $relatedType
     * @param int|null $relatedId
     * @param float $multiplier
     * @return PointsTransaction|null
     */
    public function awardPoints(
        User $user,
        int $points,
        string $source,
        ?string $description = null,
        ?string $relatedType = null,
        ?int $relatedId = null,
        float $multiplier = 1.0
    ): ?PointsTransaction {
        try {
            return DB::transaction(function () use (
                $user,
                $points,
                $source,
                $description,
                $relatedType,
                $relatedId,
                $multiplier
            ) {
                // Get or create user stats
                $stats = $user->stats()->firstOrCreate(['user_id' => $user->id]);

                // Calculate final points with multiplier
                $finalPoints = (int) ($points * $multiplier);

                // Get current balance
                $balanceBefore = $stats->available_points;
                $balanceAfter = $balanceBefore + $finalPoints;

                // Create transaction record
                $transaction = PointsTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'earn',
                    'points' => $finalPoints,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'source' => $source,
                    'description' => $description,
                    'related_type' => $relatedType,
                    'related_id' => $relatedId,
                    'multiplier' => $multiplier,
                ]);

                // Update user stats
                $stats->update([
                    'total_points' => $stats->total_points + $finalPoints,
                    'available_points' => $balanceAfter,
                ]);

                return $transaction;
            });
        } catch (\Exception $e) {
            Log::error('Failed to award points: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'points' => $points,
                'source' => $source,
            ]);
            return null;
        }
    }

    /**
     * Deduct points from a user
     *
     * @param User $user
     * @param int $points
     * @param string $source
     * @param string|null $description
     * @param string|null $relatedType
     * @param int|null $relatedId
     * @return PointsTransaction|null
     */
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
                $stats = $user->stats;

                if (!$stats || $stats->available_points < $points) {
                    throw new \Exception('Insufficient points');
                }

                $balanceBefore = $stats->available_points;
                $balanceAfter = $balanceBefore - $points;

                // Create transaction record
                $transaction = PointsTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'spend',
                    'points' => -$points,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'source' => $source,
                    'description' => $description,
                    'related_type' => $relatedType,
                    'related_id' => $relatedId,
                ]);

                // Update user stats
                $stats->update([
                    'available_points' => $balanceAfter,
                    'spent_points' => $stats->spent_points + $points,
                ]);

                return $transaction;
            });
        } catch (\Exception $e) {
            Log::error('Failed to deduct points: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'points' => $points,
                'source' => $source,
            ]);
            return null;
        }
    }

    /**
     * Get user's available points
     *
     * @param User $user
     * @return int
     */
    public function getAvailablePoints(User $user): int
    {
        return $user->stats?->available_points ?? 0;
    }

    /**
     * Get user's total earned points
     *
     * @param User $user
     * @return int
     */
    public function getTotalPoints(User $user): int
    {
        return $user->stats?->total_points ?? 0;
    }

    /**
     * Get user's points history
     *
     * @param User $user
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPointsHistory(User $user, int $limit = 20)
    {
        return PointsTransaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get points multiplier based on user's streak
     *
     * @param User $user
     * @return float
     */
    public function getStreakMultiplier(User $user): float
    {
        $streak = $user->dailyStreak?->current_streak ?? 0;

        if ($streak >= 30) return 2.0;
        if ($streak >= 14) return 1.5;
        if ($streak >= 7) return 1.25;
        if ($streak >= 3) return 1.1;

        return 1.0;
    }

    /**
     * Award bonus points (with admin or system)
     *
     * @param User $user
     * @param int $points
     * @param string $reason
     * @param User|null $admin
     * @return PointsTransaction|null
     */
    public function awardBonus(
        User $user,
        int $points,
        string $reason,
        ?User $admin = null
    ): ?PointsTransaction {
        try {
            return DB::transaction(function () use ($user, $points, $reason, $admin) {
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
                    'admin_id' => $admin?->id,
                ]);

                $stats->update([
                    'total_points' => $stats->total_points + $points,
                    'available_points' => $balanceAfter,
                ]);

                return $transaction;
            });
        } catch (\Exception $e) {
            Log::error('Failed to award bonus: ' . $e->getMessage());
            return null;
        }
    }
}
