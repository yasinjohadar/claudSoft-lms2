<?php

namespace App\Services\Reports;

use App\Models\CourseModule;
use App\Models\StudentWeeklyReport;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentWeeklyReportService
{
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

                    if (!$this->isModuleAllowedForStudent((int) $report->student_id, $courseId, $moduleId)) {
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
        if ($report->status === StudentWeeklyReport::STATUS_CLOSED || ($report->due_at && $report->due_at->isPast())) {
            throw ValidationException::withMessages([
                'report' => 'هذا التقرير مغلق ولا يمكن تعديله.',
            ]);
        }
    }

    private function isModuleAllowedForStudent(int $studentId, int $courseId, int $moduleId): bool
    {
        if ($courseId <= 0 || $moduleId <= 0) {
            return false;
        }

        $isEnrolled = DB::table('course_enrollments')
            ->where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->exists();

        if (!$isEnrolled) {
            return false;
        }

        return CourseModule::query()
            ->where('id', $moduleId)
            ->where('course_id', $courseId)
            ->exists();
    }

    private function resolveLessonIdForModule(int $moduleId): ?int
    {
        $module = CourseModule::query()->find($moduleId);

        if (!$module || $module->module_type !== 'lesson') {
            return null;
        }

        return $module->modulable_id ? (int) $module->modulable_id : null;
    }
}

