<?php

namespace App\Services\Gamification;

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
                $targetValue = $achievement->target_value;
                $progressPercentage = min(100, ($newValue / $targetValue) * 100);

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
        $stats = $user->stats;
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

        // إطلاق حدث إتمام الإنجاز
        // event(new AchievementUnlocked($user, $achievement));
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
            ->with('achievement.badge');

        if ($status) {
            $query->where('status', $status);
        }

        if ($tier) {
            $query->whereHas('achievement', function($q) use ($tier) {
                $q->where('tier', $tier);
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
            ->with('achievement.badge')
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
        $stats = $user->stats;

        $achievements = Achievement::where('is_active', true)->get();

        foreach ($achievements as $achievement) {
            $userAchievement = UserAchievement::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'achievement_id' => $achievement->id,
                ],
                [
                    'status' => 'in_progress',
                    'current_value' => 0,
                    'started_at' => now(),
                ]
            );

            if ($userAchievement->status === 'completed') {
                continue;
            }

            // الحصول على القيمة الحالية بناءً على معايير الإنجاز
            $currentValue = $this->getCurrentValueForAchievement($stats, $achievement);

            if ($currentValue != $userAchievement->current_value) {
                $userAchievement->update([
                    'current_value' => $currentValue,
                    'progress_percentage' => min(100, ($currentValue / $achievement->target_value) * 100),
                ]);

                if ($currentValue >= $achievement->target_value) {
                    $this->completeAchievement($user, $userAchievement);
                    $completed[] = $userAchievement->fresh();
                }
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

        return match($field) {
            'lessons_completed' => $stats->lessons_completed,
            'courses_completed' => $stats->courses_completed,
            'quizzes_completed' => $stats->quizzes_completed,
            'perfect_scores' => $stats->perfect_scores,
            'longest_streak' => $stats->longest_streak,
            'total_points' => $stats->total_points,
            'current_level' => $stats->current_level,
            'total_badges' => $stats->total_badges,
            'assignments_completed' => $stats->assignments_completed,
            default => 0,
        };
    }

    /**
     * إحصائيات إنجازات المستخدم
     */
    public function getUserAchievementStats(User $user): array
    {
        $totalAchievements = Achievement::where('is_active', true)->count();

        $completed = UserAchievement::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $inProgress = UserAchievement::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->count();

        $byTier = UserAchievement::where('user_id', $user->id)
            ->where('status', 'completed')
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
                ->where('status', 'completed')
                ->with('achievement')
                ->latest('completed_at')
                ->take(5)
                ->get(),
        ];
    }
}
