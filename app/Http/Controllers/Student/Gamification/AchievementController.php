<?php

namespace App\Http\Controllers\Student\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Services\Gamification\AchievementService;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    protected AchievementService $achievementService;

    public function __construct(AchievementService $achievementService)
    {
        $this->achievementService = $achievementService;
    }

    /**
     * عرض صفحة الإنجازات
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // فلترة
        $tier = $request->get('tier');
        $status = $request->get('status');

        // الإنجازات
        $achievements = $this->achievementService->getUserAchievements($user, $status, $tier);

        // الإحصائيات
        $stats = $this->achievementService->getUserAchievementStats($user);

        return view('student.pages.gamification.achievements', compact(
            'achievements',
            'stats',
            'tier',
            'status'
        ));
    }

    /**
     * عرض تفاصيل إنجاز
     */
    public function show(Achievement $achievement)
    {
        $user = auth()->user();

        $userAchievement = UserAchievement::where('user_id', $user->id)
            ->where('achievement_id', $achievement->id)
            ->first();

        if (!$userAchievement) {
            // بدء تتبع الإنجاز إذا لم يكن موجوداً
            $userAchievement = $this->achievementService->startTracking($user, $achievement);
        }

        return view('student.pages.gamification.achievements.show', compact(
            'achievement',
            'userAchievement'
        ));
    }

    /**
     * المطالبة بمكافأة إنجاز
     */
    public function claim(UserAchievement $userAchievement)
    {
        $user = auth()->user();

        // التحقق من أن الإنجاز يخص المستخدم
        if ($userAchievement->user_id !== $user->id) {
            return redirect()
                ->back()
                ->with('error', 'هذا الإنجاز لا يخصك');
        }

        $success = $this->achievementService->claimReward($user, $userAchievement);

        if ($success) {
            return redirect()
                ->back()
                ->with('success', 'تم المطالبة بالمكافأة بنجاح!');
        } else {
            return redirect()
                ->back()
                ->with('error', 'لا يمكن المطالبة بالمكافأة');
        }
    }

    /**
     * عرض الإنجازات الموصى بها (قيد التقدم)
     */
    public function recommended()
    {
        $user = auth()->user();

        $recommended = $this->achievementService->getRecommendedAchievements($user, 10);

        return view('student.pages.gamification.achievements.recommended', compact('recommended'));
    }
}
