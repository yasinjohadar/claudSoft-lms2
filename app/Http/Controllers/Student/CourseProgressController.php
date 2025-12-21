<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\ModuleCompletion;
use App\Models\SectionCompletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class CourseProgressController extends Controller
{
    /**
     * Display progress for a specific course.
     */
    public function show($courseId)
    {
        try {
            $student = auth()->user();
            $course = Course::with(['sections.modules'])->findOrFail($courseId);

            // Get enrollment
            $enrollment = CourseEnrollment::where('course_id', $course->id)
                ->where('student_id', $student->id)
                ->first();

            if (!$enrollment) {
                return redirect()
                    ->route('student.courses.index')
                    ->with('error', 'أنت غير مسجل في هذا الكورس');
            }

            // Calculate progress
            $enrollment->calculateCompletionPercentage();
            $enrollment->refresh();

            // Get section progress
            $sectionsProgress = [];
            foreach ($course->sections as $section) {
                // Try required modules first, then fall back to all modules
                $requiredModules = $section->modules()->where('is_required', true);
                $totalModules = $requiredModules->count();
                
                // If no required modules, count all modules
                if ($totalModules === 0) {
                    $modulesToCheck = $section->modules()->get();
                    $totalModules = $modulesToCheck->count();
                } else {
                    $modulesToCheck = $requiredModules->get();
                }
                
                $completedModules = 0;
                foreach ($modulesToCheck as $module) {
                    if ($module->isCompletedBy($student)) {
                        $completedModules++;
                    }
                }

                $sectionsProgress[] = [
                    'section' => $section,
                    'total_modules' => $totalModules,
                    'completed_modules' => $completedModules,
                    'percentage' => $totalModules > 0 ? ($completedModules / $totalModules * 100) : 0,
                ];
            }

            // Get recent completions
            $recentCompletions = ModuleCompletion::where('student_id', $student->id)
                ->whereHas('module', function($q) use ($courseId) {
                    $q->where('course_id', $courseId);
                })
                ->with('module')
                ->orderBy('completed_at', 'desc')
                ->limit(10)
                ->get();

            // Get statistics - use required modules, fallback to all modules
            $requiredModulesQuery = $course->modules()->where('is_required', true);
            $totalModulesCount = $requiredModulesQuery->count();
            
            if ($totalModulesCount === 0) {
                // Fallback to all modules if no required modules
                $moduleIds = $course->modules()->pluck('course_modules.id');
                $totalModulesCount = $moduleIds->count();
            } else {
                $moduleIds = $requiredModulesQuery->pluck('course_modules.id');
            }
            
            $stats = [
                'total_modules' => $totalModulesCount,
                'completed_modules' => ModuleCompletion::where('student_id', $student->id)
                    ->whereIn('module_id', $moduleIds)
                    ->where('completion_status', 'completed')
                    ->count(),
                'completion_percentage' => $enrollment->completion_percentage,
                'time_spent' => $this->calculateTimeSpent($student, $course),
                'average_score' => $this->calculateAverageScore($student, $course),
                'can_get_certificate' => $enrollment->hasPassed() && $enrollment->isCompleted(),
            ];

            return view('student.progress.show', compact('course', 'enrollment', 'sectionsProgress', 'recentCompletions', 'stats'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحميل التقرير: ' . $e->getMessage());
        }
    }

    /**
     * Display overall progress for all courses.
     */
    public function overview()
    {
        try {
            $student = auth()->user();

            // Get all enrollments
            $enrollments = $student->courseEnrollments()
                ->with('course')
                ->get();

            // Calculate statistics for each course
            $coursesProgress = [];
            foreach ($enrollments as $enrollment) {
                try {
                    if ($enrollment->course) {
                        $enrollment->calculateCompletionPercentage();
                    }
                } catch (\Exception $e) {
                    \Log::error('Error calculating completion for enrollment ' . $enrollment->id . ': ' . $e->getMessage());
                    // Skip this enrollment and continue
                    continue;
                }

                $coursesProgress[] = [
                    'enrollment' => $enrollment,
                    'course' => $enrollment->course,
                    'completion_percentage' => $enrollment->completion_percentage ?? 0,
                    'status' => $enrollment->enrollment_status,
                    'last_accessed' => $enrollment->last_accessed_at,
                ];
            }

            // Overall statistics
            $stats = [
                'total_courses' => $enrollments->count(),
                'active_courses' => $enrollments->where('enrollment_status', 'active')->count(),
                'completed_courses' => $enrollments->where('enrollment_status', 'completed')->count(),
                'average_progress' => $enrollments->avg('completion_percentage') ?? 0,
                'total_certificates' => $enrollments->where('certificate_issued', true)->count(),
            ];

            return view('student.progress.overview', compact('coursesProgress', 'stats'));
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء تحميل نظرة عامة: ' . $e->getMessage());
        }
    }

    /**
     * Generate and download certificate.
     */
    public function certificate($courseId)
    {
        try {
            $student = auth()->user();
            $course = Course::findOrFail($courseId);

            // Get enrollment
            $enrollment = CourseEnrollment::where('course_id', $course->id)
                ->where('student_id', $student->id)
                ->first();

            if (!$enrollment) {
                return redirect()
                    ->back()
                    ->with('error', 'أنت غير مسجل في هذا الكورس');
            }

            // Check if student has completed the course
            if (!$enrollment->isCompleted()) {
                return redirect()
                    ->back()
                    ->with('error', 'يجب إكمال الكورس أولاً للحصول على الشهادة');
            }

            // Check if student has passed
            if (!$enrollment->hasPassed()) {
                return redirect()
                    ->back()
                    ->with('error', 'لم تحقق النسبة المطلوبة للنجاح');
            }

            // Issue certificate if not issued
            if (!$enrollment->certificate_issued) {
                $enrollment->issueCertificate();
            }

            // Generate PDF certificate
            $data = [
                'student' => $student,
                'course' => $course,
                'enrollment' => $enrollment,
                'issued_date' => now(),
                'certificate_number' => 'CERT-' . $course->id . '-' . $student->id . '-' . time(),
            ];

            $pdf = Pdf::loadView('certificates.course-certificate', $data);

            return $pdf->download("certificate-{$course->slug}-{$student->id}.pdf");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء إنشاء الشهادة: ' . $e->getMessage());
        }
    }

    /**
     * View certificate online.
     */
    public function viewCertificate($courseId)
    {
        try {
            $student = auth()->user();
            $course = Course::findOrFail($courseId);

            // Get enrollment
            $enrollment = CourseEnrollment::where('course_id', $course->id)
                ->where('student_id', $student->id)
                ->first();

            if (!$enrollment) {
                return redirect()
                    ->back()
                    ->with('error', 'أنت غير مسجل في هذا الكورس');
            }

            // Check if student has completed the course
            if (!$enrollment->isCompleted() || !$enrollment->hasPassed()) {
                return redirect()
                    ->back()
                    ->with('error', 'لا يمكن عرض الشهادة');
            }

            // Issue certificate if not issued
            if (!$enrollment->certificate_issued) {
                $enrollment->issueCertificate();
            }

            $data = [
                'student' => $student,
                'course' => $course,
                'enrollment' => $enrollment,
                'issued_date' => now(),
                'certificate_number' => 'CERT-' . $course->id . '-' . $student->id . '-' . time(),
            ];

            return view('certificates.course-certificate', $data);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء عرض الشهادة: ' . $e->getMessage());
        }
    }

    /**
     * Get progress statistics for a course (AJAX).
     */
    public function getStats($courseId)
    {
        try {
            $student = auth()->user();
            $course = Course::findOrFail($courseId);

            $enrollment = CourseEnrollment::where('course_id', $course->id)
                ->where('student_id', $student->id)
                ->first();

            if (!$enrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مسجل في الكورس'
                ], 404);
            }

            // Calculate fresh stats
            $enrollment->calculateCompletionPercentage();

            // Use required modules, fallback to all modules
            $requiredModulesQuery = $course->modules()->where('is_required', true);
            $totalModulesCount = $requiredModulesQuery->count();
            
            if ($totalModulesCount === 0) {
                $moduleIds = $course->modules()->pluck('course_modules.id');
                $totalModulesCount = $moduleIds->count();
            } else {
                $moduleIds = $requiredModulesQuery->pluck('course_modules.id');
            }

            $stats = [
                'completion_percentage' => $enrollment->completion_percentage,
                'total_modules' => $totalModulesCount,
                'completed_modules' => ModuleCompletion::where('student_id', $student->id)
                    ->whereIn('module_id', $moduleIds)
                    ->where('completion_status', 'completed')
                    ->count(),
                'average_score' => $this->calculateAverageScore($student, $course),
                'time_spent' => $this->calculateTimeSpent($student, $course),
                'can_get_certificate' => $enrollment->hasPassed() && $enrollment->isCompleted(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export progress report as PDF.
     */
    public function exportReport($courseId)
    {
        try {
            $student = auth()->user();
            $course = Course::with(['sections.modules'])->findOrFail($courseId);

            $enrollment = CourseEnrollment::where('course_id', $course->id)
                ->where('student_id', $student->id)
                ->first();

            if (!$enrollment) {
                return redirect()
                    ->back()
                    ->with('error', 'أنت غير مسجل في هذا الكورس');
            }

            // Get detailed progress
            $enrollment->calculateCompletionPercentage();

            $sectionsProgress = [];
            foreach ($course->sections as $section) {
                $modulesProgress = [];
                foreach ($section->modules as $module) {
                    $completion = $module->getCompletionFor($student);
                    $modulesProgress[] = [
                        'module' => $module,
                        'completion' => $completion,
                        'is_completed' => $module->isCompletedBy($student),
                    ];
                }

                $sectionsProgress[] = [
                    'section' => $section,
                    'modules' => $modulesProgress,
                ];
            }

            $data = [
                'student' => $student,
                'course' => $course,
                'enrollment' => $enrollment,
                'sectionsProgress' => $sectionsProgress,
                'generated_at' => now(),
            ];

            $pdf = Pdf::loadView('reports.course-progress', $data);

            return $pdf->download("progress-report-{$course->slug}-{$student->id}.pdf");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء إنشاء التقرير: ' . $e->getMessage());
        }
    }

    /**
     * Calculate time spent on a course.
     *
     * @param  \App\Models\User  $student
     * @param  \App\Models\Course  $course
     * @return int Time in minutes
     */
    private function calculateTimeSpent($student, $course)
    {
        // TODO: Implement actual time tracking
        // This is a placeholder calculation based on module completions
        $completedModules = ModuleCompletion::where('student_id', $student->id)
            ->whereIn('module_id', $course->modules()->pluck('course_modules.id'))
            ->where('completion_status', 'completed')
            ->count();

        // Estimate average 15 minutes per module
        return $completedModules * 15;
    }

    /**
     * Calculate average score across graded modules.
     *
     * @param  \App\Models\User  $student
     * @param  \App\Models\Course  $course
     * @return float
     */
    private function calculateAverageScore($student, $course)
    {
        $gradedCompletions = ModuleCompletion::where('student_id', $student->id)
            ->whereIn('module_id', $course->modules()->where('is_graded', true)->pluck('course_modules.id'))
            ->whereNotNull('score')
            ->get();

        if ($gradedCompletions->isEmpty()) {
            return 0;
        }

        return $gradedCompletions->avg('score') ?? 0;
    }
}
