<?php

namespace App\Http\Controllers\Student\Gamification;

use App\Http\Controllers\Controller;
use App\Models\ExperienceLevel;
use App\Services\Gamification\LevelService;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    protected LevelService $levelService;

    public function __construct(LevelService $levelService)
    {
        $this->levelService = $levelService;
    }

    /**
     * عرض صفحة المستوى والتقدم
     */
    public function index()
    {
        $user = auth()->user();

        // معلومات المستوى الحالي
        $levelInfo = $this->levelService->getUserLevelInfo($user);

        // الوقت المتوقع للمستوى التالي
        $timeToNextLevel = $this->levelService->estimateTimeToNextLevel($user);

        // المستويات القريبة (الحالي ±2)
        $currentLevel = $levelInfo['current_level'];
        $nearbyLevels = ExperienceLevel::whereBetween('level', [
                max(1, $currentLevel - 2),
                min(50, $currentLevel + 5)
            ])
            ->orderBy('level')
            ->get();

        return view('student.gamification.levels.index', compact(
            'levelInfo',
            'timeToNextLevel',
            'nearbyLevels'
        ));
    }

    /**
     * عرض جميع المستويات
     */
    public function all()
    {
        $user = auth()->user();
        $currentLevel = $user->stats->current_level ?? 1;

        $levels = ExperienceLevel::orderBy('level')->get();

        return view('student.gamification.levels.all', compact(
            'levels',
            'currentLevel'
        ));
    }

    /**
     * عرض تفاصيل مستوى معين
     */
    public function show(ExperienceLevel $level)
    {
        $user = auth()->user();
        $currentLevel = $user->stats->current_level ?? 1;
        $totalXP = $user->stats->total_xp ?? 0;

        $isUnlocked = $currentLevel >= $level->level;
        $xpNeeded = max(0, $level->total_xp_required - $totalXP);

        // المستوى السابق والتالي
        $previousLevel = ExperienceLevel::where('level', $level->level - 1)->first();
        $nextLevel = ExperienceLevel::where('level', $level->level + 1)->first();

        return view('student.gamification.levels.show', compact(
            'level',
            'isUnlocked',
            'xpNeeded',
            'previousLevel',
            'nextLevel'
        ));
    }
}
