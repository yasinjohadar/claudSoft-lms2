<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gamification\Level;
use App\Models\Gamification\Badge;
use App\Models\Gamification\Achievement;
use App\Models\Gamification\Challenge;
use App\Models\Gamification\Leaderboard;
use App\Models\Gamification\ShopCategory;
use App\Models\Gamification\ShopItem;

class GamificationController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        // إحصائيات المستخدم
        $stats = $user->gamificationStats ?? (object)[
            'total_points' => 0,
            'total_xp' => 0,
            'current_level' => 1,
            'gems' => 0,
            'current_streak' => 0,
        ];

        // حساب التقدم للمستوى التالي
        $currentLevel = Level::where('level', $stats->current_level)->first();
        $nextLevel = Level::where('level', $stats->current_level + 1)->first();

        $levelProgress = 50;
        $xpToNextLevel = 0;

        if ($currentLevel && $nextLevel) {
            $xpInLevel = $stats->total_xp - $currentLevel->xp_required;
            $xpNeeded = $nextLevel->xp_required - $currentLevel->xp_required;
            $levelProgress = $xpNeeded > 0 ? min(100, ($xpInLevel / $xpNeeded) * 100) : 100;
            $xpToNextLevel = max(0, $nextLevel->xp_required - $stats->total_xp);
        }

        // آخر الشارات
        $recentBadges = $user->badges()->orderByPivot('awarded_at', 'desc')->limit(3)->get();

        // التحديات النشطة
        $activeChallenges = Challenge::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->limit(3)
            ->get();

        // الترتيب في لوحة المتصدرين
        $rank = '-';
        $totalUsers = 0;

        return view('student.pages.gamification.dashboard', compact(
            'stats', 'levelProgress', 'xpToNextLevel', 'recentBadges',
            'activeChallenges', 'rank', 'totalUsers'
        ));
    }

    public function badges()
    {
        $user = auth()->user();

        $earnedBadges = $user->badges()->orderByPivot('awarded_at', 'desc')->get();
        $earnedBadgeIds = $earnedBadges->pluck('id')->toArray();

        $availableBadges = Badge::where('is_active', true)
            ->whereNotIn('id', $earnedBadgeIds)
            ->get();

        return view('student.pages.gamification.badges', compact('earnedBadges', 'availableBadges'));
    }

    public function achievements()
    {
        $user = auth()->user();

        $unlockedAchievements = $user->achievements()->orderByPivot('unlocked_at', 'desc')->get();
        $unlockedIds = $unlockedAchievements->pluck('id')->toArray();

        $lockedAchievements = Achievement::where('is_active', true)
            ->whereNotIn('id', $unlockedIds)
            ->get();

        return view('student.pages.gamification.achievements', compact('unlockedAchievements', 'lockedAchievements'));
    }

    public function leaderboard(Request $request)
    {
        $period = $request->get('period', 'all_time');
        $type = $request->get('type', 'points');

        // هنا يمكنك تنفيذ منطق جلب البيانات حسب الفترة والنوع
        $leaderboard = collect([]);
        $topThree = collect([]);

        return view('student.pages.gamification.leaderboard', compact('leaderboard', 'topThree'));
    }

    public function challenges()
    {
        $dailyChallenges = Challenge::where('is_active', true)
            ->where('type', 'daily')
            ->get();

        $weeklyChallenges = Challenge::where('is_active', true)
            ->where('type', 'weekly')
            ->get();

        $completedChallenges = collect([]);

        return view('student.pages.gamification.challenges', compact('dailyChallenges', 'weeklyChallenges', 'completedChallenges'));
    }

    public function shop()
    {
        $user = auth()->user();
        $stats = $user->gamificationStats;

        $userPoints = $stats->total_points ?? 0;
        $userGems = $stats->gems ?? 0;

        $categories = ShopCategory::where('is_active', true)
            ->with(['items' => function($q) {
                $q->where('is_active', true);
            }])
            ->get();

        $myPurchases = collect([]);

        return view('student.pages.gamification.shop', compact('categories', 'userPoints', 'userGems', 'myPurchases'));
    }

    public function purchase(Request $request, $itemId)
    {
        // منطق الشراء سيتم تنفيذه لاحقاً
        return back()->with('error', 'خدمة الشراء غير متاحة حالياً');
    }
}
