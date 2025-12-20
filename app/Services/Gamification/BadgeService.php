<?php

namespace App\Services\Gamification;

use App\Models\User;
use App\Models\Badge;
use App\Models\UserBadge;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BadgeService
{
    /**
     * منح شارة للمستخدم
     */
    public function awardBadge(
        User $user,
        Badge $badge,
        ?string $relatedType = null,
        ?int $relatedId = null,
        array $metadata = []
    ): ?UserBadge {
        try {
            // التحقق من عدم حصول المستخدم على الشارة مسبقاً
            if ($this->userHasBadge($user, $badge)) {
                Log::info("User already has badge", [
                    'user_id' => $user->id,
                    'badge_id' => $badge->id,
                ]);
                return null;
            }

            return DB::transaction(function () use ($user, $badge, $relatedType, $relatedId, $metadata) {
                // إنشاء سجل الشارة
                $userBadge = UserBadge::create([
                    'user_id' => $user->id,
                    'badge_id' => $badge->id,
                    'awarded_at' => now(),
                    'related_type' => $relatedType,
                    'related_id' => $relatedId,
                    'metadata' => $metadata,
                ]);

                // تحديث إحصائيات المستخدم
                $stats = $user->stats;
                $stats->increment('total_badges');

                // إحصائيات حسب الندرة
                $rarityField = match($badge->rarity) {
                    'common' => 'common_badges',
                    'rare' => 'rare_badges',
                    'epic' => 'epic_badges',
                    'legendary' => 'legendary_badges',
                    'mythic' => 'mythic_badges',
                    default => null,
                };

                if ($rarityField && $stats->hasAttribute($rarityField)) {
                    $stats->increment($rarityField);
                }

                // منح نقاط الشارة إن وُجدت
                if ($badge->points_value > 0) {
                    $pointsService = app(PointsService::class);
                    $pointsService->awardPoints(
                        $user,
                        $badge->points_value,
                        'badge_earned',
                        "حصلت على شارة: {$badge->name}",
                        'App\Models\Badge',
                        $badge->id
                    );
                }

                Log::info("Badge awarded", [
                    'user_id' => $user->id,
                    'badge_id' => $badge->id,
                    'badge_name' => $badge->name,
                    'badge_rarity' => $badge->rarity,
                    'points_awarded' => $badge->points_value,
                ]);

                // إطلاق حدث منح الشارة
                // event(new BadgeEarned($user, $badge));

                return $userBadge;
            });
        } catch (\Exception $e) {
            Log::error("Failed to award badge", [
                'user_id' => $user->id,
                'badge_id' => $badge->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * التحقق من حصول المستخدم على شارة
     */
    public function userHasBadge(User $user, Badge $badge): bool
    {
        return UserBadge::where('user_id', $user->id)
            ->where('badge_id', $badge->id)
            ->exists();
    }

    /**
     * التحقق من معايير الشارة ومنحها إن تحققت
     */
    public function checkAndAwardBadge(User $user, string $badgeSlug): ?UserBadge
    {
        $badge = Badge::where('slug', $badgeSlug)
            ->where('is_active', true)
            ->first();

        if (!$badge) {
            return null;
        }

        // التحقق من عدم حصول المستخدم عليها مسبقاً
        if ($this->userHasBadge($user, $badge)) {
            return null;
        }

        // التحقق من المعايير
        if ($this->checkBadgeCriteria($user, $badge)) {
            return $this->awardBadge($user, $badge);
        }

        return null;
    }

    /**
     * التحقق من معايير الشارة
     */
    protected function checkBadgeCriteria(User $user, Badge $badge): bool
    {
        if (!$badge->criteria) {
            return true; // إذا لم تكن هناك معايير، منح الشارة
        }

        $criteria = $badge->criteria;
        $stats = $user->stats;

        // معايير مختلفة حسب النوع
        foreach ($criteria as $key => $value) {
            switch ($key) {
                case 'lessons_completed':
                    if ($stats->lessons_completed < $value) return false;
                    break;

                case 'courses_completed':
                    if ($stats->courses_completed < $value) return false;
                    break;

                case 'quizzes_completed':
                    if ($stats->quizzes_completed < $value) return false;
                    break;

                case 'perfect_scores':
                    if ($stats->perfect_scores < $value) return false;
                    break;

                case 'current_streak':
                    if ($stats->current_streak < $value) return false;
                    break;

                case 'total_points':
                    if ($stats->total_points < $value) return false;
                    break;

                case 'current_level':
                    if ($stats->current_level < $value) return false;
                    break;

                case 'total_badges':
                    if ($stats->total_badges < $value) return false;
                    break;

                case 'assignments_completed':
                    if ($stats->assignments_completed < $value) return false;
                    break;

                default:
                    // معايير مخصصة
                    break;
            }
        }

        return true;
    }

    /**
     * التحقق من جميع الشارات المتاحة للمستخدم
     */
    public function checkAllBadges(User $user): array
    {
        $awarded = [];

        // الحصول على جميع الشارات النشطة
        $badges = Badge::where('is_active', true)
            ->where('is_visible', true)
            ->get();

        foreach ($badges as $badge) {
            // تخطي الشارات المخفية (Hidden)
            if ($badge->is_hidden && !$this->userHasBadge($user, $badge)) {
                continue;
            }

            $userBadge = $this->checkAndAwardBadge($user, $badge->slug);
            if ($userBadge) {
                $awarded[] = $userBadge;
            }
        }

        return $awarded;
    }

    /**
     * الحصول على شارات المستخدم
     */
    public function getUserBadges(User $user, ?string $type = null, ?string $rarity = null)
    {
        $query = UserBadge::where('user_id', $user->id)
            ->with('badge')
            ->latest('awarded_at');

        if ($type) {
            $query->whereHas('badge', function($q) use ($type) {
                $q->where('type', $type);
            });
        }

        if ($rarity) {
            $query->whereHas('badge', function($q) use ($rarity) {
                $q->where('rarity', $rarity);
            });
        }

        return $query->get();
    }

    /**
     * الحصول على تقدم المستخدم نحو شارة معينة
     */
    public function getBadgeProgress(User $user, Badge $badge): array
    {
        if ($this->userHasBadge($user, $badge)) {
            return [
                'earned' => true,
                'progress' => 100,
                'awarded_at' => UserBadge::where('user_id', $user->id)
                    ->where('badge_id', $badge->id)
                    ->first()->earned_at,
            ];
        }

        if (!$badge->criteria) {
            return [
                'earned' => false,
                'progress' => 0,
                'requirements' => [],
            ];
        }

        $stats = $user->stats;
        $criteria = $badge->criteria;
        $requirements = [];
        $totalProgress = 0;
        $criteriaCount = count($criteria);

        foreach ($criteria as $key => $required) {
            $current = match($key) {
                'lessons_completed' => $stats->lessons_completed,
                'courses_completed' => $stats->courses_completed,
                'quizzes_completed' => $stats->quizzes_completed,
                'perfect_scores' => $stats->perfect_scores,
                'current_streak' => $stats->current_streak,
                'total_points' => $stats->total_points,
                'current_level' => $stats->current_level,
                'total_badges' => $stats->total_badges,
                'assignments_completed' => $stats->assignments_completed,
                default => 0,
            };

            $progress = min(100, ($current / $required) * 100);
            $totalProgress += $progress;

            $requirements[$key] = [
                'current' => $current,
                'required' => $required,
                'progress' => round($progress, 2),
                'completed' => $current >= $required,
            ];
        }

        return [
            'earned' => false,
            'progress' => $criteriaCount > 0 ? round($totalProgress / $criteriaCount, 2) : 0,
            'requirements' => $requirements,
        ];
    }

    /**
     * الحصول على الشارات الموصى بها (القريبة من الإنجاز)
     */
    public function getRecommendedBadges(User $user, int $limit = 5)
    {
        $badges = Badge::where('is_active', true)
            ->where('is_visible', true)
            ->get();

        $recommendations = [];

        foreach ($badges as $badge) {
            if ($this->userHasBadge($user, $badge)) {
                continue;
            }

            $progress = $this->getBadgeProgress($user, $badge);

            if ($progress['progress'] >= 50 && $progress['progress'] < 100) {
                $recommendations[] = [
                    'badge' => $badge,
                    'progress' => $progress,
                ];
            }
        }

        // ترتيب حسب التقدم (الأقرب للإنجاز أولاً)
        usort($recommendations, function($a, $b) {
            return $b['progress']['progress'] <=> $a['progress']['progress'];
        });

        return array_slice($recommendations, 0, $limit);
    }

    /**
     * إحصائيات شارات المستخدم
     */
    public function getUserBadgeStats(User $user): array
    {
        $stats = $user->stats;

        $totalBadges = Badge::where('is_active', true)
            ->where('is_visible', true)
            ->count();

        $earnedBadges = $stats->total_badges;

        $byRarity = [
            'common' => UserBadge::where('user_id', $user->id)
                ->whereHas('badge', fn($q) => $q->where('rarity', 'common'))
                ->count(),
            'rare' => UserBadge::where('user_id', $user->id)
                ->whereHas('badge', fn($q) => $q->where('rarity', 'rare'))
                ->count(),
            'epic' => UserBadge::where('user_id', $user->id)
                ->whereHas('badge', fn($q) => $q->where('rarity', 'epic'))
                ->count(),
            'legendary' => UserBadge::where('user_id', $user->id)
                ->whereHas('badge', fn($q) => $q->where('rarity', 'legendary'))
                ->count(),
            'mythic' => UserBadge::where('user_id', $user->id)
                ->whereHas('badge', fn($q) => $q->where('rarity', 'mythic'))
                ->count(),
        ];

        $byType = UserBadge::where('user_id', $user->id)
            ->join('badges', 'user_badges.badge_id', '=', 'badges.id')
            ->selectRaw('badges.type, COUNT(*) as count')
            ->groupBy('badges.type')
            ->pluck('count', 'type')
            ->toArray();

        return [
            'total_available' => $totalBadges,
            'total_earned' => $earnedBadges,
            'completion_rate' => $totalBadges > 0 ? round(($earnedBadges / $totalBadges) * 100, 2) : 0,
            'by_rarity' => $byRarity,
            'by_type' => $byType,
            'latest' => $user->userBadges()->latest('awarded_at')->take(5)->get(),
        ];
    }
}
