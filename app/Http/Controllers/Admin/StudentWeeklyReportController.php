<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\StudentWeeklyReport;
use App\Services\Reports\StudentWeeklyReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentWeeklyReportController extends Controller
{
    public function __construct(private readonly StudentWeeklyReportService $reportService)
    {
    }

    public function index(Request $request)
    {
        $submittedStatuses = [
            StudentWeeklyReport::STATUS_SUBMITTED,
            StudentWeeklyReport::STATUS_REVIEWED,
        ];

        $submittedReports = StudentWeeklyReport::query()
            ->with([
                'student:id,name,name_ar',
                'selectedLessons:id,student_weekly_report_id,course_id,module_id,lesson_id',
            ])
            ->whereIn('status', $submittedStatuses)
            ->latest('submitted_at')
            ->latest('id')
            ->get();

        $studentIds = $submittedReports->pluck('student_id')->unique()->values();
        $courseIds = $submittedReports
            ->flatMap(fn (StudentWeeklyReport $report) => $report->selectedLessons->pluck('course_id'))
            ->filter()
            ->unique()
            ->values();

        $groupMemberships = collect();
        if ($studentIds->isNotEmpty() && $courseIds->isNotEmpty()) {
            $groupMemberships = DB::table('course_group_members as cgm')
                ->join('course_group_courses as cgc', 'cgc.group_id', '=', 'cgm.group_id')
                ->join('course_groups as cg', 'cg.id', '=', 'cgm.group_id')
                ->whereIn('cgm.student_id', $studentIds)
                ->whereIn('cgc.course_id', $courseIds)
                ->select([
                    'cgm.student_id',
                    'cgc.course_id',
                    'cg.id as group_id',
                    'cg.name as group_name',
                ])
                ->get();
        }

        $groupsMap = [];
        foreach ($submittedReports as $report) {
            $reportCourseIds = $report->selectedLessons->pluck('course_id')->filter()->unique();
            if ($reportCourseIds->isEmpty()) {
                continue;
            }

            $matches = $groupMemberships
                ->where('student_id', $report->student_id)
                ->whereIn('course_id', $reportCourseIds);

            foreach ($matches as $match) {
                $groupId = (int) $match->group_id;

                if (!isset($groupsMap[$groupId])) {
                    $groupsMap[$groupId] = [
                        'group_id' => $groupId,
                        'group_name' => $match->group_name,
                        'reports' => collect(),
                    ];
                }

                if (!$groupsMap[$groupId]['reports']->contains('id', $report->id)) {
                    $groupsMap[$groupId]['reports']->push($report);
                }
            }
        }

        $groupsWithSubmittedReports = collect($groupsMap)
            ->map(function (array $group) {
                $group['reports'] = $group['reports']
                    ->sortByDesc(fn (StudentWeeklyReport $report) => $report->submitted_at ?? $report->updated_at)
                    ->values();
                $group['submissions_count'] = $group['reports']->count();
                return $group;
            })
            ->sortByDesc('submissions_count')
            ->values();

        $totalSubmittedReports = $groupsWithSubmittedReports->sum('submissions_count');
        $groupsWithSubmissionsCount = $groupsWithSubmittedReports->count();

        return view('admin.weekly-reports.index', compact(
            'groupsWithSubmittedReports',
            'totalSubmittedReports',
            'groupsWithSubmissionsCount'
        ));
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
                (int) $validated['group_id']
            );
        }

        return redirect()->route('admin.weekly-reports.index')->with('success', 'تم إنشاء التقارير الأسبوعية.');
    }

    public function groupsOverview()
    {
        $groups = CourseGroup::query()
            ->whereHas('courses')
            ->withCount([
                'students as members_count',
            ])
            ->orderBy('name')
            ->get(['id', 'name']);

        $reportsByGroup = StudentWeeklyReport::query()
            ->with(['student:id,name,name_ar', 'targetGroup:id,name'])
            ->whereNotNull('target_group_id')
            ->orderByDesc('id')
            ->get()
            ->groupBy('target_group_id');

        $groupsData = $groups->map(function (CourseGroup $group) use ($reportsByGroup) {
            $reports = collect($reportsByGroup->get($group->id, []));
            $submittedReports = $reports
                ->whereIn('status', [StudentWeeklyReport::STATUS_SUBMITTED, StudentWeeklyReport::STATUS_REVIEWED])
                ->sortByDesc(fn (StudentWeeklyReport $report) => $report->submitted_at ?? $report->updated_at)
                ->values();

            return [
                'group' => $group,
                'total_reports_count' => $reports->count(),
                'submitted_reports_count' => $submittedReports->count(),
                'submitted_reports' => $submittedReports,
            ];
        })->filter(fn (array $item) => $item['total_reports_count'] > 0)->values();

        return view('admin.weekly-reports.groups-overview', [
            'groupsData' => $groupsData,
            'totalCreatedReports' => $groupsData->sum('total_reports_count'),
            'totalSubmittedReports' => $groupsData->sum('submitted_reports_count'),
        ]);
    }

    public function show(StudentWeeklyReport $weeklyReport)
    {
        $weeklyReport->load('student', 'selectedLessons.lesson', 'selectedLessons.module', 'selectedLessons.course');
        return view('admin.weekly-reports.show', ['report' => $weeklyReport]);
    }

    public function feedback(Request $request, StudentWeeklyReport $weeklyReport)
    {
        $validated = $request->validate([
            'admin_feedback' => ['required', 'string'],
        ]);

        $this->reportService->addAdminFeedback($weeklyReport, $validated['admin_feedback']);

        return back()->with('success', 'تم حفظ تعليق الأدمن.');
    }
}

