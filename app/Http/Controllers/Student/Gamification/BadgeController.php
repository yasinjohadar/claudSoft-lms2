<?php

namespace App\Http\Controllers\Student\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Services\Gamification\BadgeService;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    protected BadgeService $badgeService;

    public function __construct(BadgeService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    /**
     * عرض صفحة الشارات
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // الحصول على إحصائيات الشارات
        $stats = $this->badgeService->getUserBadgeStats($user);

        // فلترة
        $type = $request->get('type');
        $rarity = $request->get('rarity');

        // الشارات المكتسبة
        $earnedBadges = $this->badgeService->getUserBadges($user, $type, $rarity);

        // جميع الشارات المتاحة
        $allBadges = Badge::where('is_active', true)
            ->where('is_visible', true)
            ->when($type, fn($q) => $q->where('type', $type))
            ->when($rarity, fn($q) => $q->where('rarity', $rarity))
            ->orderBy('sort_order')
            ->get();

        // إضافة معلومات الحصول على الشارة
        foreach ($allBadges as $badge) {
            $badge->is_earned = $this->badgeService->userHasBadge($user, $badge);
            $badge->progress = $this->badgeService->getBadgeProgress($user, $badge);
        }

        return view('student.pages.gamification.badges', compact(
            'stats',
            'earnedBadges',
            'allBadges',
            'type',
            'rarity'
        ));
    }

    /**
     * عرض تفاصيل شارة
     */
    public function show(Badge $badge)
    {
        $user = auth()->user();

        $isEarned = $this->badgeService->userHasBadge($user, $badge);
        $progress = $this->badgeService->getBadgeProgress($user, $badge);

        $userBadge = null;
        if ($isEarned) {
            $userBadge = $user->userBadges()
                ->where('badge_id', $badge->id)
                ->first();
        }

        return view('student.pages.gamification.badges.show', compact(
            'badge',
            'isEarned',
            'progress',
            'userBadge'
        ));
    }

    /**
     * عرض مجموعة الشارات (Showcase)
     */
    public function collection()
    {
        $user = auth()->user();

        // الشارات المكتسبة مرتبة حسب الندرة
        $badges = $this->badgeService->getUserBadges($user)
            ->sortByDesc(function($userBadge) {
                $rarityOrder = ['mythic' => 5, 'legendary' => 4, 'epic' => 3, 'rare' => 2, 'common' => 1];
                return $rarityOrder[$userBadge->badge->rarity] ?? 0;
            });

        // تجميع حسب النوع
        $badgesByType = $badges->groupBy('badge.type');

        // تجميع حسب الندرة
        $badgesByRarity = $badges->groupBy('badge.rarity');

        $stats = $this->badgeService->getUserBadgeStats($user);

        return view('student.pages.gamification.badges.collection', compact(
            'badges',
            'badgesByType',
            'badgesByRarity',
            'stats'
        ));
    }

    /**
     * عرض الشارات الموصى بها (القريبة من الإنجاز)
     */
    public function recommended()
    {
        $user = auth()->user();

        $recommendations = $this->badgeService->getRecommendedBadges($user, 10);

        return view('student.pages.gamification.badges.recommended', compact('recommendations'));
    }
}
