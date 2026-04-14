<?php

namespace App\Http\Controllers\Api\Student\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Services\Gamification\BadgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BadgeApiController extends Controller
{
    public function __construct(private readonly BadgeService $badgeService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $type = $request->query('type');
        $rarity = $request->query('rarity');

        $earnedBadges = $this->badgeService->getUserBadges($user, $type, $rarity)->values();

        $allBadges = Badge::query()
            ->where('is_active', true)
            ->where('is_visible', true)
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($rarity, fn ($q) => $q->where('rarity', $rarity))
            ->orderBy('sort_order')
            ->get()
            ->map(function (Badge $badge) use ($user) {
                return [
                    'badge' => $badge,
                    'is_earned' => $this->badgeService->userHasBadge($user, $badge),
                    'progress' => $this->badgeService->getBadgeProgress($user, $badge),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $this->badgeService->getUserBadgeStats($user),
                'earned_badges' => $earnedBadges,
                'all_badges' => $allBadges,
            ],
        ]);
    }
}
