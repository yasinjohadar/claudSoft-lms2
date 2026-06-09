<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\User;
use App\Services\Gamification\BadgeManualAwardService;
use App\Services\Gamification\BadgeService;
use App\Support\Gamification\BadgeCriteriaMapper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BadgeController extends Controller
{
    public function __construct(
        protected BadgeService $badgeService,
        protected BadgeManualAwardService $manualAwardService
    ) {}

    /**
     * عرض قائمة الشارات
     */
    public function index(Request $request)
    {
        $query = Badge::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('rarity')) {
            $query->where('rarity', $request->rarity);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $badges = $query->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->paginate(30)
            ->withQueryString();

        $stats = [
            'total' => Badge::count(),
            'active' => Badge::where('is_active', true)->count(),
            'by_rarity' => Badge::selectRaw('rarity, COUNT(*) as count')
                ->groupBy('rarity')
                ->pluck('count', 'rarity')
                ->toArray(),
        ];

        return view('admin.pages.gamification.badges.index', compact('badges', 'stats'));
    }

    public function create()
    {
        return view('admin.pages.gamification.badges.create');
    }

    public function store(Request $request)
    {
        $validator = $this->validateBadgeRequest($request);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $slug = $request->slug ?: Str::slug($request->name);

            Badge::create($this->buildBadgeAttributes($request, $slug));

            return redirect()
                ->route('admin.gamification.badges.index')
                ->with('success', 'تم إنشاء الشارة بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Badge $badge)
    {
        $badge->load('userBadges.user');

        $stats = [
            'total_earned' => $badge->userBadges()->count(),
            'earned_today' => $badge->userBadges()->whereDate('awarded_at', today())->count(),
            'earned_this_week' => $badge->userBadges()->where('awarded_at', '>=', now()->startOfWeek())->count(),
            'earned_this_month' => $badge->userBadges()->where('awarded_at', '>=', now()->startOfMonth())->count(),
        ];

        $recentEarners = $badge->userBadges()
            ->with('user')
            ->latest('awarded_at')
            ->take(20)
            ->get();

        $criteriaLabel = BadgeCriteriaMapper::formatForDisplay($badge->criteria);

        return view('admin.pages.gamification.badges.show', compact('badge', 'stats', 'recentEarners', 'criteriaLabel'));
    }

    public function edit(Badge $badge)
    {
        $formFields = BadgeCriteriaMapper::criteriaToForm($badge->criteria);

        return view('admin.pages.gamification.badges.edit', [
            'badge' => $badge,
            'requirementType' => $formFields['requirement_type'],
            'requirementValue' => $formFields['requirement_value'],
        ]);
    }

    public function update(Request $request, Badge $badge)
    {
        $validator = $this->validateBadgeRequest($request, $badge->id);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $badge->update($this->buildBadgeAttributes($request, $request->slug ?: $badge->slug));

            return redirect()
                ->route('admin.gamification.badges.index')
                ->with('success', 'تم تحديث الشارة بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Badge $badge)
    {
        try {
            $badge->delete();

            return redirect()
                ->route('admin.gamification.badges.index')
                ->with('success', 'تم حذف الشارة بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage());
        }
    }

    public function awardForm(?Badge $badge = null)
    {
        $badges = Badge::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'icon', 'rarity']);

        $courses = Course::query()->orderBy('title')->get(['id', 'title']);
        $groups = CourseGroup::query()
            ->with('courses:id')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.pages.gamification.badges.award', compact('badge', 'badges', 'courses', 'groups'));
    }

    public function awardFormForBadge(Badge $badge)
    {
        return $this->awardForm($badge);
    }

    public function previewTargets(Request $request): JsonResponse
    {
        $validator = $this->validateManualAwardRequest($request, preview: true);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $badge = Badge::findOrFail($request->badge_id);
            $preview = $this->manualAwardService->preview(
                $badge,
                $request->target_type,
                $this->manualAwardParams($request)
            );

            return response()->json($preview);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function searchStudents(Request $request): JsonResponse
    {
        $query = User::role('student');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");

                if (is_numeric($search)) {
                    $q->orWhere('id', $search);
                }
            });
        }

        if ($request->filled('ids')) {
            $ids = collect(explode(',', $request->input('ids')))
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->all();
            $query->whereIn('id', $ids);
        }

        $students = $query
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'name_ar', 'email'])
            ->map(fn (User $student) => [
                'id' => $student->id,
                'text' => $this->formatStudentLabel($student),
            ]);

        return response()->json(['results' => $students]);
    }

    public function awardManual(Request $request)
    {
        $validator = $this->validateManualAwardRequest($request);

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $badge = Badge::findOrFail($request->badge_id);
            $students = $this->manualAwardService->resolveTargetStudents(
                $request->target_type,
                $this->manualAwardParams($request)
            );

            if ($students->isEmpty()) {
                return redirect()
                    ->back()
                    ->with('error', 'لم يتم العثور على طلاب مستهدفين.')
                    ->withInput();
            }

            $result = $this->manualAwardService->awardToStudents(
                $badge,
                $students,
                [
                    'reason' => $request->reason,
                    'manually_awarded' => true,
                    'awarded_by' => auth()->id(),
                    'target_type' => $request->target_type,
                    'target_snapshot' => $this->manualAwardParams($request),
                ]
            );

            $message = "تم منح الشارة لـ {$result['awarded']} طالب.";
            if ($result['skipped'] > 0) {
                $message .= " تم تخطي {$result['skipped']} (يمتلكونها مسبقاً).";
            }
            if ($result['failed'] > 0) {
                $message .= " فشل منح {$result['failed']} طالب.";
            }

            return redirect()
                ->route('admin.gamification.badges.show', $badge)
                ->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function awardToUser(Request $request)
    {
        $request->merge([
            'target_type' => 'single',
        ]);

        return $this->awardManual($request);
    }

    protected function validateManualAwardRequest(Request $request, bool $preview = false): \Illuminate\Validation\Validator
    {
        $rules = [
            'badge_id' => 'required|exists:badges,id',
            'target_type' => ['required', Rule::in(BadgeManualAwardService::TARGET_TYPES)],
            'reason' => 'nullable|string|max:500',
        ];

        $targetType = $request->input('target_type');

        if ($targetType === 'single') {
            $rules['user_id'] = 'required|exists:users,id';
        }

        if ($targetType === 'multiple') {
            $rules['user_ids'] = 'required|array|min:1';
            $rules['user_ids.*'] = 'integer|exists:users,id';
        }

        if ($targetType === 'group') {
            $rules['group_id'] = 'required|exists:course_groups,id';
        }

        if ($targetType === 'course') {
            $rules['course_id'] = 'required|exists:courses,id';
        }

        if ($targetType === 'course_group') {
            $rules['course_id'] = 'required|exists:courses,id';
            $rules['group_id'] = 'required|exists:course_groups,id';
        }

        return Validator::make($request->all(), $rules);
    }

    protected function manualAwardParams(Request $request): array
    {
        return [
            'user_id' => $request->input('user_id'),
            'user_ids' => $request->input('user_ids', []),
            'group_id' => $request->input('group_id'),
            'course_id' => $request->input('course_id'),
        ];
    }

    protected function formatStudentLabel(User $student): string
    {
        $label = $student->name;

        if ($student->name_ar) {
            $label .= ' (' . $student->name_ar . ')';
        }

        return $label . ' - ' . $student->email;
    }

    public function toggleActive(Badge $badge)
    {
        try {
            $badge->update(['is_active' => !$badge->is_active]);

            return redirect()
                ->back()
                ->with('success', 'تم تحديث حالة الشارة');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: ' . $e->getMessage());
        }
    }

    public function statistics()
    {
        $totalBadges = Badge::count();
        $activeBadges = Badge::where('is_active', true)->count();

        $byRarity = Badge::selectRaw('rarity, COUNT(*) as count')
            ->groupBy('rarity')
            ->orderByRaw("FIELD(rarity, 'mythic', 'legendary', 'epic', 'rare', 'common')")
            ->get();

        $byType = Badge::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->orderByDesc('count')
            ->get();

        $mostEarned = Badge::withCount('userBadges')
            ->orderByDesc('user_badges_count')
            ->limit(10)
            ->get();

        $leastEarned = Badge::withCount('userBadges')
            ->having('user_badges_count', '>', 0)
            ->orderBy('user_badges_count')
            ->limit(10)
            ->get();

        $neverEarned = Badge::withCount('userBadges')
            ->having('user_badges_count', '=', 0)
            ->get();

        return view('admin.pages.gamification.badges.statistics', compact(
            'totalBadges',
            'activeBadges',
            'byRarity',
            'byType',
            'mostEarned',
            'leastEarned',
            'neverEarned'
        ));
    }

    protected function validateBadgeRequest(Request $request, ?int $badgeId = null): \Illuminate\Validation\Validator
    {
        $slugRule = 'nullable|string|max:100|unique:badges,slug';
        if ($badgeId) {
            $slugRule .= ',' . $badgeId;
        }

        return Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'slug' => $slugRule,
            'description' => 'required|string|max:500',
            'icon' => 'nullable|string|max:100',
            'type' => 'required|in:achievement,progress,performance,engagement,special,event,social',
            'category' => 'nullable|string|max:50',
            'rarity' => 'required|in:common,rare,epic,legendary,mythic',
            'requirement_type' => 'nullable|string|max:50',
            'requirement_value' => 'nullable|integer|min:0',
            'points_reward' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }

    protected function buildBadgeAttributes(Request $request, string $slug): array
    {
        return [
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'icon' => $request->icon ?? '🏆',
            'type' => $request->type,
            'category' => $request->category,
            'rarity' => $request->rarity,
            'criteria' => BadgeCriteriaMapper::formToCriteria(
                $request->requirement_type,
                $request->requirement_value
            ),
            'points_value' => (int) ($request->points_reward ?? 0),
            'is_active' => $request->has('is_active'),
            'is_visible' => $request->has('is_visible'),
            'is_hidden' => $request->has('is_hidden'),
            'sort_order' => (int) ($request->sort_order ?? 0),
        ];
    }
}
