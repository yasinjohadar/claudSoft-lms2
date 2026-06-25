<?php

namespace App\Http\Controllers\Student;

use App\Events\CourseCompleted;
use App\Events\LessonCompleted;
use App\Events\N8nWebhookEvent;
use App\Events\StudentActivityTracked;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\ModuleCompletion;
use App\Models\Resource;
use App\Models\SectionCompletion;
use App\Services\AccessControlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CourseLearningController extends Controller
{
    /**
     * Show learning page for a course (sidebar + content).
     */
    public function show($courseId)
    {
        try {
            $student = auth()->user();
            $accessControl = new AccessControlService;
            $course = Course::with([
                'sections' => function ($q) {
                    $q->visible()->orderBy('sort_order');
                },
                'sections.modules' => function ($q) {
                    $q->visible()->orderBy('sort_order');
                },
                'sections.modules.modulable',
            ])->findOrFail($courseId);

            // Check if student is enrolled
            $enrollment = CourseEnrollment::where('course_id', $courseId)
                ->where('student_id', $student->id)
                ->first();

            if (! $enrollment || ! $enrollment->isActive()) {
                return redirect()
                    ->route('student.courses.index')
                    ->with('error', 'أنت غير مسجل في هذا الكورس');
            }

            $courseAccess = $accessControl->canAccessCourse($course, $student);
            if (! ($courseAccess['can_access'] ?? false)) {
                return redirect()
                    ->route('student.courses.index')
                    ->with('error', $courseAccess['reason'] ?? 'هذا الكورس غير متاح حالياً');
            }

            $accessibleSections = collect();
            foreach ($course->sections as $section) {
                $sectionAccess = $accessControl->canAccessSection($section, $student);
                if (! ($sectionAccess['can_access'] ?? false)) {
                    continue;
                }

                $accessibleModules = $section->modules->filter(function ($module) use ($accessControl, $student) {
                    $moduleAccess = $accessControl->canAccessModule($module, $student);

                    return (bool) ($moduleAccess['can_access'] ?? false);
                })->values();

                $section->setRelation('modules', $accessibleModules);

                if ($accessibleModules->isNotEmpty()) {
                    $accessibleSections->push($section);
                }
            }

            $course->setRelation('sections', $accessibleSections->values());

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
                ->with('error', 'حدث خطأ أثناء تحميل الكورس: '.$e->getMessage());
        }
    }

    /**
     * Show specific module content.
     */
    public function showModule($moduleId)
    {
        $isLearnMainPartial = request()->header('Turbo-Frame') === 'student-learn-main'
            || request()->header('X-Learn-Partial') === 'main';

        try {
            $student = auth()->user();
            $accessControl = new AccessControlService;

            if ($isLearnMainPartial) {
                return $this->renderLearnModulePartial($moduleId, $student, $accessControl);
            }

            return $this->renderLearnModuleFull($moduleId, $student, $accessControl);

        } catch (\Exception $e) {
            if ($isLearnMainPartial) {
                return response('', 500);
            }

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحميل المحتوى: '.$e->getMessage());
        }
    }

    /**
     * Fast path: sidebar AJAX — loads only main content, not full course tree.
     */
    protected function renderLearnModulePartial($moduleId, $student, AccessControlService $accessControl)
    {
        $module = CourseModule::with([
            'section.course',
            'modulable',
            'completions' => fn ($q) => $q->where('student_id', $student->id),
        ])->findOrFail($moduleId);

        $moduleAccess = $accessControl->canAccessModule($module, $student);
        if (! $moduleAccess['can_access']) {
            return response('', 403);
        }

        $this->loadModuleModulableRelations($module);

        $enrollment = CourseEnrollment::where('course_id', $module->course_id)
            ->where('student_id', $student->id)
            ->first();

        defer(function () use ($enrollment) {
            $enrollment?->touchLastAccessed();
        });

        $completion = $module->completions->first();
        $isCompleted = $completion && $completion->completion_status === 'completed';

        $completedModules = $enrollment
            ? ModuleCompletion::where('student_id', $student->id)
                ->where('completion_status', 'completed')
                ->whereHas('module', fn ($q) => $q->where('course_id', $module->course_id))
                ->pluck('module_id')
                ->toArray()
            : [];

        return response()
            ->view('student.courses.learning.module-main', compact(
                'module',
                'enrollment',
                'isCompleted',
                'completedModules'
            ))
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Full page: includes sidebar curriculum with access-filtered sections.
     */
    protected function renderLearnModuleFull($moduleId, $student, AccessControlService $accessControl)
    {
        $module = CourseModule::with([
            'course',
            'course.sections' => function ($q) {
                $q->visible()->orderBy('sort_order');
            },
            'course.sections.course',
            'course.sections.modules' => function ($q) {
                $q->visible()->orderBy('sort_order');
            },
            'course.sections.modules.section',
            'course.sections.modules.accessRestrictions' => function ($q) {
                $q->where('restriction_type', 'group')
                    ->where('access_type', 'allow');
            },
            'course.sections.modules.accessRestrictions.group',
            'course.sections.accessRestrictions' => function ($q) {
                $q->where('restriction_type', 'group')
                    ->where('access_type', 'allow');
            },
            'course.sections.accessRestrictions.group',
            'section',
            'modulable',
            'completions' => function ($q) use ($student) {
                $q->where('student_id', $student->id);
            },
        ])->findOrFail($moduleId);

        $moduleAccess = $accessControl->canAccessModule($module, $student);
        if (! $moduleAccess['can_access']) {
            return redirect()
                ->route('student.courses.show', $module->course_id)
                ->with('error', $moduleAccess['reason'] ?? 'هذا الدرس غير متاح حالياً');
        }

        $this->loadModuleModulableRelations($module);

        $module->course->sections = $module->course->sections->filter(function ($section) use ($accessControl, $student) {
            return $accessControl->canAccessSection($section, $student)['can_access'];
        })->values();

        $module->course->sections->each(function ($section) use ($accessControl, $student) {
            $section->modules = $section->modules->filter(function ($mod) use ($accessControl, $student) {
                return $accessControl->canAccessModule($mod, $student)['can_access'];
            })->values();
        });

        $enrollment = CourseEnrollment::where('course_id', $module->course_id)
            ->where('student_id', $student->id)
            ->first();

        defer(function () use ($enrollment) {
            $enrollment?->touchLastAccessed();
        });

        $completion = $module->completions->first();
        $isCompleted = $completion && $completion->completion_status === 'completed';

        $module->loadMissing('course');
        defer(function () use ($student, $module) {
            StudentActivityTracked::dispatch($student, 'student.module.viewed', [
                'module_id' => $module->id,
                'module_title' => $module->title,
                'module_type' => $module->module_type,
                'course_id' => $module->course_id,
                'course_title' => $module->course->title ?? '',
            ]);
        });

        $completedModules = $enrollment
            ? ModuleCompletion::where('student_id', $student->id)
                ->where('completion_status', 'completed')
                ->whereHas('module', fn ($q) => $q->where('course_id', $module->course_id))
                ->pluck('module_id')
                ->toArray()
            : [];

        return view('student.courses.learning.module', compact(
            'module',
            'enrollment',
            'isCompleted',
            'completedModules'
        ));
    }

    protected function loadModuleModulableRelations(CourseModule $module): void
    {
        if ($module->module_type === 'question_module' && $module->modulable) {
            $module->modulable->load(['questions.questionType']);
        }

        if ($module->module_type === 'quiz' && $module->modulable) {
            $module->modulable->load(['quizQuestions.question.questionType', 'quizQuestions.question.options']);
        }

        if ($module->module_type === 'programming_challenge' && $module->modulable) {
            $module->modulable->load(['languages', 'files']);
        }
    }

    /**
     * Mark module as complete (زر "تم الإنجاز").
     */
    public function markAsComplete(Request $request, $moduleId)
    {
        DB::beginTransaction();
        try {
            $student = auth()->user();
            $module = CourseModule::with(['course', 'section'])->findOrFail($moduleId);

            // Check enrollment
            $enrollment = CourseEnrollment::where('course_id', $module->course_id)
                ->where('student_id', $student->id)
                ->first();

            if (! $enrollment || ! $enrollment->isActive()) {
                DB::rollBack();
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'أنت غير مسجل في هذا الكورس',
                    ], 403);
                }

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

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تحديد الدرس كمكتمل',
                    'module_id' => (int) $moduleId,
                    'is_completed' => true,
                    'completion_percentage' => $courseCompletion,
                ]);
            }

            return redirect()->back()->with('success', 'تم تحديد الدرس كمكتمل');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ: '.$e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', 'حدث خطأ: '.$e->getMessage());
        }
    }

    /**
     * Mark module as incomplete (إلغاء الإنجاز).
     */
    public function markAsIncomplete(Request $request, $moduleId)
    {
        DB::beginTransaction();
        try {
            $student = auth()->user();
            $module = CourseModule::with(['course', 'section'])->findOrFail($moduleId);

            // Check enrollment
            $enrollment = CourseEnrollment::where('course_id', $module->course_id)
                ->where('student_id', $student->id)
                ->first();

            if (! $enrollment || ! $enrollment->isActive()) {
                DB::rollBack();
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'أنت غير مسجل في هذا الكورس',
                    ], 403);
                }

                return redirect()->back()->with('error', 'أنت غير مسجل في هذا الكورس');
            }

            // Update module completion
            ModuleCompletion::where('module_id', $moduleId)
                ->where('student_id', $student->id)
                ->update([
                    'completion_status' => 'in_progress',
                    'completed_at' => null,
                ]);

            // Update section completion
            $this->updateSectionCompletion($module->section_id, $student->id);

            // Update course enrollment completion percentage
            $courseCompletion = $this->updateCourseCompletion($module->course_id, $student->id);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم إلغاء إكمال الدرس',
                    'module_id' => (int) $moduleId,
                    'is_completed' => false,
                    'completion_percentage' => $courseCompletion,
                ]);
            }

            return redirect()->back()->with('success', 'تم إلغاء إكمال الدرس');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ: '.$e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', 'حدث خطأ: '.$e->getMessage());
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
                    ],
                ]
            );

            // Auto-complete if watched >= 90%
            $percentage = ($validated['current_time'] / $validated['duration']) * 100;
            $module->loadMissing('course');
            StudentActivityTracked::dispatch($student, 'student.video.progress', [
                'module_id' => $module->id,
                'module_title' => $module->title,
                'course_id' => $module->course_id,
                'course_title' => $module->course->title ?? '',
                'progress_percentage' => round($percentage, 2),
            ]);
            if ($percentage >= 90 && $completion->completion_status !== 'completed') {
                $completion->update([
                    'completion_status' => 'completed',
                    'completed_at' => now(),
                ]);

                // Update section and course completion
                $this->updateSectionCompletion($module->section_id, $student->id);
                $this->updateCourseCompletion($module->course_id, $student->id);
            }

            app(\App\Services\Gamification\GamificationService::class)->dispatchVideoWatchIfEligible(
                $student,
                $module,
                $percentage,
                (int) $validated['current_time'],
                (int) $validated['duration']
            );

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث التقدم',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: '.$e->getMessage(),
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

            if (! $enrollment || ! $enrollment->isActive()) {
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

            if (! $resource->allow_download) {
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
                ->with('error', 'حدث خطأ أثناء التحميل: '.$e->getMessage());
        }
    }

    /**
     * Get module content based on type.
     */
    private function getModuleContent($module)
    {
        $modulable = $module->modulable;

        if (! $modulable) {
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
