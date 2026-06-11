<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Leaderboard;
use App\Services\Gamification\LeaderboardCatalog;
use App\Services\Gamification\LeaderboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LeaderboardController extends Controller
{
    public function __construct(
        protected LeaderboardService $leaderboardService,
        protected LeaderboardCatalog $catalog
    ) {}

    public function index()
    {
        $leaderboards = Leaderboard::orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->withCount('entries')
            ->get();

        $stats = [
            'total' => Leaderboard::count(),
            'active' => Leaderboard::where('is_active', true)->count(),
            'total_entries' => \DB::table('leaderboard_entries')->count(),
        ];

        return view('admin.pages.gamification.leaderboards.index', compact('leaderboards', 'stats'));
    }

    public function create()
    {
        return view('admin.pages.gamification.leaderboards.create', $this->formOptions());
    }

    public function store(Request $request)
    {
        $validator = $this->validateLeaderboard($request);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            Leaderboard::create($this->leaderboardPayload($request));

            return redirect()
                ->route('admin.gamification.leaderboards.index')
                ->with('success', 'تم إنشاء لوحة المتصدرين بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'خطأ: '.$e->getMessage())->withInput();
        }
    }

    public function show(Leaderboard $leaderboard)
    {
        $entries = $this->leaderboardService->getLeaderboard($leaderboard, 100);
        $stats = $this->leaderboardService->getLeaderboardStats($leaderboard);

        return view('admin.pages.gamification.leaderboards.show', compact('leaderboard', 'entries', 'stats'));
    }

    public function edit(Leaderboard $leaderboard)
    {
        return view('admin.pages.gamification.leaderboards.edit', array_merge(
            compact('leaderboard'),
            $this->formOptions()
        ));
    }

    public function update(Request $request, Leaderboard $leaderboard)
    {
        $validator = $this->validateLeaderboard($request, $leaderboard->id);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $leaderboard->update($this->leaderboardPayload($request, $leaderboard));

            return redirect()
                ->route('admin.gamification.leaderboards.show', $leaderboard)
                ->with('success', 'تم تحديث لوحة المتصدرين بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'خطأ: '.$e->getMessage())->withInput();
        }
    }

    public function destroy(Leaderboard $leaderboard)
    {
        try {
            $leaderboard->delete();

            return redirect()
                ->route('admin.gamification.leaderboards.index')
                ->with('success', 'تم حذف لوحة المتصدرين بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'خطأ: '.$e->getMessage());
        }
    }

    public function updateLeaderboard(Leaderboard $leaderboard)
    {
        $success = $this->leaderboardService->updateLeaderboard($leaderboard);

        return redirect()->back()->with(
            $success ? 'success' : 'error',
            $success ? 'تم تحديث الترتيب بنجاح' : 'فشل في تحديث اللوحة'
        );
    }

    public function updateAll()
    {
        $updated = $this->leaderboardService->updateAllLeaderboards();

        return redirect()->back()->with('success', 'تم تحديث '.count($updated).' لوحة بنجاح');
    }

    public function awardRewards(Leaderboard $leaderboard)
    {
        $awarded = $this->leaderboardService->awardLeaderboardRewards($leaderboard);

        return redirect()->back()->with('success', "تم منح المكافآت لـ {$awarded} مستخدم");
    }

    public function toggleActive(Leaderboard $leaderboard)
    {
        $leaderboard->update(['is_active' => ! $leaderboard->is_active]);

        return redirect()->back()->with('success', 'تم تحديث حالة اللوحة');
    }

    protected function formOptions(): array
    {
        return [
            'typeOptions' => $this->catalog->getTypeOptions(),
            'periodOptions' => $this->catalog->getPeriodOptions(),
            'metricOptions' => $this->catalog->getMetricOptions(),
            'courses' => Course::query()->orderBy('title')->get(['id', 'title']),
        ];
    }

    protected function validateLeaderboard(Request $request, ?int $ignoreId = null): \Illuminate\Contracts\Validation\Validator
    {
        $slugRule = 'nullable|string|max:100|unique:leaderboards,slug';
        if ($ignoreId) {
            $slugRule .= ','.$ignoreId;
        }

        return Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'slug' => $slugRule,
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:'.implode(',', array_keys(LeaderboardCatalog::TYPES)),
            'metric' => 'required|in:'.implode(',', array_keys(LeaderboardCatalog::METRICS)),
            'icon' => 'nullable|string|max:20',
            'period' => 'required|in:'.implode(',', array_keys(LeaderboardCatalog::PERIODS)),
            'course_id' => 'nullable|exists:courses,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'max_entries' => 'nullable|integer|min:10|max:500',
            'min_score' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer',
            'rewards_json' => 'nullable|string',
        ], [
            'name.required' => 'اسم اللوحة مطلوب',
            'type.required' => 'نوع اللوحة مطلوب',
            'metric.required' => 'المقياس مطلوب',
            'period.required' => 'الفترة مطلوبة',
        ]);
    }

    protected function leaderboardPayload(Request $request, ?Leaderboard $existing = null): array
    {
        $rewards = null;
        if ($request->filled('rewards_json')) {
            $decoded = json_decode($request->rewards_json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $rewards = $decoded;
            }
        }

        return [
            'name' => $request->name,
            'slug' => $request->slug ?: Str::slug($request->name),
            'description' => $request->description,
            'type' => $request->type,
            'metric' => $request->metric,
            'icon' => $request->icon ?: '🏆',
            'period' => $request->period,
            'course_id' => $request->course_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'max_entries' => $request->max_entries ?? 100,
            'min_score' => $request->min_score ?? 0,
            'has_divisions' => $request->boolean('has_divisions'),
            'rewards' => $rewards ?? $existing?->rewards,
            'is_active' => $request->boolean('is_active'),
            'is_visible' => $request->boolean('is_visible', true),
            'sort_order' => $request->sort_order ?? 0,
        ];
    }
}
