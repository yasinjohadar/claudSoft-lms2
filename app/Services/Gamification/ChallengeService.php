<?php

namespace App\Services\Gamification;

use App\Models\User;
use App\Models\Challenge;
use App\Models\UserChallenge;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ChallengeService
{
    protected PointsService $pointsService;
    protected BadgeService $badgeService;

    public function __construct(
        PointsService $pointsService,
        BadgeService $badgeService
    ) {
        $this->pointsService = $pointsService;
        $this->badgeService = $badgeService;
    }

    /**
     * تعيين تحدي للمستخدم
     */
    public function assignChallenge(User $user, Challenge $challenge): ?UserChallenge
    {
        try {
            // التحقق من عدم وجود التحدي مسبقاً
            $existing = UserChallenge::where('user_id', $user->id)
                ->where('challenge_id', $challenge->id)
                ->first();

            if ($existing) {
                return $existing;
            }

            // التحقق من عدد التحديات النشطة
            $activeCount = UserChallenge::where('user_id', $user->id)
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->count();

            $maxActive = config("gamification.challenges.{$challenge->type}.max_active", 3);

            if ($activeCount >= $maxActive) {
                Log::warning("User has too many active challenges", [
                    'user_id' => $user->id,
                    'active_count' => $activeCount,
                    'max_allowed' => $maxActive,
                ]);
                return null;
            }

            return UserChallenge::create([
                'user_id' => $user->id,
                'challenge_id' => $challenge->id,
                'status' => 'active',
                'current_progress' => 0,
                'progress_percentage' => 0,
                'started_at' => now(),
                'expires_at' => $this->calculateExpiryDate($challenge),
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to assign challenge", [
                'user_id' => $user->id,
                'challenge_id' => $challenge->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * حساب تاريخ انتهاء التحدي
     */
    protected function calculateExpiryDate(Challenge $challenge): Carbon
    {
        return match($challenge->type) {
            'daily' => now()->endOfDay(),
            'weekly' => now()->endOfWeek(),
            'monthly' => now()->endOfMonth(),
            default => now()->addDays(7),
        };
    }

    /**
     * تحديث تقدم المستخدم في تحدي
     */
    public function updateProgress(
        User $user,
        Challenge $challenge,
        int $incrementBy = 1
    ): ?UserChallenge {
        try {
            return DB::transaction(function () use ($user, $challenge, $incrementBy) {
                $userChallenge = UserChallenge::where('user_id', $user->id)
                    ->where('challenge_id', $challenge->id)
                    ->where('status', 'active')
                    ->first();

                if (!$userChallenge) {
                    return null;
                }

                // التحقق من عدم انتهاء الصلاحية
                if ($userChallenge->expires_at && $userChallenge->expires_at < now()) {
                    $this->expireChallenge($userChallenge);
                    return null;
                }

                $newProgress = $userChallenge->current_progress + $incrementBy;
                $targetValue = $challenge->target_value;
                $progressPercentage = min(100, ($newProgress / $targetValue) * 100);

                $userChallenge->update([
                    'current_progress' => $newProgress,
                    'progress_percentage' => round($progressPercentage, 2),
                ]);

                // التحقق من الإكمال
                if ($newProgress >= $targetValue) {
                    $this->completeChallenge($user, $userChallenge);
                }

                return $userChallenge->fresh();
            });

        } catch (\Exception $e) {
            Log::error("Failed to update challenge progress", [
                'user_id' => $user->id,
                'challenge_id' => $challenge->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * إكمال تحدي
     */
    protected function completeChallenge(User $user, UserChallenge $userChallenge): void
    {
        $challenge = $userChallenge->challenge;

        $userChallenge->update([
            'status' => 'completed',
            'progress_percentage' => 100,
            'completed_at' => now(),
        ]);

        // منح المكافآت
        $this->awardChallengeRewards($user, $challenge);

        // تحديث إحصائيات المستخدم
        $stats = $user->stats;
        $stats->increment('challenges_completed');

        if ($challenge->type === 'daily') {
            $stats->increment('daily_challenges_completed');
        } elseif ($challenge->type === 'weekly') {
            $stats->increment('weekly_challenges_completed');
        }

        Log::info("Challenge completed", [
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'challenge_name' => $challenge->name,
        ]);

        // إطلاق حدث إكمال التحدي
        // event(new ChallengeCompleted($user, $challenge));
    }

    /**
     * منح مكافآت التحدي
     */
    protected function awardChallengeRewards(User $user, Challenge $challenge): void
    {
        // منح النقاط
        if ($challenge->points_reward > 0) {
            $multiplier = config("gamification.challenges.{$challenge->type}.reward_multiplier", 1.0);
            $finalPoints = (int) ($challenge->points_reward * $multiplier);

            $this->pointsService->awardPoints(
                $user,
                $finalPoints,
                'challenge_completed',
                "إتمام تحدي: {$challenge->name}",
                'App\Models\Challenge',
                $challenge->id
            );
        }

        // منح XP
        if ($challenge->reward_xp > 0) {
            $levelService = app(LevelService::class);
            $levelService->awardXP(
                $user,
                $challenge->reward_xp,
                'challenge_completed',
                "إتمام تحدي: {$challenge->name}"
            );
        }

        // منح الشارة المرتبطة
        if ($challenge->badge_id) {
            $badge = $challenge->badge;
            if ($badge) {
                $this->badgeService->awardBadge(
                    $user,
                    $badge,
                    'App\Models\Challenge',
                    $challenge->id
                );
            }
        }

        // منح أحجار كريمة
        if ($challenge->reward_gems > 0) {
            $user->stats->increment('available_gems', $challenge->reward_gems);
        }
    }

    /**
     * انتهاء صلاحية تحدي
     */
    public function expireChallenge(UserChallenge $userChallenge): void
    {
        if ($userChallenge->status !== 'active') {
            return;
        }

        $userChallenge->update([
            'status' => 'expired',
        ]);

        Log::info("Challenge expired", [
            'user_id' => $userChallenge->user_id,
            'challenge_id' => $userChallenge->challenge_id,
            'progress' => $userChallenge->progress_percentage,
        ]);
    }

    /**
     * التحقق من وتحديث التحديات المنتهية
     */
    public function checkExpiredChallenges(): int
    {
        $expired = UserChallenge::where('status', 'active')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $userChallenge) {
            $this->expireChallenge($userChallenge);
        }

        return $expired->count();
    }

    /**
     * تعيين التحديات اليومية تلقائياً
     */
    public function assignDailyChallenges(User $user): array
    {
        $assigned = [];

        // الحصول على التحديات اليومية النشطة
        $dailyChallenges = Challenge::where('type', 'daily')
            ->where('is_active', true)
            ->where('auto_assign', true)
            ->get();

        foreach ($dailyChallenges as $challenge) {
            // التحقق من عدم وجود التحدي اليوم
            $today = today();
            $existsToday = UserChallenge::where('user_id', $user->id)
                ->where('challenge_id', $challenge->id)
                ->whereDate('started_at', $today)
                ->exists();

            if (!$existsToday) {
                $userChallenge = $this->assignChallenge($user, $challenge);
                if ($userChallenge) {
                    $assigned[] = $userChallenge;
                }
            }
        }

        return $assigned;
    }

    /**
     * الحصول على التحديات النشطة للمستخدم
     */
    public function getActiveChallenges(User $user, ?string $type = null)
    {
        $query = UserChallenge::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->with('challenge');

        if ($type) {
            $query->whereHas('challenge', function($q) use ($type) {
                $q->where('type', $type);
            });
        }

        return $query->orderBy('expires_at')->get();
    }

    /**
     * الحصول على التحديات المتاحة للمستخدم
     */
    public function getAvailableChallenges(User $user, ?string $type = null)
    {
        $query = Challenge::where('is_active', true);

        if ($type) {
            $query->where('type', $type);
        }

        $challenges = $query->orderBy('difficulty')
            ->orderBy('sort_order')
            ->get();

        // إضافة معلومات حالة التحدي للمستخدم
        foreach ($challenges as $challenge) {
            $userChallenge = UserChallenge::where('user_id', $user->id)
                ->where('challenge_id', $challenge->id)
                ->latest()
                ->first();

            $challenge->user_status = $userChallenge?->status ?? 'not_started';
            $challenge->user_progress = $userChallenge?->progress_percentage ?? 0;
            $challenge->user_challenge = $userChallenge;
        }

        return $challenges;
    }

    /**
     * قبول تحدي (للتحديات غير التلقائية)
     */
    public function acceptChallenge(User $user, Challenge $challenge): ?UserChallenge
    {
        // التحقق من أن التحدي ليس تلقائياً
        if ($challenge->auto_assign) {
            return null;
        }

        return $this->assignChallenge($user, $challenge);
    }

    /**
     * إلغاء تحدي
     */
    public function cancelChallenge(UserChallenge $userChallenge): bool
    {
        if ($userChallenge->status !== 'active') {
            return false;
        }

        $userChallenge->update(['status' => 'cancelled']);

        Log::info("Challenge cancelled", [
            'user_id' => $userChallenge->user_id,
            'challenge_id' => $userChallenge->challenge_id,
        ]);

        return true;
    }

    /**
     * إحصائيات التحديات للمستخدم
     */
    public function getUserChallengeStats(User $user): array
    {
        $stats = $user->stats;

        $activeChallenges = UserChallenge::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->count();

        $completedToday = UserChallenge::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereDate('completed_at', today())
            ->count();

        $completedThisWeek = UserChallenge::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        $completedThisMonth = UserChallenge::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $completionRate = 0;
        $totalStarted = UserChallenge::where('user_id', $user->id)
            ->whereIn('status', ['completed', 'expired'])
            ->count();

        if ($totalStarted > 0) {
            $completionRate = round(($stats->challenges_completed / $totalStarted) * 100, 2);
        }

        return [
            'total_completed' => $stats->challenges_completed,
            'daily_completed' => $stats->daily_challenges_completed,
            'weekly_completed' => $stats->weekly_challenges_completed,
            'active_challenges' => $activeChallenges,
            'completed_today' => $completedToday,
            'completed_this_week' => $completedThisWeek,
            'completed_this_month' => $completedThisMonth,
            'completion_rate' => $completionRate,
        ];
    }

    /**
     * الحصول على التحديات الموصى بها
     */
    public function getRecommendedChallenges(User $user, int $limit = 3)
    {
        $stats = $user->stats;

        // التحديات بناءً على مستوى الطالب
        $recommendedDifficulty = match(true) {
            $stats->current_level >= 30 => 'hard',
            $stats->current_level >= 15 => 'medium',
            default => 'easy',
        };

        return Challenge::where('is_active', true)
            ->where('difficulty', $recommendedDifficulty)
            ->whereDoesntHave('userChallenges', function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('status', 'active');
            })
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}
