<?php



namespace App\Http\Controllers\Admin;



use App\Http\Controllers\Controller;

use App\Models\Course;

use App\Models\CourseGroup;

use App\Models\StudentWeeklyReport;

use App\Services\Reports\StudentWeeklyReportService;

use Illuminate\Http\Request;



class StudentWeeklyReportController extends Controller

{

    public function __construct(private readonly StudentWeeklyReportService $reportService)

    {

    }



    public function index(Request $request)

    {

        [$courseId, $groupId] = $this->parseAdminFilters($request);

        $filterOptions = $this->reportService->getAdminFilterOptions($courseId);



        $submittedReports = $this->reportService->getSubmittedReportsForAdmin($courseId, $groupId);



        $groupsWithSubmittedReports = $submittedReports

            ->groupBy('target_group_id')

            ->map(function ($reports, $groupKey) {

                $group = $reports->first()->targetGroup;



                return [

                    'group_id' => (int) $groupKey,

                    'group_name' => $group?->name ?? 'غير محدد',

                    'reports' => $reports

                        ->sortByDesc(fn (StudentWeeklyReport $report) => $report->submitted_at ?? $report->updated_at)

                        ->values(),

                    'submissions_count' => $reports->count(),

                ];

            })

            ->sortByDesc('submissions_count')

            ->values();



        $totalSubmittedReports = $groupsWithSubmittedReports->sum('submissions_count');

        $groupsWithSubmissionsCount = $groupsWithSubmittedReports->count();



        return view('admin.weekly-reports.index', [

            'groupsWithSubmittedReports' => $groupsWithSubmittedReports,

            'totalSubmittedReports' => $totalSubmittedReports,

            'groupsWithSubmissionsCount' => $groupsWithSubmissionsCount,

            'filterOptions' => $filterOptions,

            'filters' => [

                'course_id' => $courseId,

                'group_id' => $groupId,

            ],

        ]);

    }



    public function create()

    {

        $courses = Course::query()

            ->whereHas('groups')

            ->orderBy('title')

            ->get(['id', 'title']);



        $groups = CourseGroup::query()

            ->with(['courses:id,title'])

            ->orderBy('name')

            ->get(['id', 'name']);



        return view('admin.weekly-reports.create', compact('courses', 'groups'));

    }



    public function store(Request $request)

    {

        $validated = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'group_id' => ['required', 'integer', 'exists:course_groups,id'],
            'report_title' => ['required', 'string', 'max:255'],
            'report_description' => ['nullable', 'string', 'max:50000'],
            'due_at' => ['nullable', 'date'],
        ]);



        $students = $this->reportService->resolveStudentsByCourseAndGroup(

            (int) $validated['course_id'],

            (int) $validated['group_id']

        );



        if ($students->isEmpty()) {

            return back()->withErrors([

                'group_id' => 'لا يوجد طلاب داخل هذه المجموعة.',

            ])->withInput();

        }



        foreach ($students as $student) {

            $this->reportService->createManualReport(
                $student,
                (int) auth()->id(),
                $validated['report_title'],
                isset($validated['due_at']) ? new \DateTimeImmutable($validated['due_at']) : null,
                (int) $validated['course_id'],
                (int) $validated['group_id'],
                isset($validated['report_description']) ? trim($validated['report_description']) : null
            );

        }



        $count = $students->count();



        return back()->with(

            'success',

            $count === 1

                ? 'تم إنشاء تقرير واحد للطالب بنجاح.'

                : "تم إنشاء {$count} تقارير للطلاب بنجاح."

        );

    }



    public function groupsOverview(Request $request)

    {

        [$courseId, $groupId] = $this->parseAdminFilters($request);

        $filterOptions = $this->reportService->getAdminFilterOptions($courseId);



        $overview = $this->reportService->getGroupsOverviewData($courseId, $groupId);



        return view('admin.weekly-reports.groups-overview', [

            'groupsData' => $overview['groupsData'],

            'totalCreatedReports' => $overview['totalCreatedReports'],

            'totalSubmittedReports' => $overview['totalSubmittedReports'],

            'totalPendingReports' => $overview['totalPendingReports'],

            'filterOptions' => $filterOptions,

            'filters' => [

                'course_id' => $courseId,

                'group_id' => $groupId,

            ],

        ]);

    }



    public function pendingReports(Request $request)

    {

        [$courseId, $groupId] = $this->parseAdminFilters($request);

        $filterOptions = $this->reportService->getAdminFilterOptions($courseId);



        $pendingReports = $this->reportService->getPendingReportsForAdmin($courseId, $groupId);



        $groupsWithPendingReports = $pendingReports

            ->groupBy('target_group_id')

            ->map(function ($reports, $groupKey) {

                $group = $reports->first()->targetGroup;



                return [

                    'group_id' => (int) $groupKey,

                    'group_name' => $group?->name ?? 'غير محدد',

                    'reports' => $reports->values(),

                    'pending_count' => $reports->count(),

                ];

            })

            ->sortByDesc('pending_count')

            ->values();



        $draftCount = $pendingReports->where('status', StudentWeeklyReport::STATUS_DRAFT)->count();

        $closedCount = $pendingReports->where('status', StudentWeeklyReport::STATUS_CLOSED)->count();



        return view('admin.weekly-reports.pending', [

            'groupsWithPendingReports' => $groupsWithPendingReports,

            'totalPendingReports' => $pendingReports->count(),

            'draftCount' => $draftCount,

            'closedCount' => $closedCount,

            'groupsWithPendingCount' => $groupsWithPendingReports->count(),

            'filterOptions' => $filterOptions,

            'filters' => [

                'course_id' => $courseId,

                'group_id' => $groupId,

            ],

        ]);

    }



    public function allReports(Request $request)
    {
        [$courseId, $groupId] = $this->parseAdminFilters($request);
        $status = $this->parseAdminStatusFilter($request);
        $filterOptions = $this->reportService->getAdminFilterOptions($courseId);

        $reports = $this->reportService->getAllReportsForAdmin($courseId, $groupId, $status);
        $statusCounts = $this->reportService->getAllReportsStatusCounts($courseId, $groupId);

        return view('admin.weekly-reports.all', [
            'reports' => $reports,
            'statusCounts' => $statusCounts,
            'filterOptions' => $filterOptions,
            'filters' => [
                'course_id' => $courseId,
                'group_id' => $groupId,
                'status' => $status,
            ],
        ]);
    }

    public function createdReports(Request $request)
    {
        [$courseId, $groupId] = $this->parseAdminFilters($request);
        $filterOptions = $this->reportService->getAdminFilterOptions($courseId);

        $batches = $this->reportService->getAdminCreatedReportBatches($courseId, $groupId);
        $batchStats = $this->reportService->getAdminCreatedBatchStats($courseId, $groupId);

        return view('admin.weekly-reports.created', [
            'batches' => $batches,
            'batchStats' => $batchStats,
            'filterOptions' => $filterOptions,
            'filters' => [
                'course_id' => $courseId,
                'group_id' => $groupId,
            ],
        ]);
    }

    public function showCreatedBatch(Request $request)
    {
        $batchKey = $request->query('batch');

        if (!is_string($batchKey) || $batchKey === '') {
            abort(404);
        }

        $batch = $this->reportService->getAdminCreatedBatchByKey($batchKey);

        if ($batch === null) {
            abort(404);
        }

        $search = $request->input('search');
        $status = $this->parseAdminStatusFilter($request);
        $batch = $this->reportService->filterAdminCreatedBatchStudents($batch, $search, $status);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'table_html' => view('admin.weekly-reports.partials.created-batch-students-table', [
                    'batch' => $batch,
                ])->render(),
                'count' => $batch['filtered_count'],
                'total' => $batch['students_count'],
            ]);
        }

        return view('admin.weekly-reports.created-batch', [
            'batch' => $batch,
            'batchKey' => $batchKey,
            'filters' => [
                'search' => $search ?? '',
                'status' => $status ?? '',
            ],
        ]);
    }

    public function editCreatedBatch(Request $request)
    {
        $batchKey = $request->query('batch');

        if (! is_string($batchKey) || $batchKey === '') {
            abort(404);
        }

        $batch = $this->reportService->getAdminCreatedBatchByKey($batchKey);

        if ($batch === null) {
            abort(404);
        }

        return view('admin.weekly-reports.edit-batch', [
            'batch' => $batch,
            'batchKey' => $batchKey,
        ]);
    }

    public function updateCreatedBatch(Request $request)
    {
        $batchKey = $request->input('batch');

        if (! is_string($batchKey) || $batchKey === '') {
            abort(404);
        }

        $validated = $request->validate([
            'batch' => ['required', 'string'],
            'report_title' => ['required', 'string', 'max:255'],
            'report_description' => ['nullable', 'string', 'max:50000'],
            'due_at' => ['nullable', 'date'],
        ]);

        $newBatchKey = $this->reportService->updateAdminCreatedBatch($batchKey, [
            'report_title' => $validated['report_title'],
            'report_description' => isset($validated['report_description'])
                ? trim($validated['report_description'])
                : null,
            'due_at' => $validated['due_at'] ?? null,
        ]);

        return redirect()
            ->route('admin.weekly-reports.created.batch', ['batch' => $newBatchKey])
            ->with('success', 'تم تحديث بيانات التقرير لجميع الطلاب في الدفعة.');
    }

    public function destroyCreatedBatch(Request $request)
    {
        $batchKey = $request->input('batch');

        if (! is_string($batchKey) || $batchKey === '') {
            abort(404);
        }

        $deleted = $this->reportService->deleteAdminCreatedBatch($batchKey);

        return redirect()
            ->route('admin.weekly-reports.created')
            ->with('success', $deleted === 1
                ? 'تم حذف تقرير واحد بنجاح.'
                : "تم حذف {$deleted} تقارير من الدفعة بنجاح.");
    }

    public function show(StudentWeeklyReport $weeklyReport)
    {
        $weeklyReport->load([
            'student',
            'targetCourse',
            'targetGroup',
            'createdByAdmin:id,name,name_ar',
            'selectedLessons.lesson',
            'selectedLessons.module.section',
            'selectedLessons.course',
        ]);

        $selectedLessonGroups = $this->reportService->groupSelectedLessonsBySectionForDisplay($weeklyReport);

        $courseProgress = $this->reportService->calculateVisibleCourseProgressForStudentReport(
            (int) $weeklyReport->student_id,
            $weeklyReport
        );

        return view('admin.weekly-reports.show', [
            'report' => $weeklyReport,
            'selectedLessonGroups' => $selectedLessonGroups,
            'courseProgress' => $courseProgress,
        ]);
    }



    public function feedback(Request $request, StudentWeeklyReport $weeklyReport)

    {

        $validated = $request->validate([

            'admin_feedback' => ['required', 'string'],

        ]);



        $this->reportService->addAdminFeedback($weeklyReport, $validated['admin_feedback']);



        return back()->with('success', 'تم حفظ تعليق الأدمن.');

    }



    private function parseAdminFilters(Request $request): array

    {

        $courseId = $request->filled('course_id') ? (int) $request->input('course_id') : null;

        $groupId = $request->filled('group_id') ? (int) $request->input('group_id') : null;



        if ($courseId <= 0) {

            $courseId = null;

        }



        if ($groupId <= 0) {

            $groupId = null;

        }



        return [$courseId, $groupId];
    }

    private function parseAdminStatusFilter(Request $request): ?string
    {
        $status = $request->input('status');
        $allowedStatuses = [
            StudentWeeklyReport::STATUS_DRAFT,
            StudentWeeklyReport::STATUS_SUBMITTED,
            StudentWeeklyReport::STATUS_REVIEWED,
            StudentWeeklyReport::STATUS_CLOSED,
        ];

        if (!is_string($status) || !in_array($status, $allowedStatuses, true)) {
            return null;
        }

        return $status;
    }
}


