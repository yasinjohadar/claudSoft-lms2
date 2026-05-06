<?php

namespace App\Services\Reports;

use App\Models\StudentWeeklyReport;
use App\Models\StudentWeeklyReportSchedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StudentWeeklyReportScheduleService
{
    public function __construct(private readonly StudentWeeklyReportService $reportService)
    {
    }

    public function runDueSchedules(): int
    {
        $schedules = StudentWeeklyReportSchedule::query()
            ->where('is_active', true)
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', now())
            ->get();

        $created = 0;

        foreach ($schedules as $schedule) {
            DB::transaction(function () use ($schedule, &$created) {
                $targetStudents = $this->resolveTargetStudents($schedule);
                $title = 'تقرير أسبوعي - ' . now()->format('Y-m-d');
                $dueAt = $schedule->next_run_at;

                foreach ($targetStudents as $student) {
                    $report = StudentWeeklyReport::firstOrCreate([
                        'student_id' => $student->id,
                        'created_by_admin_id' => $schedule->created_by_admin_id,
                        'target_course_id' => $schedule->target_course_id,
                        'target_group_id' => $schedule->target_group_id,
                        'report_title' => $title,
                        'due_at' => $dueAt,
                    ], [
                        'status' => StudentWeeklyReport::STATUS_DRAFT,
                    ]);

                    if ($report->wasRecentlyCreated) {
                        $created++;
                    }
                }

                $schedule->update([
                    'next_run_at' => $schedule->calculateNextRun(),
                ]);
            });
        }

        return $created;
    }

    private function resolveTargetStudents(StudentWeeklyReportSchedule $schedule)
    {
        if (!empty($schedule->target_course_id) && !empty($schedule->target_group_id)) {
            return $this->reportService->resolveStudentsByCourseAndGroup(
                (int) $schedule->target_course_id,
                (int) $schedule->target_group_id
            );
        }

        if ($schedule->target_scope === 'specific_students') {
            $ids = collect($schedule->target_student_ids ?? [])->map(fn ($id) => (int) $id)->filter()->values();
            return User::query()->whereIn('id', $ids)->get();
        }

        return User::query()->role('student')->get();
    }
}

