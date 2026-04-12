<?php

namespace App\Services\Api;

use App\Events\CourseCompleted;
use App\Events\LessonCompleted;
use App\Events\N8nWebhookEvent;
use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\ModuleCompletion;
use App\Models\SectionCompletion;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * تسجيل إكمال وحدات الكورس لمسارات API الطالب فقط (نفس منطق CourseLearningController دون تعديل الويب).
 */
class StudentModuleProgressApiService
{
    /**
     * @return array{success: bool, message: string, module_id?: int, is_completed?: bool, completion_percentage?: float, http_status?: int}
     */
    public function markModuleComplete(User $student, int $moduleId): array
    {
        DB::beginTransaction();
        try {
            $module = CourseModule::with(['course', 'section'])->findOrFail($moduleId);

            $enrollment = CourseEnrollment::where('course_id', $module->course_id)
                ->where('student_id', $student->id)
                ->first();

            if (! $enrollment || ! $enrollment->isActive()) {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => 'أنت غير مسجل في هذا الكورس',
                    'http_status' => 403,
                ];
            }

            ModuleCompletion::updateOrCreate(
                [
                    'module_id' => $moduleId,
                    'student_id' => $student->id,
                ],
                [
                    'completion_status' => 'completed',
                    'completed_at' => now(),
                ]
            );

            $this->updateSectionCompletion($module->section_id, $student->id);
            $courseCompletion = $this->updateCourseCompletion($module->course_id, $student->id);

            LessonCompleted::dispatch($student, $module);

            event(new N8nWebhookEvent('lesson.completed', [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'student_email' => $student->email,
                'lesson_id' => $module->id,
                'lesson_title' => $module->title,
                'course_id' => $module->course_id,
                'course_title' => $module->course->title ?? null,
                'completion_percentage' => $courseCompletion,
                'completed_at' => now()->toIso8601String(),
            ]));

            if ($courseCompletion >= 100) {
                CourseCompleted::dispatch($student, $module->course);

                event(new N8nWebhookEvent('course.completed', [
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'student_email' => $student->email,
                    'course_id' => $module->course_id,
                    'course_title' => $module->course->title ?? null,
                    'completion_percentage' => 100,
                    'completed_at' => now()->toIso8601String(),
                ]));
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'تم تحديد الدرس كمكتمل',
                'module_id' => (int) $moduleId,
                'is_completed' => true,
                'completion_percentage' => (float) $courseCompletion,
                'http_status' => 200,
            ];
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'الوحدة غير موجودة',
                'http_status' => 404,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Student API] markModuleComplete failed', [
                'module_id' => $moduleId,
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'حدث خطأ: '.$e->getMessage(),
                'http_status' => 422,
            ];
        }
    }

    /**
     * @return array{success: bool, message: string, module_id?: int, is_completed?: bool, completion_percentage?: float, http_status?: int}
     */
    public function markModuleIncomplete(User $student, int $moduleId): array
    {
        DB::beginTransaction();
        try {
            $module = CourseModule::with(['course', 'section'])->findOrFail($moduleId);

            $enrollment = CourseEnrollment::where('course_id', $module->course_id)
                ->where('student_id', $student->id)
                ->first();

            if (! $enrollment || ! $enrollment->isActive()) {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => 'أنت غير مسجل في هذا الكورس',
                    'http_status' => 403,
                ];
            }

            ModuleCompletion::where('module_id', $moduleId)
                ->where('student_id', $student->id)
                ->update([
                    'completion_status' => 'in_progress',
                    'completed_at' => null,
                ]);

            $this->updateSectionCompletion($module->section_id, $student->id);
            $courseCompletion = $this->updateCourseCompletion($module->course_id, $student->id);

            DB::commit();

            return [
                'success' => true,
                'message' => 'تم إلغاء إكمال الدرس',
                'module_id' => (int) $moduleId,
                'is_completed' => false,
                'completion_percentage' => (float) $courseCompletion,
                'http_status' => 200,
            ];
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'الوحدة غير موجودة',
                'http_status' => 404,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[Student API] markModuleIncomplete failed', [
                'module_id' => $moduleId,
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'حدث خطأ: '.$e->getMessage(),
                'http_status' => 422,
            ];
        }
    }

    /**
     * نفس منطق CourseLearningController::updateSectionCompletion (الوحدات المطلوبة فقط).
     */
    private function updateSectionCompletion(int $sectionId, int $studentId): void
    {
        $section = \App\Models\CourseSection::with('modules')->find($sectionId);

        if (! $section) {
            return;
        }

        $totalModules = $section->modules()->where('is_required', true)->count();

        if ($totalModules === 0) {
            return;
        }

        $completedModules = ModuleCompletion::whereIn('module_id',
            $section->modules()->where('is_required', true)->pluck('course_modules.id')
        )
            ->where('student_id', $studentId)
            ->where('completion_status', 'completed')
            ->count();

        $percentage = ($completedModules / $totalModules) * 100;

        SectionCompletion::updateOrCreate(
            [
                'section_id' => $sectionId,
                'student_id' => $studentId,
            ],
            [
                'completion_percentage' => $percentage,
                'completed_at' => $percentage >= 100 ? now() : null,
            ]
        );
    }

    /**
     * نفس منطق CourseLearningController::updateCourseCompletion.
     */
    private function updateCourseCompletion(int $courseId, int $studentId): float
    {
        $enrollment = CourseEnrollment::where('course_id', $courseId)
            ->where('student_id', $studentId)
            ->first();

        if ($enrollment) {
            $enrollment->calculateCompletionPercentage();

            return (float) ($enrollment->completion_percentage ?? 0);
        }

        return 0.0;
    }
}
