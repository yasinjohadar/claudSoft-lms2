<?php

namespace App\Http\Controllers\Admin\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\PointsTransaction;
use App\Models\User;
use App\Services\Gamification\BadgeManualAwardService;
use App\Services\Gamification\GamificationService;
use App\Services\Gamification\PointEarningCatalog;
use App\Services\Gamification\PointsBulkGrantService;
use App\Services\Gamification\PointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PointsController extends Controller
{
    public function __construct(
        protected PointsService $pointsService,
        protected GamificationService $gamificationService,
        protected PointsBulkGrantService $bulkGrantService,
        protected BadgeManualAwardService $targetService,
        protected PointEarningCatalog $earningCatalog
    ) {}

    public function index(Request $request)
    {
        $query = PointsTransaction::with(['user:id,name,email,name_ar', 'admin:id,name']);

        if ($request->filled('q')) {
            $term = trim((string) $request->q);
            $query->whereHas('user', function ($userQuery) use ($term) {
                $userQuery->where('name', 'like', "%{$term}%")
                    ->orWhere('name_ar', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $transactions = $query->latest()
            ->paginate(50)
            ->withQueryString();

        $stats = [
            'total_transactions' => PointsTransaction::count(),
            'total_points_awarded' => (int) PointsTransaction::where('points', '>', 0)->sum('points'),
            'total_points_spent' => (int) abs(PointsTransaction::where('points', '<', 0)->sum('points')),
            'today_transactions' => PointsTransaction::whereDate('created_at', today())->count(),
        ];

        $sourceOptions = $this->earningCatalog->getDistinctSourcesForFilter();

        return view('admin.pages.gamification.points.index', compact('transactions', 'stats', 'sourceOptions'));
    }

    public function userTransactions(User $user)
    {
        $transactions = $user->pointsTransactions()
            ->latest()
            ->paginate(30);

        $stats = [
            'total_points' => $this->pointsService->getTotalPoints($user),
            'available_points' => $this->pointsService->getAvailablePoints($user),
            'total_earned' => $user->pointsTransactions()->where('points', '>', 0)->sum('points'),
            'total_spent' => abs($user->pointsTransactions()->where('points', '<', 0)->sum('points')),
        ];

        return view('admin.pages.gamification.points.user-transactions', compact('user', 'transactions', 'stats'));
    }

    public function create()
    {
        $courses = Course::query()->orderBy('title')->get(['id', 'title']);
        $groups = CourseGroup::query()
            ->with('courses:id')
            ->orderBy('name')
            ->get(['id', 'name']);

        $targetTypes = BadgeManualAwardService::TARGET_TYPE_LABELS;

        return view('admin.pages.gamification.points.create', compact('courses', 'groups', 'targetTypes'));
    }

    public function previewRecipients(Request $request): JsonResponse
    {
        $validator = $this->validateGrantRequest($request, preview: true);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $preview = $this->bulkGrantService->preview(
                $request->input('target_type'),
                $this->targetService->targetPayloadFromRequest($request->all()),
                $request->input('operation'),
                (int) $request->input('points', 0)
            );

            return response()->json($preview);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function searchStudents(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q', ''));
        $ids = collect($request->input('ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values();

        $query = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'student'));

        if ($ids->isNotEmpty()) {
            $query->whereIn('id', $ids);
        } elseif (mb_strlen($term) >= 2) {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('name_ar', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        } else {
            return response()->json(['results' => []]);
        }

        $results = $query->orderBy('name')->limit(50)->get(['id', 'name', 'name_ar', 'email'])
            ->map(fn (User $student) => [
                'id' => $student->id,
                'text' => $this->formatStudentLabel($student),
            ]);

        return response()->json(['results' => $results]);
    }

    public function store(Request $request)
    {
        $validator = $this->validateGrantRequest($request);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $result = $this->bulkGrantService->execute(
                $request->input('target_type'),
                $this->targetService->targetPayloadFromRequest($request->all()),
                $request->input('operation'),
                (int) $request->input('points', 0),
                $request->input('reason'),
                auth()->user()
            );

            $message = $this->formatSuccessMessage($result);

            return redirect()
                ->route('admin.gamification.points.index')
                ->with('success', $message);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'خطأ: '.$e->getMessage())->withInput();
        }
    }

    public function destroy(PointsTransaction $transaction)
    {
        try {
            $user = $transaction->user;
            $reversePoints = -$transaction->points;

            $this->pointsService->awardBonus(
                $user,
                $reversePoints,
                "إلغاء معاملة رقم {$transaction->id}",
                auth()->user()
            );

            return redirect()
                ->back()
                ->with('success', 'تم إلغاء المعاملة بنجاح');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'خطأ: '.$e->getMessage());
        }
    }

    public function recalculate(User $user)
    {
        try {
            $success = $this->gamificationService->recalculateStats($user);

            if ($success) {
                return redirect()
                    ->back()
                    ->with('success', 'تم إعادة حساب الإحصائيات بنجاح');
            }

            return redirect()->back()->with('error', 'فشل في إعادة حساب الإحصائيات');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'خطأ: '.$e->getMessage());
        }
    }

    public function report(Request $request)
    {
        return redirect()->route('admin.gamification.points.index');
    }

    protected function validateGrantRequest(Request $request, bool $preview = false): \Illuminate\Contracts\Validation\Validator
    {
        $rules = [
            'target_type' => ['required', Rule::in(BadgeManualAwardService::TARGET_TYPES)],
            'operation' => ['required', Rule::in([
                PointsBulkGrantService::OPERATION_BONUS,
                PointsBulkGrantService::OPERATION_DEDUCT,
                PointsBulkGrantService::OPERATION_BACKFILL,
            ])],
            'reason' => ($preview ? 'nullable' : 'required').'|string|max:500',
        ];

        if ($request->input('operation') !== PointsBulkGrantService::OPERATION_BACKFILL) {
            $rules['points'] = 'required|integer|not_in:0';
        }

        $validator = Validator::make($request->all(), $rules);
        $validator->after(function ($validator) use ($request) {
            $this->appendTargetingErrors($validator, $request);
        });

        return $validator;
    }

    protected function appendTargetingErrors($validator, Request $request): void
    {
        $targetType = $request->input('target_type');

        if ($targetType === 'single' && ! $request->filled('user_id')) {
            $validator->errors()->add('user_id', 'يرجى اختيار طالب.');
        }

        if ($targetType === 'multiple' && (! is_array($request->input('user_ids')) || count($request->input('user_ids', [])) === 0)) {
            $validator->errors()->add('user_ids', 'يرجى اختيار طالب واحد على الأقل.');
        }

        if ($targetType === 'group' && ! $request->filled('group_id')) {
            $validator->errors()->add('group_id', 'يرجى اختيار مجموعة.');
        }

        if ($targetType === 'multiple_groups' && (! is_array($request->input('group_ids')) || count($request->input('group_ids', [])) === 0)) {
            $validator->errors()->add('group_ids', 'يرجى اختيار مجموعة واحدة على الأقل.');
        }

        if ($targetType === 'course' && ! $request->filled('course_id')) {
            $validator->errors()->add('course_id', 'يرجى اختيار كورس.');
        }

        if ($targetType === 'course_group') {
            if (! $request->filled('course_id')) {
                $validator->errors()->add('course_id', 'يرجى اختيار كورس.');
            }
            if (! $request->filled('group_id')) {
                $validator->errors()->add('group_id', 'يرجى اختيار مجموعة.');
            }
        }
    }

    protected function formatStudentLabel(User $student): string
    {
        $label = $student->name;

        if ($student->name_ar) {
            $label .= ' ('.$student->name_ar.')';
        }

        return $label.' - '.$student->email;
    }

    protected function formatSuccessMessage(array $result): string
    {
        if (($result['operation'] ?? '') === PointsBulkGrantService::OPERATION_BACKFILL) {
            return sprintf(
                'تم التعويض: %d طالب، %d نشاطاً ممنوحاً، %s نقطة إجمالاً.',
                $result['students_with_awards'] ?? 0,
                $result['activities_awarded'] ?? 0,
                number_format($result['points_awarded'] ?? 0)
            );
        }

        return sprintf(
            'تم تنفيذ العملية على %d طالب (%d نجح، %d فشل) — إجمالي %s نقطة.',
            $result['total_students'] ?? 0,
            $result['awarded'] ?? 0,
            $result['failed'] ?? 0,
            number_format($result['total_points'] ?? 0)
        );
    }
}
