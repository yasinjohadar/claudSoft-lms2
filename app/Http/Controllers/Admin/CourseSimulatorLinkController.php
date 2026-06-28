<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LessonSimulator;
use App\Services\Simulator\SimulatorCurriculumService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CourseSimulatorLinkController extends Controller
{
    public function __construct(
        private SimulatorCurriculumService $curriculumService,
    ) {}

    public function search(Course $course, Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        $simulators = LessonSimulator::query()
            ->where('status', 'published')
            ->when($term !== '', function ($query) use ($term) {
                $query->where(function ($inner) use ($term) {
                    $inner->where('title', 'like', "%{$term}%")
                        ->orWhere('slug', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                });
            })
            ->latest()
            ->limit(50)
            ->get(['id', 'title', 'slug', 'description'])
            ->map(fn (LessonSimulator $simulator) => [
                'id' => $simulator->id,
                'text' => $simulator->title,
                'slug' => $simulator->slug,
                'description' => $simulator->description,
            ]);

        return response()->json(['results' => $simulators]);
    }

    public function store(Request $request, Course $course): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:course_sections,id',
            'lesson_simulator_ids' => 'required|array|min:1',
            'lesson_simulator_ids.*' => 'integer|exists:lesson_simulators,id',
        ]);

        $section = $course->sections()->findOrFail($validated['section_id']);

        try {
            $modules = $this->curriculumService->attachToSection(
                $section,
                $validated['lesson_simulator_ids'],
            );

            $message = 'تم إضافة '.$modules->count().' محاكاة إلى القسم.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => route('courses.show', $course->id),
                ]);
            }

            return redirect()
                ->route('courses.show', $course->id)
                ->with('success', $message);
        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'تعذّر إضافة المحاكيات.',
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }
}
