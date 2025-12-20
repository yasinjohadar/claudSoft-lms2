<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\CourseModule;
use App\Models\CourseEnrollment;
use App\Models\ModuleCompletion;
use App\Models\SectionCompletion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service for tracking student progress in courses
 *
 * Handles module, section, and course completion tracking
 * with automatic cascade updates
 */
class ProgressTrackingService
{
    /**
     * Mark a module as completed by a student
     * This is the main method called when student clicks "تم الإنجاز" button
     *
     * @param int $moduleId Module ID
     * @param int $studentId Student user ID
     * @param float|null $score Optional score for graded modules
     * @param int|null $timeSpent Time spent in seconds
     * @return array Result with success status and updated progress
     */
    public function markModuleAsComplete(int $moduleId, int $studentId, ?float $score = null, ?int $timeSpent = null): array
    {
        try {
            DB::beginTransaction();

            $module = CourseModule::with(['section', 'course'])->find($moduleId);
            $student = User::find($studentId);

            if (!$module) {
                return [
                    'success' => false,
                    'error' => 'Module not found',
                    'code' => 'MODULE_NOT_FOUND'
                ];
            }

            if (!$student) {
                return [
                    'success' => false,
                    'error' => 'Student not found',
                    'code' => 'STUDENT_NOT_FOUND'
                ];
            }

            // Check if student is enrolled in the course
            $enrollment = CourseEnrollment::where('course_id', $module->course_id)
                ->where('student_id', $studentId)
                ->first();

            if (!$enrollment) {
                return [
                    'success' => false,
                    'error' => 'Student is not enrolled in this course',
                    'code' => 'NOT_ENROLLED'
                ];
            }

            // Create or update module completion
            $moduleCompletion = ModuleCompletion::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'module_id' => $moduleId
                ],
                [
                    'completion_status' => 'completed',
                    'completed_at' => now(),
                    'score' => $score,
                    'time_spent' => $timeSpent ?? 0,
                    'completion_percentage' => 100
                ]
            );

            // Clear cache for this module completion
            $this->clearProgressCache($module->course_id, $studentId);

            // Update section completion
            $sectionProgress = $this->updateSectionCompletion($module->section_id, $studentId);

            // Update course completion
            $courseProgress = $this->updateCourseCompletion($module->course_id, $studentId);

            // Update last accessed timestamp
            $enrollment->update([
                'last_accessed' => now()
            ]);

            DB::commit();

            Log::info('Module marked as complete', [
                'module_id' => $moduleId,
                'student_id' => $studentId,
                'section_progress' => $sectionProgress['percentage'],
                'course_progress' => $courseProgress['percentage']
            ]);

            return [
                'success' => true,
                'module_completion' => $moduleCompletion,
                'section_progress' => $sectionProgress,
                'course_progress' => $courseProgress,
                'message' => 'Module marked as completed successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error marking module as complete', [
                'module_id' => $moduleId,
                'student_id' => $studentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'An error occurred while marking module as complete',
                'code' => 'COMPLETION_ERROR',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Mark a module as incomplete (undo completion)
     *
     * @param int $moduleId Module ID
     * @param int $studentId Student user ID
     * @return array Result with success status and updated progress
     */
    public function markModuleAsIncomplete(int $moduleId, int $studentId): array
    {
        try {
            DB::beginTransaction();

            $module = CourseModule::with(['section', 'course'])->find($moduleId);

            if (!$module) {
                return [
                    'success' => false,
                    'error' => 'Module not found',
                    'code' => 'MODULE_NOT_FOUND'
                ];
            }

            // Find and update module completion
            $moduleCompletion = ModuleCompletion::where('student_id', $studentId)
                ->where('module_id', $moduleId)
                ->first();

            if (!$moduleCompletion) {
                return [
                    'success' => false,
                    'error' => 'Module completion not found',
                    'code' => 'COMPLETION_NOT_FOUND'
                ];
            }

            $moduleCompletion->update([
                'completion_status' => 'in_progress',
                'completed_at' => null,
                'completion_percentage' => 0
            ]);

            // Clear cache
            $this->clearProgressCache($module->course_id, $studentId);

            // Update section completion
            $sectionProgress = $this->updateSectionCompletion($module->section_id, $studentId);

            // Update course completion
            $courseProgress = $this->updateCourseCompletion($module->course_id, $studentId);

            DB::commit();

            Log::info('Module marked as incomplete', [
                'module_id' => $moduleId,
                'student_id' => $studentId,
                'section_progress' => $sectionProgress['percentage'],
                'course_progress' => $courseProgress['percentage']
            ]);

            return [
                'success' => true,
                'module_completion' => $moduleCompletion,
                'section_progress' => $sectionProgress,
                'course_progress' => $courseProgress,
                'message' => 'Module marked as incomplete successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error marking module as incomplete', [
                'module_id' => $moduleId,
                'student_id' => $studentId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'An error occurred while marking module as incomplete',
                'code' => 'INCOMPLETION_ERROR',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Update section completion percentage based on module completions
     *
     * @param int $sectionId Section ID
     * @param int $studentId Student user ID
     * @return array Section progress details
     */
    public function updateSectionCompletion(int $sectionId, int $studentId): array
    {
        try {
            $section = CourseSection::with('modules')->find($sectionId);

            if (!$section) {
                return [
                    'success' => false,
                    'error' => 'Section not found'
                ];
            }

            $totalModules = $section->modules()->count();

            if ($totalModules === 0) {
                return [
                    'success' => true,
                    'percentage' => 0,
                    'completed' => 0,
                    'total' => 0
                ];
            }

            // Get completed modules count
            $completedModules = ModuleCompletion::whereIn('module_id', $section->modules()->pluck('id'))
                ->where('student_id', $studentId)
                ->where('completion_status', 'completed')
                ->count();

            $percentage = round(($completedModules / $totalModules) * 100, 2);

            // Update or create section completion
            $sectionCompletion = SectionCompletion::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'section_id' => $sectionId
                ],
                [
                    'completion_percentage' => $percentage,
                    'modules_completed' => $completedModules,
                    'total_modules' => $totalModules,
                    'last_activity' => now()
                ]
            );

            return [
                'success' => true,
                'percentage' => $percentage,
                'completed' => $completedModules,
                'total' => $totalModules,
                'section_completion' => $sectionCompletion
            ];

        } catch (Exception $e) {
            Log::error('Error updating section completion', [
                'section_id' => $sectionId,
                'student_id' => $studentId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update course completion percentage based on module completions
     *
     * @param int $courseId Course ID
     * @param int $studentId Student user ID
     * @return array Course progress details
     */
    public function updateCourseCompletion(int $courseId, int $studentId): array
    {
        try {
            $course = Course::with('modules')->find($courseId);

            if (!$course) {
                return [
                    'success' => false,
                    'error' => 'Course not found'
                ];
            }

            $totalModules = $course->modules()->count();

            if ($totalModules === 0) {
                return [
                    'success' => true,
                    'percentage' => 0,
                    'completed' => 0,
                    'total' => 0
                ];
            }

            // Get completed modules count
            $completedModules = ModuleCompletion::whereIn('module_id', $course->modules()->pluck('id'))
                ->where('student_id', $studentId)
                ->where('completion_status', 'completed')
                ->count();

            $percentage = round(($completedModules / $totalModules) * 100, 2);

            // Update enrollment
            $enrollment = CourseEnrollment::where('course_id', $courseId)
                ->where('student_id', $studentId)
                ->first();

            if ($enrollment) {
                $enrollment->update([
                    'completion_percentage' => $percentage,
                    'last_accessed' => now()
                ]);

                // If 100% complete, mark as completed
                if ($percentage >= 100 && $enrollment->enrollment_status !== 'completed') {
                    $enrollment->update([
                        'enrollment_status' => 'completed',
                        'completed_at' => now()
                    ]);
                }
            }

            return [
                'success' => true,
                'percentage' => $percentage,
                'completed' => $completedModules,
                'total' => $totalModules,
                'enrollment' => $enrollment
            ];

        } catch (Exception $e) {
            Log::error('Error updating course completion', [
                'course_id' => $courseId,
                'student_id' => $studentId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Calculate module completion percentage for a student
     *
     * @param int $moduleId Module ID
     * @param int $studentId Student user ID
     * @return float Completion percentage (0-100)
     */
    public function calculateModuleCompletionPercentage(int $moduleId, int $studentId): float
    {
        $completion = ModuleCompletion::where('module_id', $moduleId)
            ->where('student_id', $studentId)
            ->first();

        return $completion ? (float) $completion->completion_percentage : 0;
    }

    /**
     * Calculate section completion percentage for a student
     *
     * @param int $sectionId Section ID
     * @param int $studentId Student user ID
     * @return float Completion percentage (0-100)
     */
    public function calculateSectionCompletionPercentage(int $sectionId, int $studentId): float
    {
        $section = CourseSection::with('modules')->find($sectionId);

        if (!$section || $section->modules->isEmpty()) {
            return 0;
        }

        $totalModules = $section->modules->count();
        $completedModules = ModuleCompletion::whereIn('module_id', $section->modules->pluck('id'))
            ->where('student_id', $studentId)
            ->where('completion_status', 'completed')
            ->count();

        return round(($completedModules / $totalModules) * 100, 2);
    }

    /**
     * Calculate course completion percentage for a student
     *
     * @param int $courseId Course ID
     * @param int $studentId Student user ID
     * @return float Completion percentage (0-100)
     */
    public function calculateCourseCompletionPercentage(int $courseId, int $studentId): float
    {
        $enrollment = CourseEnrollment::where('course_id', $courseId)
            ->where('student_id', $studentId)
            ->first();

        return $enrollment ? (float) $enrollment->completion_percentage : 0;
    }

    /**
     * Get comprehensive student progress for a course
     *
     * @param int $courseId Course ID
     * @param int $studentId Student user ID
     * @return array Detailed progress information
     */
    public function getStudentProgress(int $courseId, int $studentId): array
    {
        $cacheKey = "student_progress_{$courseId}_{$studentId}";

        return Cache::remember($cacheKey, 300, function () use ($courseId, $studentId) {
            $course = Course::with(['sections.modules'])->find($courseId);
            $enrollment = CourseEnrollment::where('course_id', $courseId)
                ->where('student_id', $studentId)
                ->first();

            if (!$course || !$enrollment) {
                return [
                    'success' => false,
                    'error' => 'Course or enrollment not found'
                ];
            }

            $progress = [
                'course_id' => $courseId,
                'student_id' => $studentId,
                'overall_percentage' => $enrollment->completion_percentage,
                'enrollment_date' => $enrollment->enrollment_date,
                'last_accessed' => $enrollment->last_accessed,
                'status' => $enrollment->enrollment_status,
                'sections' => []
            ];

            foreach ($course->sections as $section) {
                $sectionProgress = $this->calculateSectionCompletionPercentage($section->id, $studentId);

                $modules = [];
                foreach ($section->modules as $module) {
                    $moduleCompletion = ModuleCompletion::where('module_id', $module->id)
                        ->where('student_id', $studentId)
                        ->first();

                    $modules[] = [
                        'module_id' => $module->id,
                        'title' => $module->title,
                        'type' => $module->module_type,
                        'is_completed' => $moduleCompletion && $moduleCompletion->completion_status === 'completed',
                        'completion_percentage' => $moduleCompletion ? $moduleCompletion->completion_percentage : 0,
                        'score' => $moduleCompletion ? $moduleCompletion->score : null,
                        'time_spent' => $moduleCompletion ? $moduleCompletion->time_spent : 0,
                        'completed_at' => $moduleCompletion ? $moduleCompletion->completed_at : null
                    ];
                }

                $progress['sections'][] = [
                    'section_id' => $section->id,
                    'title' => $section->title,
                    'completion_percentage' => $sectionProgress,
                    'modules' => $modules
                ];
            }

            return [
                'success' => true,
                'progress' => $progress
            ];
        });
    }

    /**
     * Track video watching progress
     *
     * @param int $moduleId Module ID
     * @param int $studentId Student user ID
     * @param int $watchedSeconds Seconds watched
     * @param int $totalSeconds Total video duration
     * @return array Result with success status
     */
    public function trackVideoProgress(int $moduleId, int $studentId, int $watchedSeconds, int $totalSeconds): array
    {
        try {
            if ($totalSeconds <= 0) {
                return [
                    'success' => false,
                    'error' => 'Invalid total seconds'
                ];
            }

            $percentage = min(round(($watchedSeconds / $totalSeconds) * 100, 2), 100);

            // Update or create module completion
            $moduleCompletion = ModuleCompletion::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'module_id' => $moduleId
                ],
                [
                    'completion_percentage' => $percentage,
                    'time_spent' => $watchedSeconds,
                    'completion_status' => $percentage >= 90 ? 'completed' : 'in_progress',
                    'completed_at' => $percentage >= 90 ? now() : null
                ]
            );

            // If video is 90% watched, auto-complete
            if ($percentage >= 90) {
                $module = CourseModule::find($moduleId);
                if ($module) {
                    $this->updateSectionCompletion($module->section_id, $studentId);
                    $this->updateCourseCompletion($module->course_id, $studentId);
                }
            }

            return [
                'success' => true,
                'percentage' => $percentage,
                'is_completed' => $percentage >= 90,
                'module_completion' => $moduleCompletion
            ];

        } catch (Exception $e) {
            Log::error('Error tracking video progress', [
                'module_id' => $moduleId,
                'student_id' => $studentId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Clear progress cache for a student in a course
     *
     * @param int $courseId Course ID
     * @param int $studentId Student user ID
     * @return void
     */
    protected function clearProgressCache(int $courseId, int $studentId): void
    {
        $cacheKey = "student_progress_{$courseId}_{$studentId}";
        Cache::forget($cacheKey);
    }
}
