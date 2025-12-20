<?php

namespace App\Http\Controllers\Student\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Leaderboard;
use App\Services\Gamification\LeaderboardService;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    protected LeaderboardService $leaderboardService;

    public function __construct(LeaderboardService $leaderboardService)
    {
        $this->leaderboardService = $leaderboardService;
    }

    /**
     * عرض قائمة اللوحات
     */
    public function index()
    {
        $user = auth()->user();

        $leaderboards = Leaderboard::where('is_active', true)
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->get();

        // الحصول على ترتيب المستخدم في كل لوحة
        foreach ($leaderboards as $leaderboard) {
            $leaderboard->user_rank = $this->leaderboardService->getUserRank($user, $leaderboard);
        }

        return view('student.pages.gamification.leaderboard', compact('leaderboards'));
    }

    /**
     * عرض تفاصيل لوحة
     */
    public function show(Leaderboard $leaderboard)
    {
        // التحقق من أن اللوحة نشطة وعامة
        if (!$leaderboard->is_active || !$leaderboard->is_visible) {
            abort(404);
        }

        $user = auth()->user();

        // الحصول على قائمة المتصدرين
        $entries = $this->leaderboardService->getLeaderboard($leaderboard, 50);

        // ترتيب المستخدم
        $userRank = $this->leaderboardService->getUserRank($user, $leaderboard);

        // المستخدمين المحيطين
        $surroundingUsers = $this->leaderboardService->getSurroundingUsers($user, $leaderboard, 3);

        // إحصائيات
        $stats = $this->leaderboardService->getLeaderboardStats($leaderboard);

        return view('student.pages.gamification.leaderboards.show', compact(
            'leaderboard',
            'entries',
            'userRank',
            'surroundingUsers',
            'stats'
        ));
    }

    /**
     * عرض المتصدرين حسب التقسيم
     */
    public function division(Leaderboard $leaderboard, string $division)
    {
        if (!$leaderboard->is_active || !$leaderboard->is_visible) {
            abort(404);
        }

        $validDivisions = ['bronze', 'silver', 'gold', 'platinum', 'diamond'];
        if (!in_array($division, $validDivisions)) {
            abort(404);
        }

        $entries = $this->leaderboardService->getTopByDivision($leaderboard, $division, 50);

        return view('student.pages.gamification.leaderboards.division', compact(
            'leaderboard',
            'division',
            'entries'
        ));
    }

    /**
     * عرض لوحة خاصة بالمستخدم (My Rank)
     */
    public function myRank()
    {
        $user = auth()->user();

        $leaderboards = Leaderboard::where('is_active', true)
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->get();

        $rankings = [];

        foreach ($leaderboards as $leaderboard) {
            $rank = $this->leaderboardService->getUserRank($user, $leaderboard);
            if ($rank) {
                $rankings[] = [
                    'leaderboard' => $leaderboard,
                    'rank' => $rank,
                ];
            }
        }

        // ترتيب حسب الأداء (أفضل النسب المئوية)
        usort($rankings, function($a, $b) {
            return $b['rank']['percentile'] <=> $a['rank']['percentile'];
        });

        return view('student.pages.gamification.leaderboards.my-rank', compact('rankings'));
    }
}
