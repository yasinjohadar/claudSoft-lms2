<?php

namespace App\Services\Simulator;

use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\LessonSimulator;
use App\Models\User;

class SimulatorAccessService
{
    /**
     * @return array{allowed: bool, reason: ?string}
     */
    public function canAccess(User $user, LessonSimulator $simulator, ?CourseModule $module = null): array
    {
        if (! $simulator->isPublished()) {
            return ['allowed' => false, 'reason' => 'هذه المحاكاة غير منشورة بعد.'];
        }

        if ($module) {
            if ($module->modulable_type !== LessonSimulator::class || (int) $module->modulable_id !== (int) $simulator->id) {
                return ['allowed' => false, 'reason' => 'وحدة الكورس غير مرتبطة بهذه المحاكاة.'];
            }
            if (! $module->is_visible) {
                return ['allowed' => false, 'reason' => 'هذه الوحدة مخفية حالياً.'];
            }
            if (! $module->isAvailable()) {
                return ['allowed' => false, 'reason' => 'هذه الوحدة غير متاحة في الوقت الحالي.'];
            }
        }

        $courseIds = $this->linkedCourseIds($simulator);

        if ($courseIds->isEmpty()) {
            return ['allowed' => false, 'reason' => 'المحاكاة غير مرتبطة بأي كورس.'];
        }

        $enrolled = CourseEnrollment::query()
            ->where('student_id', $user->id)
            ->whereIn('course_id', $courseIds)
            ->get()
            ->contains(fn (CourseEnrollment $e) => $e->isActive());

        if (! $enrolled) {
            return ['allowed' => false, 'reason' => 'يجب أن تكون مسجّلاً في أحد الكورسات المرتبطة للوصول إلى هذه المحاكاة.'];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * @return \Illuminate\Support\Collection<int, int>
     */
    public function linkedCourseIds(LessonSimulator $simulator): \Illuminate\Support\Collection
    {
        $simulator->loadMissing(['courses', 'courseModules']);

        $fromPivot = $simulator->courses->pluck('id');
        $fromModules = $simulator->courseModules->pluck('course_id');

        return $fromPivot->merge($fromModules)->unique()->filter()->values();
    }
}
