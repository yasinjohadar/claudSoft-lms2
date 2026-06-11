<?php

namespace App\Services\Gamification;

use App\Events\Gamification\AchievementUnlocked;
use App\Models\User;
use App\Models\Achievement;
use App\Models\UserAchievement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AchievementService
{
    protected BadgeService $badgeService;

    public function __construct(BadgeService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    /**
     * بدء تتبع إنجاز للمستخدم
     */
    public function startTracking(User $user, Achievement $achievement): ?UserAchievement
    {
        try {
            // التحقق من عدم وجود تتبع سابق
            $existing = UserAchievement::where('user_id', $user->id)
                ->where('achievement_id', $achievement->id)
                ->first();

            if ($existing) {
                return $existing;
            }

            return UserAchievement::create([
                'user_id' => $user->id,
                'achievement_id' => $achievement->id,
                'status' => 'in_progress',
                'current_value' => 0,
                'progress_percentage' => 0,
                'started_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to start tracking achievement", [
                'user_id' => $user->id,
                'achievement_id' => $achievement->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * تحديث تقدم المستخدم في إنجاز
     */
    public function updateProgress(
        User $user,
        Achievement $achievement,
        int $incrementBy = 1,
        ?string $relatedType = null,
        ?int $relatedId = null
    ): ?UserAchievement {
        try {
            return DB::transaction(function () use ($user, $achievement, $incrementBy, $relatedType, $relatedId) {
                $userAchievement = UserAchievement::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'achievement_id' => $achievement->id,
                    ],
                    [
                        'status' => 'in_progress',
                        'current_value' => 0,
                        'progress_percentage' => 0,
                        'started_at' => now(),
                    ]
                );

                // تخطي إذا تم إنجازه بالفعل
                if ($userAchievement->status === 'completed') {
                    return $userAchievement;
                }

                $newValue = $userAchievement->current_value + $incrementBy;
                $targetValue = max(1, (int) $achievement->target_value);
                $progressPercentage = $this->progressPercentage($newValue, $targetValue);

                $userAchievement->update([
                    'current_value' => $newValue,
                    'progress_percentage' => round($progressPercentage, 2),
                    'related_type' => $relatedType,
                    'related_id' => $relatedId,
                ]);

                // التحقق من الإنجاز
                if ($newValue >= $targetValue) {
                    $this->completeAchievement($user, $userAchievement);
                }

                return $userAchievement->fresh();
            });
        } catch (\Exception $e) {
            Log::error("Failed to update achievement progress", [
                'user_id' => $user->id,
                'achievement_id' => $achievement->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * إكمال إنجاز
     */
    protected function completeAchievement(User $user, UserAchievement $userAchievement): void
    {
        $achievement = $userAchievement->achievement;

        $userAchievement->update([
            'status' => 'completed',
            'progress_percentage' => 100,
            'completed_at' => now(),
        ]);

        // تحديث إحصائيات المستخدم
        $stats = $user->stats()->firstOrCreate(['user_id' => $user->id]);
        $stats->increment('total_achievements');

        // منح الشارة المرتبطة إن وُجدت
        if ($achievement->badge_id) {
            $badge = $achievement->badge;
            if ($badge) {
                $this->badgeService->awardBadge(
                    $user,
                    $badge,
                    'App\Models\Achievement',
                    $achievement->id
                );
            }
        }

        // منح نقاط الإنجاز
        if ($achievement->points_reward > 0) {
            $pointsService = app(PointsService::class);
            $pointsService->awardPoints(
                $user,
                $achievement->points_reward,
                'achievement_completed',
                "أنجزت: {$achievement->name}",
                'App\Models\Achievement',
                $achievement->id
            );
        }

        Log::info("Achievement completed", [
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
            'achievement_name' => $achievement->name,
            'tier' => $achievement->tier,
        ]);

        event(new AchievementUnlocked($user, $achievement));
    }

    /**
     * مطالبة بمكافأة الإنجاز
     */
    public function claimReward(User $user, UserAchievement $userAchievement): bool
    {
        if ($userAchievement->status !== 'completed') {
            return false;
        }

        if ($userAchievement->claimed_at) {
            return false; // تمت المطالبة بالفعل
        }

        try {
            $userAchievement->update([
                'claimed_at' => now(),
                'status' => 'claimed',
            ]);

            Log::info("Achievement reward claimed", [
                'user_id' => $user->id,
                'achievement_id' => $userAchievement->achievement_id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to claim achievement reward", [
                'user_id' => $user->id,
                'achievement_id' => $userAchievement->achievement_id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * الحصول على إنجازات المستخدم
     */
    public function getUserAchievements(User $user, ?string $status = null, ?string $tier = null)
    {
        $query = UserAchievement::where('user_id', $user->id)
            ->whereHas('achievement', function ($q) {
                $q->where('is_active', true);
            })
            ->with(['achievement' => function ($q) {
                $q->with('badge');
            }]);

        if ($status) {
            $query->where('status', $status);
        }

        if ($tier) {
            $query->whereHas('achievement', function ($q) use ($tier) {
                $q->where('is_active', true)->where('tier', $tier);
            });
        }

        return $query->orderByDesc('progress_percentage')
            ->orderByDesc('completed_at')
            ->get();
    }

    /**
     * الحصول على الإنجازات الموصى بها (قيد التقدم)
     */
    public function getRecommendedAchievements(User $user, int $limit = 5)
    {
        return UserAchievement::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->where('progress_percentage', '>=', 50)
            ->whereHas('achievement', function ($q) {
                $q->where('is_active', true);
            })
            ->with(['achievement' => function ($q) {
                $q->with('badge');
            }])
            ->orderByDesc('progress_percentage')
            ->limit($limit)
            ->get();
    }

    /**
     * بدء تتبع جميع الإنجازات المتاحة
     */
    public function initializeAllAchievements(User $user): int
    {
        $achievements = Achievement::where('is_active', true)->get();
        $initialized = 0;

        foreach ($achievements as $achievement) {
            $exists = UserAchievement::where('user_id', $user->id)
                ->where('achievement_id', $achievement->id)
                ->exists();

            if (!$exists) {
                $this->startTracking($user, $achievement);
                $initialized++;
            }
        }

        return $initialized;
    }

    /**
     * التحقق من وتحديث جميع الإنجازات
     */
    public function checkAllAchievements(User $user): array
    {
        $completed = [];
        $stats = $user->stats()->firstOrCreate(['user_id' => $user->id]);

        $achievements = Achievement::where('is_active', true)->get();

        foreach ($achievements as $achievement) {
            if (empty($achievement->criteria['field'] ?? null) || (int) $achievement->target_value <= 0) {
                continue;
            }

            $userAchievement = UserAchievement::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'achievement_id' => $achievement->id,
                ],
                [
                    'status' => 'in_progress',
                    'current_value' => 0,
                    'started_at' => now(),
                    'related_type' => null,
                    'related_id' => null,
                ]
            );

            if ($userAchievement->status === 'completed' || $userAchievement->status === 'claimed') {
                continue;
            }

            $currentValue = $this->getCurrentValueForAchievement($stats, $achievement);
            $targetValue = max(1, (int) $achievement->target_value);

            if ($currentValue != $userAchievement->current_value) {
                $userAchievement->update([
                    'current_value' => $currentValue,
                    'progress_percentage' => $this->progressPercentage($currentValue, $targetValue),
                ]);
            }

            if ($currentValue >= $targetValue) {
                $this->completeAchievement($user, $userAchievement->fresh());
                $completed[] = $userAchievement->fresh();
            }
        }

        return $completed;
    }

    /**
     * الحصول على القيمة الحالية للإنجاز
     */
    protected function getCurrentValueForAchievement($stats, Achievement $achievement): int
    {
        if (!$achievement->criteria) {
            return 0;
        }

        $criteria = $achievement->criteria;
        $field = $criteria['field'] ?? null;

        if (!$field) {
            return 0;
        }

        return match ($field) {
            'lessons_completed' => (int) ($stats->lessons_completed ?? 0),
            'courses_completed' => (int) ($stats->courses_completed ?? 0),
            'quizzes_completed' => (int) ($stats->quizzes_completed ?? 0),
            'perfect_scores' => (int) ($stats->perfect_scores ?? 0),
            'longest_streak' => (int) ($stats->longest_streak ?? 0),
            'current_streak' => (int) ($stats->current_streak ?? 0),
            'total_points' => (int) ($stats->total_points ?? 0),
            'current_level' => (int) ($stats->current_level ?? 0),
            'total_badges' => (int) ($stats->total_badges ?? 0),
            'assignments_submitted' => (int) ($stats->assignments_submitted ?? 0),
            default => 0,
        };
    }

    protected function progressPercentage(int $currentValue, int $targetValue): float
    {
        if ($targetValue <= 0) {
            return 0;
        }

        return round(min(100, ($currentValue / $targetValue) * 100), 2);
    }

    /**
     * إحصائيات إنجازات المستخدم
     */
    public function getUserAchievementStats(User $user): array
    {
        $totalAchievements = Achievement::where('is_active', true)->count();

        $completed = UserAchievement::where('user_id', $user->id)
            ->whereIn('status', ['completed', 'claimed'])
            ->count();

        $inProgress = UserAchievement::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->count();

        $byTier = UserAchievement::where('user_id', $user->id)
            ->whereIn('status', ['completed', 'claimed'])
            ->join('achievements', 'user_achievements.achievement_id', '=', 'achievements.id')
            ->selectRaw('achievements.tier, COUNT(*) as count')
            ->groupBy('achievements.tier')
            ->pluck('count', 'tier')
            ->toArray();

        return [
            'total_available' => $totalAchievements,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'completion_rate' => $totalAchievements > 0 ? round(($completed / $totalAchievements) * 100, 2) : 0,
            'by_tier' => $byTier,
            'latest_completed' => UserAchievement::where('user_id', $user->id)
                ->whereIn('status', ['completed', 'claimed'])
                ->with('achievement')
                ->latest('completed_at')
                ->take(5)
                ->get(),
        ];
    }
}
