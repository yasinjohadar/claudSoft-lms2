<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\Badge;
use App\Services\Gamification\AchievementRecalculationService;
use App\Support\Gamification\AchievementCriteriaMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AchievementController extends Controller
{
    public function __construct(
        protected AchievementRecalculationService $recalculationService
    ) {}

    public function index(Request $request)
    {
        $query = Achievement::with('badge')
            ->withCount(['userAchievements as completions_count' => function ($q) {
                $q->whereIn('status', ['completed', 'claimed']);
            }]);

        if ($request->filled('tier')) {
            $query->where('tier', $request->tier);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('requirement_type')) {
            $mapped = AchievementCriteriaMapper::formToAchievementData($request->requirement_type, 1);
            if ($mapped) {
                $query->where('criteria->field', $mapped['criteria']['field']);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $achievements = $query->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => Achievement::count(),
            'active' => Achievement::where('is_active', true)->count(),
            'total_completions' => \DB::table('user_achievements')
                ->whereIn('status', ['completed', 'claimed'])
                ->count(),
            'by_tier' => Achievement::selectRaw('tier, COUNT(*) as count')
                ->groupBy('tier')
                ->orderByRaw("FIELD(tier, 'diamond', 'platinum', 'gold', 'silver', 'bronze')")
                ->pluck('count', 'tier')
                ->toArray(),
        ];

        $requirementTypes = AchievementCriteriaMapper::REQUIREMENT_TYPE_OPTIONS;
        $tierOptions = [
            'bronze' => 'برونزي',
            'silver' => 'فضي',
            'gold' => 'ذهبي',
            'platinum' => 'بلاتيني',
            'diamond' => 'ماسي',
        ];

        return view('admin.pages.gamification.achievements.index', compact(
            'achievements',
            'stats',
            'requirementTypes',
            'tierOptions'
        ));
    }

    public function create()
    {
        $badges = Badge::where('is_active', true)->orderBy('name')->get();
        $requirementTypes = AchievementCriteriaMapper::REQUIREMENT_TYPE_OPTIONS;

        return view('admin.pages.gamification.achievements.create', compact('badges', 'requirementTypes'));
    }

    public function store(Request $request)
    {
        $validator = $this->validateAchievementRequest($request);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $payload = $this->buildAchievementPayload($request);

            Achievement::create($payload);

            return redirect()
                ->route('admin.gamification.achievements.index')
                ->with('success', 'تم إنشاء الإنجاز بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'خطأ: '.$e->getMessage())->withInput();
        }
    }

    public function show(Achievement $achievement)
    {
        $achievement->load('badge', 'userAchievements.user');

        $stats = [
            'total_started' => $achievement->userAchievements()->count(),
            'in_progress' => $achievement->userAchievements()->where('status', 'in_progress')->count(),
            'completed' => $achievement->userAchievements()->whereIn('status', ['completed', 'claimed'])->count(),
            'completion_rate' => 0,
        ];

        if ($stats['total_started'] > 0) {
            $stats['completion_rate'] = round(($stats['completed'] / $stats['total_started']) * 100, 2);
        }

        $recentCompletions = $achievement->userAchievements()
            ->whereIn('status', ['completed', 'claimed'])
            ->with('user')
            ->latest('completed_at')
            ->take(20)
            ->get();

        return view('admin.pages.gamification.achievements.show', compact('achievement', 'stats', 'recentCompletions'));
    }

    public function edit(Achievement $achievement)
    {
        $badges = Badge::where('is_active', true)->orderBy('name')->get();
        $requirementTypes = AchievementCriteriaMapper::REQUIREMENT_TYPE_OPTIONS;
        $formCriteria = AchievementCriteriaMapper::criteriaToForm(
            $achievement->criteria,
            $achievement->target_value
        );

        return view('admin.pages.gamification.achievements.edit', compact(
            'achievement',
            'badges',
            'requirementTypes',
            'formCriteria'
        ));
    }

    public function update(Request $request, Achievement $achievement)
    {
        $validator = $this->validateAchievementRequest($request, $achievement->id);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $achievement->update($this->buildAchievementPayload($request, $achievement));

            return redirect()
                ->route('admin.gamification.achievements.index')
                ->with('success', 'تم تحديث الإنجاز بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'خطأ: '.$e->getMessage())->withInput();
        }
    }

    public function destroy(Achievement $achievement)
    {
        try {
            $achievement->delete();

            return redirect()
                ->route('admin.gamification.achievements.index')
                ->with('success', 'تم حذف الإنجاز بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'خطأ: '.$e->getMessage());
        }
    }

    public function toggleActive(Achievement $achievement)
    {
        try {
            $achievement->update(['is_active' => ! $achievement->is_active]);

            return redirect()->back()->with('success', 'تم تحديث حالة الإنجاز');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'خطأ: '.$e->getMessage());
        }
    }

    public function recalculateAll()
    {
        set_time_limit(300);

        try {
            $this->recalculationService->migrateGamificationAchievements();
            $result = $this->recalculationService->recalculateForAllActiveStudents();

            return redirect()->back()->with(
                'success',
                'تمت إعادة التحقق من الإنجازات لـ '.$result['students']
                .' طالب. اكتمل '.$result['achievements_completed'].' إنجازاً جديداً.'
            );
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'خطأ: '.$e->getMessage());
        }
    }

    public function statistics()
    {
        $totalAchievements = Achievement::count();
        $activeAchievements = Achievement::where('is_active', true)->count();

        $byTier = Achievement::selectRaw('tier, COUNT(*) as count')
            ->groupBy('tier')
            ->orderByRaw("FIELD(tier, 'diamond', 'platinum', 'gold', 'silver', 'bronze')")
            ->get();

        $mostCompleted = Achievement::withCount(['userAchievements as completions_count' => function ($q) {
            $q->whereIn('status', ['completed', 'claimed']);
        }])
            ->orderByDesc('completions_count')
            ->limit(10)
            ->get();

        $leastCompleted = Achievement::withCount(['userAchievements as completions_count' => function ($q) {
            $q->whereIn('status', ['completed', 'claimed']);
        }])
            ->having('completions_count', '>', 0)
            ->orderBy('completions_count')
            ->limit(10)
            ->get();

        $totalCompletions = \DB::table('user_achievements')
            ->whereIn('status', ['completed', 'claimed'])
            ->count();

        return view('admin.pages.gamification.achievements.statistics', compact(
            'totalAchievements',
            'activeAchievements',
            'totalCompletions',
            'byTier',
            'mostCompleted',
            'leastCompleted'
        ));
    }

    protected function validateAchievementRequest(Request $request, ?int $ignoreId = null): \Illuminate\Contracts\Validation\Validator
    {
        $slugRule = 'nullable|string|max:100|unique:achievements,slug';
        if ($ignoreId) {
            $slugRule .= ','.$ignoreId;
        }

        return Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'slug' => $slugRule,
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:20',
            'tier' => 'required|in:bronze,silver,gold,platinum,diamond',
            'badge_id' => 'nullable|exists:badges,id',
            'requirement_type' => 'required|in:'.implode(',', array_keys(AchievementCriteriaMapper::REQUIREMENT_TYPE_OPTIONS)),
            'requirement_value' => 'required|integer|min:1',
            'points_reward' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ], [
            'name.required' => 'اسم الإنجاز مطلوب',
            'requirement_type.required' => 'نوع المتطلب مطلوب',
            'requirement_value.required' => 'قيمة المتطلب مطلوبة',
        ]);
    }

    protected function buildAchievementPayload(Request $request, ?Achievement $existing = null): array
    {
        $mapped = AchievementCriteriaMapper::formToAchievementData(
            $request->requirement_type,
            $request->requirement_value
        );

        if (!$mapped) {
            throw new \InvalidArgumentException('نوع المتطلب غير صالح');
        }

        return [
            'name' => $request->name,
            'slug' => $request->slug ?: ($existing?->slug ?? Str::slug($request->name)),
            'description' => $request->description,
            'icon' => $request->icon ?: '🏆',
            'tier' => $request->tier,
            'badge_id' => $request->badge_id,
            'type' => 'general',
            'target_value' => $mapped['target_value'],
            'criteria' => $mapped['criteria'],
            'points_reward' => $request->points_reward ?? 0,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->sort_order ?? ($existing?->sort_order ?? 0),
        ];
    }
}
