<?php

namespace App\Services\Gamification;

use App\Models\User;
use App\Models\UserStat;
use App\Models\ExperienceLevel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LevelService
{
    /**
     * منح XP للمستخدم مع التحقق من ترقية المستوى
     */
    public function awardXP(
        User $user,
        int $xp,
        string $source,
        ?string $description = null
    ): bool {
        try {
            return DB::transaction(function () use ($user, $xp, $source, $description) {
                $stats = $user->stats()->firstOrCreate(['user_id' => $user->id]);

                $oldXP = $stats->total_xp;
                $oldLevel = $stats->current_level;
                $newXP = $oldXP + $xp;

                // حساب المستوى الجديد بناءً على XP
                $newLevel = $this->calculateLevel($newXP);

                // تحديث الإحصائيات
                $stats->update([
                    'total_xp' => $newXP,
                    'current_level' => $newLevel,
                    'current_level_xp' => $this->getCurrentLevelXP($newXP, $newLevel),
                    'next_level_xp' => $this->getRequiredXPForLevel($newLevel + 1),
                    'level_progress' => $this->getLevelProgress($newXP, $newLevel),
                ]);

                // إذا تمت الترقية
                if ($newLevel > $oldLevel) {
                    $this->handleLevelUp($user, $oldLevel, $newLevel);
                }

                Log::info("XP Awarded", [
                    'user_id' => $user->id,
                    'xp' => $xp,
                    'source' => $source,
                    'old_level' => $oldLevel,
                    'new_level' => $newLevel,
                ]);

                return true;
            });
        } catch (\Exception $e) {
            Log::error("Failed to award XP", [
                'user_id' => $user->id,
                'xp' => $xp,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * حساب المستوى بناءً على إجمالي XP
     */
    public function calculateLevel(int $totalXP): int
    {
        // استخدام الكاش لتحسين الأداء
        $levels = Cache::remember('experience_levels', 3600, function () {
            return ExperienceLevel::orderBy('level')->get();
        });

        $currentLevel = 1;

        foreach ($levels as $level) {
            if ($totalXP >= $level->xp_required) {
                $currentLevel = $level->level;
            } else {
                break;
            }
        }

        return min($currentLevel, 50); // Max level 50
    }

    /**
     * الحصول على XP المطلوب للوصول لمستوى معين
     */
    public function getRequiredXPForLevel(int $level): int
    {
        if ($level <= 1) return 0;
        if ($level > 50) return PHP_INT_MAX;

        $experienceLevel = Cache::remember("level_{$level}_xp", 3600, function () use ($level) {
            return ExperienceLevel::where('level', $level)->first();
        });

        return $experienceLevel ? $experienceLevel->xp_required : 0;
    }

    /**
     * الحصول على XP الحالي في المستوى
     */
    public function getCurrentLevelXP(int $totalXP, int $currentLevel): int
    {
        $currentLevelRequired = $this->getRequiredXPForLevel($currentLevel);
        return $totalXP - $currentLevelRequired;
    }

    /**
     * حساب نسبة التقدم في المستوى الحالي (0-100)
     */
    public function getLevelProgress(int $totalXP, int $currentLevel): float
    {
        if ($currentLevel >= 50) return 100.0;

        $currentLevelXP = $this->getRequiredXPForLevel($currentLevel);
        $nextLevelXP = $this->getRequiredXPForLevel($currentLevel + 1);
        $xpNeededForNextLevel = $nextLevelXP - $currentLevelXP;

        if ($xpNeededForNextLevel <= 0) return 100.0;

        $currentProgress = $totalXP - $currentLevelXP;
        $progress = ($currentProgress / $xpNeededForNextLevel) * 100;

        return round(min(max($progress, 0), 100), 2);
    }

    /**
     * معالجة ترقية المستوى
     */
    protected function handleLevelUp(User $user, int $oldLevel, int $newLevel): void
    {
        $stats = $user->stats;

        // حساب عدد المستويات المكتسبة
        $levelsGained = $newLevel - $oldLevel;

        // تحديث إحصائيات الترقيات
        $stats->increment('total_level_ups', $levelsGained);

        // جوائز الترقية
        $this->awardLevelUpRewards($user, $newLevel);

        // إطلاق حدث الترقية (سيتم استخدامه لاحقاً)
        // event(new UserLeveledUp($user, $oldLevel, $newLevel));

        Log::info("User Leveled Up", [
            'user_id' => $user->id,
            'old_level' => $oldLevel,
            'new_level' => $newLevel,
            'levels_gained' => $levelsGained,
        ]);
    }

    /**
     * منح مكافآت الترقية
     */
    protected function awardLevelUpRewards(User $user, int $newLevel): void
    {
        // الحصول على معلومات المستوى
        $levelData = ExperienceLevel::where('level', $newLevel)->first();

        if (!$levelData) return;

        $stats = $user->stats;

        // منح نقاط مكافأة الترقية
        if ($levelData->points_reward > 0) {
            $pointsService = app(PointsService::class);
            $pointsService->awardPoints(
                $user,
                $levelData->points_reward,
                'level_up',
                "مكافأة الوصول للمستوى {$newLevel}",
                'App\Models\ExperienceLevel',
                $levelData->id
            );
        }

        // منح أحجار كريمة (Gems)
        if ($levelData->reward_gems > 0) {
            $stats->increment('total_gems', $levelData->reward_gems);
            $stats->increment('available_gems', $levelData->reward_gems);
        }

        // منح شارات خاصة عند مستويات معينة
        $this->checkAndAwardLevelBadges($user, $newLevel);
    }

    /**
     * التحقق من شارات المستويات ومنحها
     */
    protected function checkAndAwardLevelBadges(User $user, int $level): void
    {
        // شارات المستويات المميزة
        $levelMilestones = [
            10 => 'level-10-badge',
            25 => 'level-25-badge',
            50 => 'level-50-badge',
        ];

        if (isset($levelMilestones[$level])) {
            // سيتم ربطها مع BadgeService لاحقاً
            Log::info("Level milestone reached", [
                'user_id' => $user->id,
                'level' => $level,
                'badge_slug' => $levelMilestones[$level],
            ]);
        }
    }

    /**
     * الحصول على معلومات المستوى الحالي للمستخدم
     */
    public function getUserLevelInfo(User $user): array
    {
        $stats = $user->stats()->firstOrCreate(['user_id' => $user->id]);

        $currentLevel = $stats->current_level;
        $totalXP = $stats->total_xp;

        return [
            'current_level' => $currentLevel,
            'total_xp' => $totalXP,
            'current_level_xp' => $this->getCurrentLevelXP($totalXP, $currentLevel),
            'next_level_xp' => $this->getRequiredXPForLevel($currentLevel + 1),
            'xp_needed' => max(0, $this->getRequiredXPForLevel($currentLevel + 1) - $totalXP),
            'level_progress' => $this->getLevelProgress($totalXP, $currentLevel),
            'total_level_ups' => $stats->total_level_ups,
            'level_data' => ExperienceLevel::where('level', $currentLevel)->first(),
            'is_max_level' => $currentLevel >= 50,
        ];
    }

    /**
     * الحصول على قائمة كل المستويات
     */
    public function getAllLevels(): \Illuminate\Support\Collection
    {
        return Cache::remember('all_experience_levels', 3600, function () {
            return ExperienceLevel::orderBy('level')->get();
        });
    }

    /**
     * حساب الوقت المتوقع للوصول للمستوى التالي
     */
    public function estimateTimeToNextLevel(User $user): ?array
    {
        $stats = $user->stats;
        if (!$stats || $stats->current_level >= 50) {
            return null;
        }

        // حساب متوسط XP اليومي خلال آخر 7 أيام
        $sevenDaysAgo = now()->subDays(7);
        $recentXP = DB::table('points_transactions')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $sevenDaysAgo)
            ->sum('points');

        $averageXPPerDay = $recentXP / 7;

        if ($averageXPPerDay <= 0) {
            return null;
        }

        $xpNeeded = $this->getRequiredXPForLevel($stats->current_level + 1) - $stats->total_xp;
        $estimatedDays = ceil($xpNeeded / $averageXPPerDay);

        return [
            'xp_needed' => $xpNeeded,
            'average_xp_per_day' => round($averageXPPerDay, 2),
            'estimated_days' => $estimatedDays,
            'estimated_date' => now()->addDays($estimatedDays)->format('Y-m-d'),
        ];
    }
}
