<?php

namespace App\Services\Reports;

use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\CourseModule;
use App\Models\ModuleCompletion;
use App\Models\StudentWeeklyReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentWeeklyReportService
{
    public function applyAdminFilters(Builder $query, ?int $courseId, ?int $groupId): Builder
    {
        if ($courseId > 0) {
            $query->where('target_course_id', $courseId);
        }

        if ($groupId > 0) {
            $query->where('target_group_id', $groupId);
        }

        return $query;
    }

    public function applyAdminStatusFilter(Builder $query, ?string $status): Builder
    {
        $allowedStatuses = [
            StudentWeeklyReport::STATUS_DRAFT,
            StudentWeeklyReport::STATUS_SUBMITTED,
            StudentWeeklyReport::STATUS_REVIEWED,
            StudentWeeklyReport::STATUS_CLOSED,
        ];

        if ($status && in_array($status, $allowedStatuses, true)) {
            $query->where('status', $status);
        }

        return $query;
    }

    public function buildAllReportsAdminQuery(?int $courseId = null, ?int $groupId = null, ?string $status = null): Builder
    {
        $query = StudentWeeklyReport::query()
            ->with([
                'student:id,name,name_ar,email,phone,country_code,full_phone',
                'targetCourse:id,title',
                'targetGroup:id,name',
                'createdByAdmin:id,name,name_ar',
            ])
            ->latest('id');

        $this->applyAdminFilters($query, $courseId, $groupId);
        $this->applyAdminStatusFilter($query, $status);

        return $query;
    }

    public function getAllReportsForAdmin(
        ?int $courseId = null,
        ?int $groupId = null,
        ?string $status = null,
        int $perPage = 20
    ): \Illuminate\Contracts\Pagination\LengthAwarePaginator {
        return $this->buildAllReportsAdminQuery($courseId, $groupId, $status)->paginate($perPage);
    }

    public function getAllReportsStatusCounts(?int $courseId = null, ?int $groupId = null): array
    {
        $query = StudentWeeklyReport::query();
        $this->applyAdminFilters($query, $courseId, $groupId);

        $counts = $query
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => (int) $counts->sum(),
            'draft' => (int) ($counts[StudentWeeklyReport::STATUS_DRAFT] ?? 0),
            'submitted' => (int) ($counts[StudentWeeklyReport::STATUS_SUBMITTED] ?? 0),
            'reviewed' => (int) ($counts[StudentWeeklyReport::STATUS_REVIEWED] ?? 0),
            'closed' => (int) ($counts[StudentWeeklyReport::STATUS_CLOSED] ?? 0),
        ];
    }

    public function getAdminCreatedReportsQuery(?int $courseId = null, ?int $groupId = null): Builder
    {
        $query = StudentWeeklyReport::query()
            ->adminCreated()
            ->with([
                'student:id,name,name_ar,email,phone,country_code,full_phone',
                'targetCourse:id,title',
                'targetGroup:id,name',
                'createdByAdmin:id,name,name_ar',
            ])
            ->latest('created_at')
            ->latest('id');

        return $this->applyAdminFilters($query, $courseId, $groupId);
    }

    public function getAdminCreatedBatchByKey(string $batchKey): ?array
    {
        $reports = $this->getAdminCreatedReportsQuery()
            ->get()
            ->filter(fn (StudentWeeklyReport $report) => $this->adminCreatedBatchKey($report) === $batchKey);

        if ($reports->isEmpty()) {
            return null;
        }

        return $this->formatAdminCreatedBatch($reports);
    }

    public function filterAdminCreatedBatchStudents(array $batch, ?string $search = null, ?string $status = null): array
    {
        $reports = $batch['student_reports'];

        if ($status && in_array($status, [
            StudentWeeklyReport::STATUS_DRAFT,
            StudentWeeklyReport::STATUS_SUBMITTED,
            StudentWeeklyReport::STATUS_REVIEWED,
            StudentWeeklyReport::STATUS_CLOSED,
        ], true)) {
            $reports = $reports->filter(fn (StudentWeeklyReport $report) => $report->status === $status);
        }

        $search = is_string($search) ? trim($search) : '';
        if ($search !== '') {
            $needle = mb_strtolower($search);
            $reports = $reports->filter(function (StudentWeeklyReport $report) use ($needle) {
                $student = $report->student;
                $haystack = mb_strtolower(implode(' ', array_filter([
                    $student->name ?? '',
                    $student->name_ar ?? '',
                    $student->email ?? '',
                ])));

                return str_contains($haystack, $needle);
            });
        }

        $filteredReports = $reports->values();

        return array_merge($batch, [
            'student_reports' => $filteredReports,
            'filtered_count' => $filteredReports->count(),
            'is_filtered' => $search !== '' || ($status !== null && $status !== ''),
        ]);
    }

    public function getAdminCreatedReportBatches(
        ?int $courseId = null,
        ?int $groupId = null,
        int $perPage = 15,
        ?int $page = null
    ): \Illuminate\Contracts\Pagination\LengthAwarePaginator {
        $query = StudentWeeklyReport::query()
            ->adminCreated()
            ->with([
                'targetCourse:id,title',
                'targetGroup:id,name',
                'createdByAdmin:id,name,name_ar',
            ])
            ->latest('created_at')
            ->latest('id');

        $this->applyAdminFilters($query, $courseId, $groupId);
        $reports = $query->get();

        $batches = $reports
            ->groupBy(fn (StudentWeeklyReport $report) => $this->adminCreatedBatchKey($report))
            ->map(fn (Collection $groupReports) => $this->formatAdminCreatedBatchSummary($groupReports))
            ->sortByDesc('created_at')
            ->values();

        $page = $page ?? (int) request()->input('page', 1);
        $total = $batches->count();
        $items = $batches->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function getAdminCreatedBatchStats(?int $courseId = null, ?int $groupId = null): array
    {
        $reports = $this->getAdminCreatedReportsQuery($courseId, $groupId)->get();
        $batches = $reports->groupBy(fn (StudentWeeklyReport $report) => $this->adminCreatedBatchKey($report));

        $submittedStatuses = [
            StudentWeeklyReport::STATUS_SUBMITTED,
            StudentWeeklyReport::STATUS_REVIEWED,
        ];

        return [
            'batches_count' => $batches->count(),
            'students_count' => $reports->count(),
            'submitted_count' => $reports->whereIn('status', $submittedStatuses)->count(),
            'pending_count' => $reports->whereNull('submitted_at')
                ->whereIn('status', [StudentWeeklyReport::STATUS_DRAFT, StudentWeeklyReport::STATUS_CLOSED])
                ->count(),
        ];
    }

    public function getAdminFilterOptions(?int $courseId = null): array
    {
        $courseIds = StudentWeeklyReport::query()
            ->whereNotNull('target_course_id')
            ->distinct()
            ->pluck('target_course_id');

        $courses = Course::query()
            ->whereIn('id', $courseIds)
            ->orderBy('title')
            ->get(['id', 'title']);

        $groupQuery = CourseGroup::query()
            ->whereIn('id', function ($query) {
                $query->select('target_group_id')
                    ->from('student_weekly_reports')
                    ->whereNotNull('target_group_id')
                    ->distinct();
            })
            ->with(['courses:id']);

        if ($courseId > 0) {
            $groupQuery->whereHas('courses', fn ($query) => $query->where('courses.id', $courseId));
        }

        $groups = $groupQuery->orderBy('name')->get(['id', 'name']);

        return [
            'courses' => $courses,
            'groups' => $groups,
        ];
    }

    public function getSubmittedReportsForAdmin(?int $courseId = null, ?int $groupId = null): Collection
    {
        $query = StudentWeeklyReport::query()
            ->submitted()
            ->with([
                'student:id,name,name_ar',
                'targetCourse:id,title',
                'targetGroup:id,name',
            ])
            ->latest('submitted_at')
            ->latest('id');

        return $this->applyAdminFilters($query, $courseId, $groupId)->get();
    }

    public function getPendingReportsForAdmin(?int $courseId = null, ?int $groupId = null): Collection
    {
        $query = StudentWeeklyReport::query()
            ->notSubmitted()
            ->with([
                'student:id,name,name_ar',
                'targetCourse:id,title',
                'targetGroup:id,name',
            ])
            ->latest('due_at')
            ->latest('id');

        return $this->applyAdminFilters($query, $courseId, $groupId)->get();
    }

    public function getGroupsOverviewData(?int $courseId = null, ?int $groupId = null): array
    {
        $groupsQuery = CourseGroup::query()
            ->whereHas('courses')
            ->orderBy('name');

        if ($groupId > 0) {
            $groupsQuery->where('id', $groupId);
        } elseif ($courseId > 0) {
            $groupsQuery->whereHas('courses', fn ($query) => $query->where('courses.id', $courseId));
        }

        $groups = $groupsQuery->get(['id', 'name']);

        $reportsQuery = StudentWeeklyReport::query()
            ->with(['student:id,name,name_ar', 'targetGroup:id,name'])
            ->whereNotNull('target_group_id');

        $this->applyAdminFilters($reportsQuery, $courseId, $groupId);

        $reportsByGroup = $reportsQuery
            ->orderByDesc('id')
            ->get()
            ->groupBy('target_group_id');

        $groupsData = $groups->map(function (CourseGroup $group) use ($reportsByGroup) {
            $reports = collect($reportsByGroup->get($group->id, []));
            $submittedReports = $reports
                ->filter(fn (StudentWeeklyReport $report) => in_array($report->status, [
                    StudentWeeklyReport::STATUS_SUBMITTED,
                    StudentWeeklyReport::STATUS_REVIEWED,
                ], true))
                ->sortByDesc(fn (StudentWeeklyReport $report) => $report->submitted_at ?? $report->updated_at)
                ->values();

            return [
                'group' => $group,
                'total_reports_count' => $reports->count(),
                'submitted_reports_count' => $submittedReports->count(),
                'submitted_reports' => $submittedReports,
            ];
        })->filter(fn (array $item) => $item['total_reports_count'] > 0)->values();

        $totalCreatedReports = $groupsData->sum('total_reports_count');
        $totalSubmittedReports = $groupsData->sum('submitted_reports_count');

        return [
            'groupsData' => $groupsData,
            'totalCreatedReports' => $totalCreatedReports,
            'totalSubmittedReports' => $totalSubmittedReports,
            'totalPendingReports' => max(0, $totalCreatedReports - $totalSubmittedReports),
        ];
    }

    public function resolveStudentsByCourseAndGroup(int $courseId, int $groupId): Collection
    {
        if ($courseId <= 0 || $groupId <= 0) {
            throw ValidationException::withMessages([
                'group_id' => 'يرجى اختيار الكورس والمجموعة بشكل صحيح.',
            ]);
        }

        $isGroupLinkedToCourse = DB::table('course_group_courses')
            ->where('course_id', $courseId)
            ->where('group_id', $groupId)
            ->exists();

        if (!$isGroupLinkedToCourse) {
            throw ValidationException::withMessages([
                'group_id' => 'المجموعة المحددة غير مرتبطة بالكورس المحدد.',
            ]);
        }

        return User::query()
            ->whereIn('id', function ($query) use ($groupId) {
                $query->select('student_id')
                    ->from('course_group_members')
                    ->where('group_id', $groupId);
            })
            ->orderBy('name')
            ->get();
    }

    public function resolveCoursesForStudentReport(int $studentId, StudentWeeklyReport $report): Collection
    {
        if (!empty($report->target_course_id)) {
            return Course::query()
                ->where('id', (int) $report->target_course_id)
                ->orderBy('title')
                ->get(['id', 'title']);
        }

        $courseIds = DB::table('course_group_members')
            ->join('course_group_courses', 'course_group_members.group_id', '=', 'course_group_courses.group_id')
            ->where('course_group_members.student_id', $studentId)
            ->distinct()
            ->pluck('course_group_courses.course_id');

        return Course::query()
            ->whereIn('id', $courseIds)
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    public function isCourseAllowedForStudentReport(int $studentId, StudentWeeklyReport $report, int $courseId): bool
    {
        if ($courseId <= 0) {
            return false;
        }

        return $this->resolveCoursesForStudentReport($studentId, $report)
            ->pluck('id')
            ->contains($courseId);
    }

    public function resolveGroupIdForStudentReport(int $studentId, StudentWeeklyReport $report, int $courseId): ?int
    {
        if ($courseId <= 0) {
            return null;
        }

        if (!empty($report->target_group_id)) {
            $groupId = (int) $report->target_group_id;

            $isMember = DB::table('course_group_members')
                ->where('group_id', $groupId)
                ->where('student_id', $studentId)
                ->exists();

            return $isMember ? $groupId : null;
        }

        $groupId = DB::table('course_group_members')
            ->join('course_group_courses', 'course_group_members.group_id', '=', 'course_group_courses.group_id')
            ->where('course_group_members.student_id', $studentId)
            ->where('course_group_courses.course_id', $courseId)
            ->orderBy('course_group_members.id')
            ->value('course_group_members.group_id');

        return $groupId ? (int) $groupId : null;
    }

    /**
     * @return Collection<int, array{id: int, title: string, module_type: string, type_label: string, is_completed: bool}>
     */
    public function resolveSelectableModulesForStudentReport(int $studentId, StudentWeeklyReport $report, int $courseId): Collection
    {
        $modules = $this->getFilteredSelectableModules($studentId, $report, $courseId);
        $completedModuleIds = $this->resolveCompletedModuleIds($studentId, $modules);

        return $modules
            ->map(fn (CourseModule $module) => $this->formatSelectableModule($module, $completedModuleIds))
            ->values();
    }

    /**
     * @return Collection<int, array{section_id: int, section_title: string, section_sort_order: int, modules: array<int, array{id: int, title: string, module_type: string, type_label: string, is_completed: bool}>}>
     */
    public function resolveSelectableModuleGroupsForStudentReport(int $studentId, StudentWeeklyReport $report, int $courseId): Collection
    {
        $modules = $this->getFilteredSelectableModules($studentId, $report, $courseId);
        $completedModuleIds = $this->resolveCompletedModuleIds($studentId, $modules);

        return $modules
            ->groupBy('section_id')
            ->map(function (Collection $sectionModules) use ($completedModuleIds) {
                $section = $sectionModules->first()?->section;
                $sectionTitle = $section?->title ?? 'بدون قسم';

                return [
                    'section_id' => (int) ($section?->id ?? 0),
                    'section_title' => $sectionTitle,
                    'section_sort_order' => (int) ($section?->sort_order ?? $section?->order_index ?? 0),
                    'modules' => $sectionModules
                        ->sortBy('sort_order')
                        ->values()
                        ->map(fn (CourseModule $module) => $this->formatSelectableModule($module, $completedModuleIds))
                        ->all(),
                ];
            })
            ->sortBy('section_sort_order')
            ->values();
    }

    /**
     * @return array{total_modules: int, completed_modules: int, percentage: int, course_titles: array<int, string>}
     */
    public function calculateVisibleCourseProgressForStudentReport(int $studentId, StudentWeeklyReport $report): array
    {
        $courses = $this->resolveCoursesForStudentReport($studentId, $report);
        $totalModules = 0;
        $completedModules = 0;

        foreach ($courses as $course) {
            $modules = $this->getFilteredSelectableModules($studentId, $report, (int) $course->id);
            $completedIds = $this->resolveCompletedModuleIds($studentId, $modules);
            $totalModules += $modules->count();
            $completedModules += count($completedIds);
        }

        $percentage = $totalModules > 0
            ? (int) round(($completedModules / $totalModules) * 100)
            : 0;

        return [
            'total_modules' => $totalModules,
            'completed_modules' => $completedModules,
            'percentage' => min(100, max(0, $percentage)),
            'course_titles' => $courses->pluck('title', 'id')->all(),
        ];
    }

    public function isModuleAllowedForStudentReport(
        int $studentId,
        StudentWeeklyReport $report,
        int $courseId,
        int $moduleId
    ): bool {
        if ($moduleId <= 0) {
            return false;
        }

        return $this->resolveSelectableModulesForStudentReport($studentId, $report, $courseId)
            ->contains(fn (array $module) => (int) $module['id'] === $moduleId);
    }

    public function groupSelectedLessonsByCourse(StudentWeeklyReport $report): Collection
    {
        return $report->selectedLessons
            ->groupBy('course_id')
            ->map(function (Collection $items, $courseId) {
                return [
                    'course_id' => (int) $courseId,
                    'module_ids' => $items->pluck('module_id')->filter()->values()->all(),
                ];
            })
            ->values();
    }

    /**
     * @return Collection<int, array{section_id: int, section_title: string, section_sort_order: int, items: array<int, array{id: int, module_id: int, title: string, type_label: string, is_completed: bool}>}>
     */
    public function groupSelectedLessonsBySectionForDisplay(StudentWeeklyReport $report): Collection
    {
        $report->loadMissing([
            'selectedLessons.module.section',
            'selectedLessons.lesson',
            'selectedLessons.course',
        ]);

        if ($report->selectedLessons->isEmpty()) {
            return collect();
        }

        $moduleIds = $report->selectedLessons
            ->pluck('module_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        $completedModuleIds = $moduleIds->isEmpty()
            ? []
            : $this->resolveCompletedModuleIds((int) $report->student_id, CourseModule::query()->whereIn('id', $moduleIds)->get());

        return $report->selectedLessons
            ->groupBy(fn ($item) => (int) ($item->module?->section_id ?? 0))
            ->map(function (Collection $items, $sectionId) use ($completedModuleIds) {
                $section = $items->first()?->module?->section;
                $sectionTitle = $section?->title ?? 'بدون قسم';

                return [
                    'section_id' => (int) $sectionId,
                    'section_title' => $sectionTitle,
                    'section_sort_order' => (int) ($section?->sort_order ?? $section?->order_index ?? 0),
                    'items' => $items
                        ->sortBy(fn ($item) => $item->module?->sort_order ?? 0)
                        ->values()
                        ->map(function ($item) use ($completedModuleIds) {
                            $moduleId = (int) ($item->module_id ?? 0);
                            $moduleType = $item->module?->module_type;

                            return [
                                'id' => (int) $item->id,
                                'module_id' => $moduleId,
                                'title' => $item->module?->title ?? $item->lesson?->title ?? '-',
                                'type_label' => $this->moduleTypeLabel($moduleType),
                                'is_completed' => in_array($moduleId, $completedModuleIds, true),
                            ];
                        })
                        ->all(),
                ];
            })
            ->sortBy('section_sort_order')
            ->values();
    }

    public function flattenLessonsPayload(array $lessons): array
    {
        $flattened = [];

        foreach ($lessons as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $courseId = (int) ($entry['course_id'] ?? 0);
            if ($courseId <= 0) {
                continue;
            }

            if (!empty($entry['module_id'])) {
                $flattened[] = [
                    'course_id' => $courseId,
                    'module_id' => (int) $entry['module_id'],
                ];
                continue;
            }

            $moduleIds = $entry['module_ids'] ?? [];
            if (!is_array($moduleIds)) {
                continue;
            }

            foreach ($moduleIds as $moduleId) {
                $moduleId = (int) $moduleId;
                if ($moduleId <= 0) {
                    continue;
                }

                $flattened[] = [
                    'course_id' => $courseId,
                    'module_id' => $moduleId,
                ];
            }
        }

        return $flattened;
    }

    public function createManualReport(
        User $student,
        int $adminId,
        string $title,
        ?\DateTimeInterface $dueAt,
        ?int $targetCourseId = null,
        ?int $targetGroupId = null
    ): StudentWeeklyReport
    {
        return StudentWeeklyReport::create([
            'student_id' => $student->id,
            'created_by_admin_id' => $adminId,
            'target_course_id' => $targetCourseId,
            'target_group_id' => $targetGroupId,
            'report_title' => $title,
            'due_at' => $dueAt,
            'status' => StudentWeeklyReport::STATUS_DRAFT,
        ]);
    }

    public function saveStudentReport(StudentWeeklyReport $report, array $payload): StudentWeeklyReport
    {
        $this->assertStudentCanEdit($report);

        return DB::transaction(function () use ($report, $payload) {
            $report->update([
                'student_details' => $payload['student_details'] ?? null,
                'student_notes' => $payload['student_notes'] ?? null,
            ]);

            $lessonSelections = $payload['lessons'] ?? [];
            $report->selectedLessons()->delete();

            if (!empty($lessonSelections)) {
                foreach ($lessonSelections as $entry) {
                    $courseId = (int) ($entry['course_id'] ?? 0);
                    $moduleId = (int) ($entry['module_id'] ?? 0);

                    if (!$this->isModuleAllowedForStudentReport((int) $report->student_id, $report, $courseId, $moduleId)) {
                        throw ValidationException::withMessages([
                            'lessons' => 'تم اختيار درس غير متاح لهذا الطالب.',
                        ]);
                    }

                    $lessonId = $this->resolveLessonIdForModule($moduleId);

                    $report->selectedLessons()->create([
                        'course_id' => $courseId,
                        'lesson_id' => $lessonId,
                        'module_id' => $moduleId,
                    ]);
                }
            }

            return $report->fresh(['selectedLessons.lesson', 'selectedLessons.module', 'selectedLessons.course']);
        });
    }

    public function submitReport(StudentWeeklyReport $report, array $payload): StudentWeeklyReport
    {
        if ($report->wasSubmittedByStudent()) {
            throw ValidationException::withMessages([
                'report' => 'تم إرسال هذا التقرير مسبقاً ولا يمكن إرساله مجدداً.',
            ]);
        }

        if (!$report->isEditableByStudent()) {
            throw ValidationException::withMessages([
                'report' => 'لا يمكن إرسال هذا التقرير في حالته الحالية.',
            ]);
        }

        $report = $this->saveStudentReport($report, $payload);

        $report->update([
            'status' => StudentWeeklyReport::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        return $report->fresh();
    }

    public function addAdminFeedback(StudentWeeklyReport $report, string $feedback): StudentWeeklyReport
    {
        $report->update([
            'admin_feedback' => $feedback,
            'status' => StudentWeeklyReport::STATUS_REVIEWED,
            'reviewed_at' => now(),
        ]);

        return $report->fresh();
    }

    public function closeOverdueReports(): int
    {
        return StudentWeeklyReport::query()
            ->whereIn('status', [StudentWeeklyReport::STATUS_DRAFT, StudentWeeklyReport::STATUS_SUBMITTED])
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->update([
                'status' => StudentWeeklyReport::STATUS_CLOSED,
                'closed_at' => now(),
            ]);
    }

    private function assertStudentCanEdit(StudentWeeklyReport $report): void
    {
        if (!$report->isEditableByStudent()) {
            $message = $report->wasSubmittedByStudent()
                ? 'تم إرسال التقرير مسبقاً ولا يمكن تعديله أو إرساله مجدداً.'
                : 'هذا التقرير مغلق ولا يمكن تعديله.';

            throw ValidationException::withMessages([
                'report' => $message,
            ]);
        }
    }

    private function getFilteredSelectableModules(int $studentId, StudentWeeklyReport $report, int $courseId): Collection
    {
        if (!$this->isCourseAllowedForStudentReport($studentId, $report, $courseId)) {
            return collect();
        }

        $groupId = $this->resolveGroupIdForStudentReport($studentId, $report, $courseId);
        if (!$groupId) {
            return collect();
        }

        $modules = CourseModule::query()
            ->with([
                'section.accessRestrictions',
                'accessRestrictions',
            ])
            ->where('course_id', $courseId)
            ->where('is_visible', true)
            ->whereNull('deleted_at')
            ->whereHas('section', function ($query) {
                $query->where('is_visible', true)->whereNull('deleted_at');
            })
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return $modules
            ->filter(function (CourseModule $module) use ($groupId) {
                $section = $module->section;
                if (!$section || !$section->is_visible) {
                    return false;
                }

                if (!$this->isContentVisibleForGroup($section->accessRestrictions, $groupId)) {
                    return false;
                }

                return $this->isContentVisibleForGroup($module->accessRestrictions, $groupId);
            })
            ->values();
    }

    /**
     * @param  array<int, int>  $completedModuleIds
     * @return array{id: int, title: string, module_type: string, type_label: string, is_completed: bool}
     */
    private function formatSelectableModule(CourseModule $module, array $completedModuleIds): array
    {
        return [
            'id' => (int) $module->id,
            'title' => $module->title,
            'module_type' => $module->module_type,
            'type_label' => $this->moduleTypeLabel($module->module_type),
            'is_completed' => in_array((int) $module->id, $completedModuleIds, true),
        ];
    }

    /**
     * @return array<int, int>
     */
    private function resolveCompletedModuleIds(int $studentId, Collection $modules): array
    {
        if ($modules->isEmpty()) {
            return [];
        }

        return ModuleCompletion::query()
            ->where('student_id', $studentId)
            ->whereIn('module_id', $modules->pluck('id'))
            ->where('completion_status', 'completed')
            ->pluck('module_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function moduleTypeLabel(?string $moduleType): string
    {
        return match ($moduleType) {
            'video' => 'فيديو',
            'lesson' => 'درس',
            'assignment' => 'واجب',
            'quiz' => 'اختبار',
            'question_module' => 'اختبار',
            'resource' => 'مورد',
            'documentation' => 'توثيق',
            'simulator' => 'محاكاة',
            default => 'محتوى',
        };
    }

    private function isContentVisibleForGroup(?Collection $restrictions, int $groupId): bool
    {
        $restrictions = $restrictions ?? collect();

        if ($restrictions->isEmpty()) {
            return true;
        }

        foreach ($restrictions as $restriction) {
            if ($restriction->restriction_type !== 'group') {
                continue;
            }

            $matches = (int) $restriction->restriction_id === $groupId;

            if ($restriction->access_type === 'deny' && $matches) {
                return false;
            }

            if ($restriction->access_type === 'allow' && $matches) {
                return true;
            }
        }

        $hasGroupAllowRestrictions = $restrictions
            ->where('access_type', 'allow')
            ->where('restriction_type', 'group')
            ->isNotEmpty();

        if ($hasGroupAllowRestrictions) {
            return false;
        }

        return true;
    }

    private function resolveLessonIdForModule(int $moduleId): ?int
    {
        $module = CourseModule::query()->find($moduleId);

        if (!$module || $module->module_type !== 'lesson') {
            return null;
        }

        return $module->modulable_id ? (int) $module->modulable_id : null;
    }

    private function adminCreatedBatchKey(StudentWeeklyReport $report): string
    {
        return implode('::', [
            $report->report_title,
            (int) ($report->target_course_id ?? 0),
            (int) ($report->target_group_id ?? 0),
            (int) ($report->created_by_admin_id ?? 0),
            optional($report->due_at)->format('Y-m-d H:i:s') ?? '',
            $report->created_at->format('Y-m-d H:i'),
        ]);
    }

    private function formatAdminCreatedBatchSummary(Collection $reports): array
    {
        return array_merge($this->buildAdminCreatedBatchMeta($reports), [
            'student_reports' => collect(),
        ]);
    }

    private function formatAdminCreatedBatch(Collection $reports): array
    {
        $studentReports = $reports
            ->sortBy(fn (StudentWeeklyReport $report) => $report->student->name_ar ?? $report->student->name ?? '')
            ->values();

        return array_merge($this->buildAdminCreatedBatchMeta($reports), [
            'student_reports' => $studentReports,
        ]);
    }

    private function buildAdminCreatedBatchMeta(Collection $reports): array
    {
        $first = $reports->first();
        $submittedStatuses = [
            StudentWeeklyReport::STATUS_SUBMITTED,
            StudentWeeklyReport::STATUS_REVIEWED,
        ];

        return [
            'key' => $this->adminCreatedBatchKey($first),
            'report_title' => $first->report_title,
            'target_course' => $first->targetCourse,
            'target_group' => $first->targetGroup,
            'created_by_admin' => $first->createdByAdmin,
            'due_at' => $first->due_at,
            'created_at' => $reports->min('created_at'),
            'students_count' => $reports->count(),
            'submitted_count' => $reports->whereIn('status', $submittedStatuses)->count(),
            'pending_count' => $reports->filter(fn (StudentWeeklyReport $report) => $report->submitted_at === null
                && in_array($report->status, [StudentWeeklyReport::STATUS_DRAFT, StudentWeeklyReport::STATUS_CLOSED], true)
            )->count(),
        ];
    }
}

