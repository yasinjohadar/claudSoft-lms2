<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseEnrollment;
use App\Models\ModuleCompletion;
use App\Models\SectionCompletion;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Events\LessonCompleted;
use App\Events\CourseCompleted;
use App\Events\N8nWebhookEvent;

class CourseLearningController extends Controller
{
    /**
     * Show learning page for a course (sidebar + content).
     */
    public function show($courseId)
    {
        try {
            $student = auth()->user();
            $course = Course::with([
                'sections' => function($q) {
                    $q->visible()->orderBy('sort_order');
                },
                'sections.modules' => function($q) {
                    $q->visible()->orderBy('sort_order');
                },
                'sections.modules.modulable'
            ])->findOrFail($courseId);

            // Check if student is enrolled
            $enrollment = CourseEnrollment::where('course_id', $courseId)
                ->where('student_id', $student->id)
                ->first();

            if (!$enrollment || !$enrollment->isActive()) {
                return redirect()
                    ->route('student.courses.index')
                    ->with('error', 'أنت غير مسجل في هذا الكورس');
            }

            // Update last accessed
            $enrollment->touchLastAccessed();

            // Get first module to display
            $currentModule = null;
            if ($course->sections->count() > 0) {
                $firstSection = $course->sections->first();
                if ($firstSection->modules->count() > 0) {
                    $currentModule = $firstSection->modules->first();
                }
            }

            // Get completion data for all modules
            $completedModules = ModuleCompletion::where('student_id', $student->id)
                ->whereIn('module_id', $course->modules()->pluck('course_modules.id'))
                ->where('completion_status', 'completed')
                ->pluck('module_id')
                ->toArray();

            return view('student.courses.learning.show', compact(
                'course',
                'enrollment',
                'currentModule',
                'completedModules'
            ));

        } catch (\Exception $e) {
            return redirect()
                ->route('student.courses.my-courses')
                ->with('error', 'حدث خطأ أثناء تحميل الكورس: ' . $e->getMessage());
        }
    }

    /**
     * Show specific module content.
     */
    public function showModule($moduleId)
    {
        try {
            $student = auth()->user();
            $module = CourseModule::with([
                'course',
                'course.sections' => function($q) {
                    $q->visible()->orderBy('sort_order');
                },
                'course.sections.modules' => function($q) {
                    $q->visible()->orderBy('sort_order');
                },
                'section',
                'modulable',
                'completions' => function($q) use ($student) {
                    $q->where('student_id', $student->id);
                }
            ])->findOrFail($moduleId);

            // Load questions for question modules
            if ($module->module_type === 'question_module' && $module->modulable) {
                $module->modulable->load(['questions.questionType']);
            }

            // Check enrollment or preview access
            $enrollment = CourseEnrollment::where('course_id', $module->course_id)
                ->where('student_id', $student->id)
                ->first();

            // Allow access if enrolled OR module is preview
            if (!$module->is_preview && (!$enrollment || !$enrollment->isActive())) {
                return redirect()
                    ->route('student.courses.show', $module->course_id)
                    ->with('error', 'أنت غير مسجل في هذا الكورس');
            }

            // Update last accessed if enrolled
            if ($enrollment) {
                $enrollment->touchLastAccessed();
            }

            // Check if module is completed
            $completion = $module->completions->first();
            $isCompleted = $completion && $completion->completion_status === 'completed';

            // Get all completed modules for the course
            $completedModules = [];
            if ($enrollment) {
                $completedModules = ModuleCompletion::where('student_id', $student->id)
                    ->whereIn('module_id', $module->course->modules()->pluck('course_modules.id'))
                    ->where('completion_status', 'completed')
                    ->pluck('module_id')
                    ->toArray();
            }

            return view('student.courses.learning.module', compact(
                'module',
                'enrollment',
                'isCompleted',
                'completedModules'
            ));

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحميل المحتوى: ' . $e->getMessage());
        }
    }

    /**
     * Mark module as complete (زر "تم الإنجاز").
     */
    public function markAsComplete($moduleId)
    {
        DB::beginTransaction();
        try {
            $student = auth()->user();
            $module = CourseModule::with(['course', 'section'])->findOrFail($moduleId);

            // Check enrollment
            $enrollment = CourseEnrollment::where('course_id', $module->course_id)
                ->where('student_id', $student->id)
                ->first();

            if (!$enrollment || !$enrollment->isActive()) {
                return redirect()->back()->with('error', 'أنت غير مسجل في هذا الكورس');
            }

            // Create or update module completion
            $moduleCompletion = ModuleCompletion::updateOrCreate(
                [
                    'module_id' => $moduleId,
                    'student_id' => $student->id,
                ],
                [
                    'completion_status' => 'completed',
                    'completed_at' => now(),
                ]
            );

            // Update section completion
            $this->updateSectionCompletion($module->section_id, $student->id);

            // Update course enrollment completion percentage
            $courseCompletion = $this->updateCourseCompletion($module->course_id, $student->id);

            // Dispatch LessonCompleted event for gamification
            LessonCompleted::dispatch(auth()->user(), $module);

            // Dispatch n8n webhook event for lesson completion
            event(new N8nWebhookEvent('lesson.completed', [
                'student_id' => auth()->id(),
                'student_name' => auth()->user()->name,
                'student_email' => auth()->user()->email,
                'lesson_id' => $module->id,
                'lesson_title' => $module->title,
                'course_id' => $module->course_id,
                'course_title' => $module->course->title ?? null,
                'completion_percentage' => $courseCompletion,
                'completed_at' => now()->toIso8601String(),
            ]));

            // Check if course is fully completed and dispatch event
            if ($courseCompletion >= 100) {
                CourseCompleted::dispatch(auth()->user(), $module->course);
                
                // Dispatch n8n webhook event for course completion
                event(new N8nWebhookEvent('course.completed', [
                    'student_id' => auth()->id(),
                    'student_name' => auth()->user()->name,
                    'student_email' => auth()->user()->email,
                    'course_id' => $module->course_id,
                    'course_title' => $module->course->title ?? null,
                    'completion_percentage' => 100,
                    'completed_at' => now()->toIso8601String(),
                ]));
            }

            DB::commit();

            return redirect()->back()->with('success', 'تم تحديد الدرس كمكتمل');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Mark module as incomplete (إلغاء الإنجاز).
     */
    public function markAsIncomplete($moduleId)
    {
        DB::beginTransaction();
        try {
            $student = auth()->user();
            $module = CourseModule::with(['course', 'section'])->findOrFail($moduleId);

            // Check enrollment
            $enrollment = CourseEnrollment::where('course_id', $module->course_id)
                ->where('student_id', $student->id)
                ->first();

            if (!$enrollment || !$enrollment->isActive()) {
                return redirect()->back()->with('error', 'أنت غير مسجل في هذا الكورس');
            }

            // Update module completion
            ModuleCompletion::where('module_id', $moduleId)
                ->where('student_id', $student->id)
                ->update([
                    'completion_status' => 'incomplete',
                    'completed_at' => null,
                ]);

            // Update section completion
            $this->updateSectionCompletion($module->section_id, $student->id);

            // Update course enrollment completion percentage
            $this->updateCourseCompletion($module->course_id, $student->id);

            DB::commit();

            return redirect()->back()->with('success', 'تم إلغاء إكمال الدرس');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Track video progress.
     */
    public function trackVideoProgress(Request $request, $moduleId)
    {
        $validated = $request->validate([
            'current_time' => 'required|numeric|min:0',
            'duration' => 'required|numeric|min:0',
        ]);

        try {
            $student = auth()->user();
            $module = CourseModule::findOrFail($moduleId);

            // Update or create module completion with progress
            $completion = ModuleCompletion::updateOrCreate(
                [
                    'module_id' => $moduleId,
                    'student_id' => $student->id,
                ],
                [
                    'progress' => [
                        'current_time' => $validated['current_time'],
                        'duration' => $validated['duration'],
                        'percentage' => ($validated['current_time'] / $validated['duration']) * 100,
                    ]
                ]
            );

            // Auto-complete if watched >= 90%
            $percentage = ($validated['current_time'] / $validated['duration']) * 100;
            if ($percentage >= 90 && $completion->completion_status !== 'completed') {
                $completion->update([
                    'completion_status' => 'completed',
                    'completed_at' => now(),
                ]);

                // Update section and course completion
                $this->updateSectionCompletion($module->section_id, $student->id);
                $this->updateCourseCompletion($module->course_id, $student->id);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث التقدم'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download resource file.
     */
    public function downloadResource($moduleId)
    {
        try {
            $student = auth()->user();
            $module = CourseModule::with('modulable')->findOrFail($moduleId);

            // Check enrollment
            $enrollment = CourseEnrollment::where('course_id', $module->course_id)
                ->where('student_id', $student->id)
                ->first();

            if (!$enrollment || !$enrollment->isActive()) {
                return redirect()
                    ->back()
                    ->with('error', 'أنت غير مسجل في هذا الكورس');
            }

            if ($module->module_type !== 'resource') {
                return redirect()
                    ->back()
                    ->with('error', 'هذا ليس ملف للتحميل');
            }

            $resource = $module->modulable;

            if (!$resource->allow_download) {
                return redirect()
                    ->back()
                    ->with('error', 'التحميل غير مسموح لهذا الملف');
            }

            // Increment download count
            $resource->incrementDownloadCount();

            // Return file for download
            return Storage::disk('public')->download($resource->file_path, $resource->file_name);

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء التحميل: ' . $e->getMessage());
        }
    }

    /**
     * Get module content based on type.
     */
    private function getModuleContent($module)
    {
        $modulable = $module->modulable;

        if (!$modulable) {
            return null;
        }

        switch ($module->module_type) {
            case 'lesson':
                return [
                    'content' => $modulable->content,
                    'objectives' => $modulable->objectives,
                    'attachments' => $modulable->attachments,
                    'reading_time' => $modulable->reading_time,
                ];

            case 'video':
                return [
                    'video_type' => $modulable->video_type,
                    'video_url' => $modulable->video_url,
                    'video_path' => $modulable->video_path,
                    'embed_url' => $modulable->getEmbedUrl(),
                    'duration' => $modulable->duration,
                    'thumbnail' => $modulable->thumbnail,
                    'allow_download' => $modulable->allow_download,
                    'allow_speed_control' => $modulable->allow_speed_control,
                    'subtitles' => $modulable->subtitles,
                ];

            case 'resource':
                return [
                    'file_name' => $modulable->file_name,
                    'file_size' => $modulable->file_size,
                    'formatted_size' => $modulable->getFormattedFileSize(),
                    'resource_type' => $modulable->resource_type,
                    'allow_download' => $modulable->allow_download,
                    'preview_available' => $modulable->preview_available,
                    'icon_class' => $modulable->getIconClass(),
                ];

            default:
                return null;
        }
    }

    /**
     * Update section completion percentage.
     */
    private function updateSectionCompletion($sectionId, $studentId)
    {
        $section = \App\Models\CourseSection::with('modules')->find($sectionId);

        if (!$section) {
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
     * Update course completion percentage.
     */
    private function updateCourseCompletion($courseId, $studentId)
    {
        $enrollment = CourseEnrollment::where('course_id', $courseId)
            ->where('student_id', $studentId)
            ->first();

        if ($enrollment) {
            $enrollment->calculateCompletionPercentage();
            return $enrollment->completion_percentage ?? 0;
        }

        return 0;
    }
}
