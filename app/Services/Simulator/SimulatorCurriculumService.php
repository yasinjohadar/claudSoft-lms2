<?php

namespace App\Services\Simulator;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseSection;
use App\Models\LessonSimulator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SimulatorCurriculumService
{
    /**
     * @param  list<int>  $simulatorIds
     * @return Collection<int, CourseModule>
     */
    public function attachToSection(CourseSection $section, array $simulatorIds): Collection
    {
        $simulators = LessonSimulator::query()
            ->whereIn('id', $simulatorIds)
            ->where('status', 'published')
            ->get();

        if ($simulators->count() !== count($simulatorIds)) {
            throw ValidationException::withMessages([
                'lesson_simulator_ids' => 'يجب اختيار محاكيات منشورة فقط.',
            ]);
        }

        $created = collect();

        DB::transaction(function () use ($section, $simulators, &$created) {
            foreach ($simulators as $simulator) {
                $existing = CourseModule::query()
                    ->where('section_id', $section->id)
                    ->where('module_type', 'simulator')
                    ->where('modulable_type', LessonSimulator::class)
                    ->where('modulable_id', $simulator->id)
                    ->first();

                if ($existing) {
                    $created->push($existing);
                    $this->syncCoursePivot($section->course_id, $simulator);

                    continue;
                }

                $maxOrder = CourseModule::query()
                    ->where('section_id', $section->id)
                    ->max('sort_order') ?? 0;

                $module = CourseModule::create([
                    'course_id' => $section->course_id,
                    'section_id' => $section->id,
                    'module_type' => 'simulator',
                    'modulable_id' => $simulator->id,
                    'modulable_type' => LessonSimulator::class,
                    'title' => $simulator->title,
                    'description' => $simulator->description,
                    'sort_order' => $maxOrder + 1,
                    'is_visible' => true,
                    'is_required' => false,
                    'is_graded' => false,
                    'completion_type' => 'auto',
                ]);

                $this->syncCoursePivot($section->course_id, $simulator);
                $created->push($module);
            }
        });

        return $created->unique('id')->values();
    }

    public function syncCoursePivot(int $courseId, LessonSimulator $simulator): void
    {
        $simulator->courses()->syncWithoutDetaching([$courseId]);
    }

    public function detachModule(CourseModule $module): void
    {
        if ($module->module_type !== 'simulator') {
            throw ValidationException::withMessages([
                'module' => 'هذه الوحدة ليست محاكاة.',
            ]);
        }

        $module->delete();
    }
}
