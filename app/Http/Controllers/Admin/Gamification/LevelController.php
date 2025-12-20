<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ExperienceLevel;
use App\Services\Gamification\LevelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LevelController extends Controller
{
    protected LevelService $levelService;

    public function __construct(LevelService $levelService)
    {
        $this->levelService = $levelService;
    }

    /**
     * عرض قائمة المستويات
     */
    public function index()
    {
        $levels = ExperienceLevel::orderBy('level')->paginate(50);

        $stats = [
            'total_levels' => ExperienceLevel::count(),
            'max_level' => config('gamification.levels.max_level', 50),
            'users_max_level' => User::whereHas('stats', function($q) {
                $q->where('current_level', '>=', config('gamification.levels.max_level', 50));
            })->count(),
        ];

        return view('admin.pages.gamification.levels.index', compact('levels', 'stats'));
    }

    /**
     * عرض صفحة إنشاء مستوى جديد
     */
    public function create()
    {
        $maxLevel = ExperienceLevel::max('level') ?? 0;
        $nextLevel = $maxLevel + 1;

        // حساب XP المقترح للمستوى التالي
        $baseXP = config('gamification.levels.base_xp', 100);
        $exponent = config('gamification.levels.exponent', 1.5);
        $suggestedXP = (int) ($baseXP * pow($nextLevel, $exponent));

        return view('admin.pages.gamification.levels.create', compact('nextLevel', 'suggestedXP'));
    }

    /**
     * حفظ مستوى جديد
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'level' => 'required|integer|min:1|max:10000|unique:experience_levels,level',
            'name' => 'required|string|max:255',
            'xp_required' => 'required|integer|min:0',
            'title' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'color_code' => 'nullable|string|max:20',
            'points_reward' => 'nullable|integer|min:0',
        ], [
            'level.required' => 'رقم المستوى مطلوب',
            'level.integer' => 'رقم المستوى يجب أن يكون رقماً صحيحاً',
            'level.min' => 'رقم المستوى يجب أن يكون على الأقل 1',
            'level.max' => 'رقم المستوى يجب ألا يتجاوز 10000',
            'level.unique' => 'هذا المستوى موجود بالفعل',
            'name.required' => 'اسم المستوى مطلوب',
            'xp_required.required' => 'XP المطلوب مطلوب',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            ExperienceLevel::create([
                'level' => $request->level,
                'name' => $request->name,
                'title' => $request->title ?? $request->name,
                'description' => $request->description,
                'xp_required' => $request->xp_required,
                'xp_to_next' => 0, // سيتم حسابه لاحقاً
                'icon' => $request->icon ?? '🏆',
                'color_code' => $request->color_code,
                'points_reward' => $request->points_reward ?? 0,
                'sort_order' => $request->level,
                'is_active' => true,
            ]);

            // تنظيف الكاش
            \Cache::forget('experience_levels');
            \Cache::forget('all_experience_levels');

            return redirect()
                ->route('admin.gamification.levels.index')
                ->with('success', 'تم إنشاء المستوى بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * عرض صفحة تعديل مستوى
     */
    public function edit(ExperienceLevel $level)
    {
        return view('admin.pages.gamification.levels.edit', compact('level'));
    }

    /**
     * تحديث مستوى
     */
    public function update(Request $request, ExperienceLevel $level)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'xp_required' => 'required|integer|min:0',
            'points_reward' => 'nullable|integer|min:0',
            'gems_reward' => 'nullable|integer|min:0',
            'title' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'color_code' => 'nullable|string|max:20',
        ], [
            'name.required' => 'اسم المستوى مطلوب',
            'xp_required.required' => 'XP المطلوب مطلوب',
            'xp_required.integer' => 'XP المطلوب يجب أن يكون رقماً صحيحاً',
            'xp_required.min' => 'XP المطلوب يجب أن يكون على الأقل 0',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $level->update([
                'name' => $request->name,
                'xp_required' => $request->xp_required,
                'points_reward' => $request->points_reward ?? 0,
                'title' => $request->title ?? $request->name,
                'description' => $request->description,
                'icon' => $request->icon ?? $level->icon,
                'color_code' => $request->color_code ?? $level->color_code,
            ]);

            // تنظيف الكاش
            \Cache::forget('experience_levels');
            \Cache::forget('all_experience_levels');
            \Cache::forget("level_{$level->level}_xp");

            return redirect()
                ->route('admin.gamification.levels.index')
                ->with('success', 'تم تحديث المستوى بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * حذف مستوى
     */
    public function destroy(ExperienceLevel $level)
    {
        try {
            $level->delete();

            // تنظيف الكاش
            \Cache::forget('experience_levels');
            \Cache::forget('all_experience_levels');
            \Cache::forget("level_{$level->level}_xp");

            return redirect()
                ->route('admin.gamification.levels.index')
                ->with('success', 'تم حذف المستوى بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage());
        }
    }

    /**
     * عرض إحصائيات المستويات
     */
    public function statistics()
    {
        // توزيع المستخدمين حسب المستويات
        $levelDistribution = User::whereHas('stats')
            ->join('gamification_user_stats', 'users.id', '=', 'user_stats.user_id')
            ->selectRaw('current_level, COUNT(*) as user_count')
            ->groupBy('current_level')
            ->orderBy('current_level')
            ->get();

        // أعلى المستخدمين
        $topUsers = User::whereHas('stats')
            ->with('stats')
            ->join('gamification_user_stats', 'users.id', '=', 'user_stats.user_id')
            ->orderByDesc('user_stats.current_level')
            ->orderByDesc('user_stats.total_xp')
            ->limit(50)
            ->get();

        // متوسط المستوى
        $avgLevel = User::whereHas('stats')
            ->join('gamification_user_stats', 'users.id', '=', 'user_stats.user_id')
            ->avg('current_level');

        return view('admin.pages.gamification.levels.statistics', compact(
            'levelDistribution',
            'topUsers',
            'avgLevel'
        ));
    }

    /**
     * توليد المستويات تلقائياً
     */
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_level' => 'required|integer|min:1',
            'end_level' => 'required|integer|min:1|gte:start_level',
            'base_xp' => 'required|integer|min:1',
            'exponent' => 'required|numeric|min:1|max:3',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $startLevel = (int) $request->start_level;
            $endLevel = (int) $request->end_level;
            $baseXP = (int) $request->base_xp;
            $exponent = (float) $request->exponent;

            $created = 0;

            $levelNames = [
                1 => 'مبتدئ', 2 => 'متدرب', 3 => 'ناشئ', 4 => 'متقدم', 5 => 'ماهر',
                10 => 'خبير', 15 => 'متمكن', 20 => 'محترف', 25 => 'بطل', 30 => 'نجم',
                35 => 'أسطورة', 40 => 'عبقري', 45 => 'سيد', 50 => 'إله'
            ];

            for ($level = $startLevel; $level <= $endLevel; $level++) {
                // التحقق من عدم وجود المستوى
                if (ExperienceLevel::where('level', $level)->exists()) {
                    continue;
                }

                $totalXPRequired = (int) ($baseXP * pow($level, $exponent));
                $rewardPoints = (int) ($level * 50); // 50 نقطة لكل مستوى
                $levelName = $levelNames[$level] ?? "المستوى {$level}";

                ExperienceLevel::create([
                    'level' => $level,
                    'name' => $levelName,
                    'title' => $levelName,
                    'description' => "وصلت إلى {$levelName}",
                    'xp_required' => $totalXPRequired,
                    'xp_to_next' => $level < $endLevel ? (int)($baseXP * pow($level + 1, $exponent)) - $totalXPRequired : 0,
                    'points_reward' => $rewardPoints,
                    'icon' => '🏆',
                    'color_code' => '#' . substr(dechex($level * 1000 + 100000), 0, 6),
                    'sort_order' => $level,
                    'is_active' => true,
                ]);

                $created++;
            }

            // تنظيف الكاش
            \Cache::forget('experience_levels');
            \Cache::forget('all_experience_levels');

            return redirect()
                ->route('admin.gamification.levels.index')
                ->with('success', "تم إنشاء {$created} مستوى بنجاح");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage())
                ->withInput();
        }
    }
}
