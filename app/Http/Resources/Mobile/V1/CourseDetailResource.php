<?php

namespace App\Http\Resources\Mobile\V1;

use Illuminate\Http\Request;

/**
 * تفاصيل كورس مسجّل فيه الطالب مع الأقسام والدروس وحالة الإنجاز.
 * يتوقع أن تكون علاقة course.sections.modules محمّلة ومفلترة مسبقاً،
 * وأن تُمرَّر حالات الإنجاز (مفهرسة بـ module_id) عبر withCompletions().
 */
class CourseDetailResource extends EnrolledCourseResource
{
    /** @var \Illuminate\Support\Collection<int, \App\Models\ModuleCompletion> */
    private $completions;

    public function withCompletions($completions): static
    {
        $this->completions = $completions;

        return $this;
    }

    public function toArray(Request $request): array
    {
        $course = $this->course;
        $completions = $this->completions ?? collect();

        $sections = $course->sections->map(function ($section) use ($completions) {
            $modules = $section->modules->map(fn ($module) => [
                'id' => (int) $module->id,
                'title' => $module->title,
                'type' => $module->module_type,
                'estimated_duration' => $module->estimated_duration !== null ? (int) $module->estimated_duration : null,
                'is_required' => (bool) $module->is_required,
                'status' => $completions->get($module->id)?->completion_status ?? 'not_started',
            ])->values();

            $completed = $modules->where('status', 'completed')->count();

            return [
                'id' => (int) $section->id,
                'title' => $section->title,
                'description' => $section->description,
                'modules_count' => $modules->count(),
                'completed_modules_count' => $completed,
                'modules' => $modules,
            ];
        })->values();

        return array_merge(parent::toArray($request), [
            'description' => $course->description,
            'language' => $course->language,
            'sections_count' => $sections->count(),
            'modules_count' => $sections->sum('modules_count'),
            'completed_modules_count' => $sections->sum('completed_modules_count'),
            'sections' => $sections,
        ]);
    }
}
