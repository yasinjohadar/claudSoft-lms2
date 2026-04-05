<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\UsesLaravelAiSdkForWizards;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateStudentCourseAiReportJob;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseGroup;
use App\Models\LaravelAiModel;
use App\Models\StudentCourseAiReport;
use App\Models\StudentCourseAiReportBatch;
use App\Models\User;
use App\Services\Reports\StudentCourseReportDataBuilder;
use App\Services\Reports\StudentCourseReportNarrativeService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentCourseAiReportController extends Controller
{
    use UsesLaravelAiSdkForWizards;

    public function index(): View
    {
        $reports = StudentCourseAiReport::query()
            ->with(['student', 'course', 'creator'])
            ->latest()
            ->paginate(25);

        $recentBatches = StudentCourseAiReportBatch::query()
            ->with(['course', 'courseGroup'])
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.ai.student-progress-reports.index', compact('reports', 'recentBatches'));
    }

    public function batchesIndex(): View
    {
        $batches = StudentCourseAiReportBatch::query()
            ->with(['course', 'creator', 'courseGroup'])
            ->latest()
            ->paginate(25);

        return view('admin.ai.student-progress-reports.batches-index', compact('batches'));
    }

    public function showBatch(StudentCourseAiReportBatch $batch): View
    {
        $batch->load([
            'course',
            'creator',
            'courseGroup',
            'laravelAiModel',
            'items.student',
            'items.courseGroup',
            'items.report',
        ]);

        $itemsByGroup = $batch->items->groupBy('course_group_id');

        return view('admin.ai.student-progress-reports.batch-show', compact('batch', 'itemsByGroup'));
    }

    public function show(StudentCourseAiReport $report): View
    {
        $report->load(['student', 'course', 'creator', 'laravelAiModel']);

        return view('admin.ai.student-progress-reports.show', compact('report'));
    }

    public function create(Request $request): View
    {
        $courses = Course::query()->where('is_published', true)->orderBy('title')->get();
        $useLaravelAi = $this->wizardUsesLaravelAiSdk('reports_engine');
        $laravelAiModels = $useLaravelAi
            ? LaravelAiModel::query()->activeOrdered()->get()
            : collect();

        return view('admin.ai.student-progress-reports.create', compact(
            'courses',
            'useLaravelAi',
            'laravelAiModels',
        ));
    }

    public function enrolledStudents(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $ids = CourseEnrollment::query()
            ->active()
            ->where('course_id', $request->integer('course_id'))
            ->pluck('student_id');

        $students = User::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return response()->json($students);
    }

    public function courseGroups(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $course = Course::query()->findOrFail($request->integer('course_id'));
        // لا تستخدم get(['id','name']) هنا: جدول course_group_courses يحتوي id فيُنتج SQL غامض (Column 'id' in field list is ambiguous).
        $groups = $course->groups()->orderBy('course_groups.name')->get();

        return response()->json(
            $groups->map(fn (CourseGroup $g) => [
                'id' => $g->id,
                'name' => $g->name,
            ])->values()
        );
    }

    public function preview(
        Request $request,
        StudentCourseReportDataBuilder $builder,
        StudentCourseReportNarrativeService $narrativeService,
    ): View|RedirectResponse {
        if (! $this->wizardUsesLaravelAiSdk('reports_engine')) {
            return redirect()->route('admin.ai.student-progress-reports.create')
                ->with('error', 'تقارير الدراسة تتطلب تفعيل Laravel AI SDK (موديل نشط أو AI_REPORTS_ENGINE / المحرك العام).');
        }

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'student_id' => 'required|exists:users,id',
            'attempt_strategy' => 'required|in:best,latest',
            'laravel_ai_model_id' => 'nullable|exists:laravel_ai_models,id',
            'since' => 'nullable|date',
        ]);

        $course = Course::query()->findOrFail($validated['course_id']);
        $student = User::query()->findOrFail($validated['student_id']);

        if (! $this->studentEligibleForCourse($student, $course, null)) {
            return redirect()->back()
                ->with('error', 'الطالب غير مسجّل بشكل فعّال في هذا الكورس.')
                ->withInput();
        }

        $since = ! empty($validated['since']) ? Carbon::parse($validated['since']) : null;
        $facts = $builder->build($student, $course, $validated['attempt_strategy'], $since);

        try {
            $model = $this->resolveLaravelModel($validated['laravel_ai_model_id'] ?? null);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }

        set_time_limit(180);
        $result = $narrativeService->generate($facts, $model, $request->user());
        $narrative = $result['narrative'];

        return view('admin.ai.student-progress-reports.preview', compact('facts', 'narrative', 'course', 'student'));
    }

    public function dispatchBatch(Request $request): RedirectResponse
    {
        if (! $this->wizardUsesLaravelAiSdk('reports_engine')) {
            return redirect()->back()
                ->with('error', 'تقارير الدراسة تتطلب تفعيل Laravel AI SDK.');
        }

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'batch_scope' => 'required|in:single_group,all_groups_in_course',
            'course_group_id' => 'required_if:batch_scope,single_group|nullable|exists:course_groups,id',
            'attempt_strategy' => 'required|in:best,latest',
            'laravel_ai_model_id' => 'nullable|exists:laravel_ai_models,id',
            'since' => 'nullable|date',
        ]);

        $course = Course::query()->findOrFail($validated['course_id']);

        $groups = collect();
        if ($validated['batch_scope'] === 'single_group') {
            $group = CourseGroup::query()->findOrFail($validated['course_group_id']);
            if (! $group->courses()->where('courses.id', $course->id)->exists()) {
                return redirect()->back()
                    ->with('error', 'المجموعة المختارة غير مرتبطة بهذا الكورس.')
                    ->withInput();
            }
            $groups = collect([$group]);
        } else {
            $groups = $course->groups()->orderBy('course_groups.name')->get();
            if ($groups->isEmpty()) {
                return redirect()->back()
                    ->with('error', 'لا توجد مجموعات مرتبطة بهذا الكورس. أضف مجموعة واربطها بالكورس أولاً.')
                    ->withInput();
            }
        }

        try {
            $this->resolveLaravelModel($validated['laravel_ai_model_id'] ?? null);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }

        $pairs = $this->studentGroupPairsForBatch($course, $groups);
        if ($pairs->isEmpty()) {
            return redirect()->back()
                ->with('error', 'لا يوجد طلاب مؤهلون (تسجيل فعّال في الكورس وعضوية في المجموعة/المجموعات المختارة).')
                ->withInput();
        }

        $sinceForJob = ! empty($validated['since']) ? Carbon::parse($validated['since'])->toIso8601String() : null;
        $sinceForBatch = ! empty($validated['since']) ? Carbon::parse($validated['since'])->toDateString() : null;
        $adminId = (int) $request->user()->id;
        $strategy = $validated['attempt_strategy'];
        $modelId = $validated['laravel_ai_model_id'] ?? null;
        $singleGroupId = $validated['batch_scope'] === 'single_group'
            ? (int) $validated['course_group_id']
            : null;

        $batch = DB::transaction(function () use ($course, $adminId, $strategy, $sinceForBatch, $modelId, $validated, $singleGroupId, $pairs) {
            $b = StudentCourseAiReportBatch::query()->create([
                'course_id' => $course->id,
                'created_by' => $adminId,
                'attempt_strategy' => $strategy,
                'since' => $sinceForBatch,
                'laravel_ai_model_id' => $modelId ? (int) $modelId : null,
                'scope' => $validated['batch_scope'],
                'course_group_id' => $singleGroupId,
                'status' => 'running',
                'items_total' => 0,
                'items_succeeded' => 0,
                'items_failed' => 0,
                'items_skipped' => 0,
            ]);

            foreach ($pairs as $pair) {
                $b->items()->create([
                    'student_id' => $pair['student_id'],
                    'course_group_id' => $pair['course_group_id'],
                    'status' => 'queued',
                ]);
            }

            $b->update(['items_total' => $b->items()->count()]);

            return $b->fresh(['items']);
        });

        foreach ($batch->items as $item) {
            GenerateStudentCourseAiReportJob::dispatch(
                (int) $item->student_id,
                (int) $course->id,
                (int) $item->course_group_id,
                $adminId,
                $strategy,
                $modelId ? (int) $modelId : null,
                $sinceForJob,
                (int) $item->id,
            );
        }

        return redirect()->route('admin.ai.student-progress-reports.batches.show', $batch)
            ->with('success', 'تمت جدولة '.$batch->items_total.' تقرير دراسة ضمن الدفعة #'.$batch->id.'. شغّل عامل الطابور إن لم يكن يعمل، ثم تابع الحالة من صفحة الدفعة.');
    }

    /**
     * @param  Collection<int, CourseGroup>  $groups
     * @return Collection<int, array{student_id: int, course_group_id: int}>
     */
    private function studentGroupPairsForBatch(Course $course, Collection $groups): Collection
    {
        $pairs = collect();
        foreach ($groups as $group) {
            if (! $group->courses()->where('courses.id', $course->id)->exists()) {
                continue;
            }
            $ids = $this->resolveStudentIdsForBatch($course, $group);
            foreach ($ids as $sid) {
                $pairs->push([
                    'student_id' => (int) $sid,
                    'course_group_id' => (int) $group->id,
                ]);
            }
        }

        return $pairs;
    }

    /**
     * @return Collection<int, int>
     */
    private function resolveStudentIdsForBatch(Course $course, ?CourseGroup $group): Collection
    {
        $ids = CourseEnrollment::query()
            ->active()
            ->where('course_id', $course->id)
            ->pluck('student_id');

        if ($group) {
            $groupIds = $group->students()->pluck('users.id');

            return $ids->intersect($groupIds)->values();
        }

        return $ids->values();
    }

    private function studentEligibleForCourse(User $student, Course $course, ?CourseGroup $group): bool
    {
        $enrolled = CourseEnrollment::query()
            ->active()
            ->where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->exists();

        if (! $enrolled) {
            return false;
        }

        if ($group) {
            return $group->students()->where('users.id', $student->id)->exists();
        }

        return true;
    }

    private function resolveLaravelModel(?int $id): LaravelAiModel
    {
        if ($id) {
            $m = LaravelAiModel::query()->whereKey($id)->where('is_active', true)->first();
            if ($m) {
                return $m;
            }
            throw new \InvalidArgumentException('موديل Laravel AI غير متاح أو غير نشط.');
        }

        $fallback = LaravelAiModel::query()->activeOrdered()->forCapability('reports.student_progress')->first()
            ?? LaravelAiModel::query()->activeOrdered()->forCapability('content.general')->first()
            ?? LaravelAiModel::query()->activeOrdered()->first();

        if (! $fallback) {
            throw new \RuntimeException('لا يوجد موديل Laravel AI نشط. أضف موديلاً من «موديلات Laravel AI SDK».');
        }

        return $fallback;
    }
}
