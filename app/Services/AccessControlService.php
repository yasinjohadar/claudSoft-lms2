<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\CourseModule;
use App\Models\CourseAccessRestriction;
use App\Models\SectionAccessRestriction;
use App\Models\ModuleAccessRestriction;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Service for controlling access to courses, sections, and modules
 *
 * Handles visibility, availability, and restriction-based access control
 */
class AccessControlService
{
    /**
     * Check if a student can access a course
     *
     * @param Course $course Course model
     * @param User $student Student user model
     * @return array Result with can_access status and reason if false
     */
    public function canAccessCourse(Course $course, User $student): array
    {
        // Check if course is published
        if (!$course->is_published) {
            return [
                'can_access' => false,
                'reason' => 'الكورس غير منشور',
                'reason_en' => 'Course is not published',
                'code' => 'NOT_PUBLISHED'
            ];
        }

        // Check if course is visible
        if (!$course->is_visible) {
            return [
                'can_access' => false,
                'reason' => 'الكورس مخفي',
                'reason_en' => 'Course is hidden',
                'code' => 'NOT_VISIBLE'
            ];
        }

        // Check course availability dates
        if (!$course->isAvailable()) {
            $now = now();
            if ($course->available_from && $course->available_from > $now) {
                return [
                    'can_access' => false,
                    'reason' => 'الكورس سيكون متاحاً في ' . $course->available_from->format('Y-m-d'),
                    'reason_en' => 'Course will be available from ' . $course->available_from->format('Y-m-d'),
                    'code' => 'NOT_YET_AVAILABLE',
                    'available_from' => $course->available_from
                ];
            }

            if ($course->available_until && $course->available_until < $now) {
                return [
                    'can_access' => false,
                    'reason' => 'انتهت صلاحية الكورس في ' . $course->available_until->format('Y-m-d'),
                    'reason_en' => 'Course expired on ' . $course->available_until->format('Y-m-d'),
                    'code' => 'EXPIRED',
                    'expired_at' => $course->available_until
                ];
            }
        }

        // Check if student is enrolled
        $enrollment = CourseEnrollment::where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->whereIn('enrollment_status', ['active', 'completed'])
            ->first();

        if (!$enrollment) {
            return [
                'can_access' => false,
                'reason' => 'أنت غير مسجل في هذا الكورس',
                'reason_en' => 'You are not enrolled in this course',
                'code' => 'NOT_ENROLLED'
            ];
        }

        // Check access restrictions
        $restrictionCheck = $this->checkRestrictions(
            $course->id,
            'course',
            $student
        );

        if (!$restrictionCheck['has_access']) {
            return [
                'can_access' => false,
                'reason' => $restrictionCheck['reason'],
                'reason_en' => $restrictionCheck['reason_en'],
                'code' => 'RESTRICTED'
            ];
        }

        return [
            'can_access' => true,
            'enrollment' => $enrollment
        ];
    }

    /**
     * Check if a student can access a section
     *
     * @param CourseSection $section Section model
     * @param User $student Student user model
     * @return array Result with can_access status and reason if false
     */
    public function canAccessSection(CourseSection $section, User $student): array
    {
        // First check course access
        $courseAccess = $this->canAccessCourse($section->course, $student);
        if (!$courseAccess['can_access']) {
            return $courseAccess;
        }

        // Check if section is visible
        if (!$section->is_visible) {
            return [
                'can_access' => false,
                'reason' => 'القسم مخفي',
                'reason_en' => 'Section is hidden',
                'code' => 'NOT_VISIBLE'
            ];
        }

        // Check section availability dates
        if (!$section->isAvailable()) {
            $now = now();
            if ($section->available_from && $section->available_from > $now) {
                return [
                    'can_access' => false,
                    'reason' => 'القسم سيكون متاحاً في ' . $section->available_from->format('Y-m-d H:i'),
                    'reason_en' => 'Section will be available from ' . $section->available_from->format('Y-m-d H:i'),
                    'code' => 'NOT_YET_AVAILABLE',
                    'available_from' => $section->available_from
                ];
            }

            if ($section->available_until && $section->available_until < $now) {
                return [
                    'can_access' => false,
                    'reason' => 'انتهت صلاحية القسم في ' . $section->available_until->format('Y-m-d H:i'),
                    'reason_en' => 'Section expired on ' . $section->available_until->format('Y-m-d H:i'),
                    'code' => 'EXPIRED',
                    'expired_at' => $section->available_until
                ];
            }
        }

        // Check if section is locked
        if (!$this->isSectionUnlocked($section, $student)) {
            return [
                'can_access' => false,
                'reason' => 'القسم مقفل. يجب إتمام المتطلبات السابقة',
                'reason_en' => 'Section is locked. Complete previous requirements first',
                'code' => 'LOCKED',
                'unlock_conditions' => $section->unlock_conditions
            ];
        }

        // Check section access restrictions
        $restrictionCheck = $this->checkRestrictions(
            $section->id,
            'section',
            $student
        );

        if (!$restrictionCheck['has_access']) {
            return [
                'can_access' => false,
                'reason' => $restrictionCheck['reason'],
                'reason_en' => $restrictionCheck['reason_en'],
                'code' => 'RESTRICTED'
            ];
        }

        return [
            'can_access' => true
        ];
    }

    /**
     * Check if a student can access a module
     *
     * @param CourseModule $module Module model
     * @param User $student Student user model
     * @return array Result with can_access status and reason if false
     */
    public function canAccessModule(CourseModule $module, User $student): array
    {
        // First check section access
        $sectionAccess = $this->canAccessSection($module->section, $student);
        if (!$sectionAccess['can_access']) {
            return $sectionAccess;
        }

        // Check if module is visible
        if (!$module->is_visible) {
            return [
                'can_access' => false,
                'reason' => 'الوحدة مخفية',
                'reason_en' => 'Module is hidden',
                'code' => 'NOT_VISIBLE'
            ];
        }

        // Check module availability dates
        if (!$module->isAvailable()) {
            $now = now();
            if ($module->available_from && $module->available_from > $now) {
                return [
                    'can_access' => false,
                    'reason' => 'الوحدة ستكون متاحة في ' . $module->available_from->format('Y-m-d H:i'),
                    'reason_en' => 'Module will be available from ' . $module->available_from->format('Y-m-d H:i'),
                    'code' => 'NOT_YET_AVAILABLE',
                    'available_from' => $module->available_from
                ];
            }

            if ($module->available_until && $module->available_until < $now) {
                return [
                    'can_access' => false,
                    'reason' => 'انتهت صلاحية الوحدة في ' . $module->available_until->format('Y-m-d H:i'),
                    'reason_en' => 'Module expired on ' . $module->available_until->format('Y-m-d H:i'),
                    'code' => 'EXPIRED',
                    'expired_at' => $module->available_until
                ];
            }
        }

        // Check if module is unlocked
        if (!$this->isModuleUnlocked($module, $student)) {
            return [
                'can_access' => false,
                'reason' => 'الوحدة مقفلة. يجب إتمام المتطلبات السابقة',
                'reason_en' => 'Module is locked. Complete previous requirements first',
                'code' => 'LOCKED',
                'unlock_conditions' => $module->unlock_conditions
            ];
        }

        // Check module access restrictions
        $restrictionCheck = $this->checkRestrictions(
            $module->id,
            'module',
            $student
        );

        if (!$restrictionCheck['has_access']) {
            return [
                'can_access' => false,
                'reason' => $restrictionCheck['reason'],
                'reason_en' => $restrictionCheck['reason_en'],
                'code' => 'RESTRICTED'
            ];
        }

        return [
            'can_access' => true
        ];
    }

    /**
     * Check access restrictions for a model (course/section/module)
     *
     * @param int $modelId Model ID
     * @param string $modelType Type: 'course', 'section', or 'module'
     * @param User $student Student user model
     * @return array Result with has_access status
     */
    protected function checkRestrictions(int $modelId, string $modelType, User $student): array
    {
        try {
            // Get restrictions based on model type
            switch ($modelType) {
                case 'course':
                    $restrictions = CourseAccessRestriction::where('course_id', $modelId)->get();
                    break;
                case 'section':
                    $restrictions = SectionAccessRestriction::where('section_id', $modelId)->get();
                    break;
                case 'module':
                    $restrictions = ModuleAccessRestriction::where('module_id', $modelId)->get();
                    break;
                default:
                    return ['has_access' => true];
            }

            // If no restrictions, allow access
            if ($restrictions->isEmpty()) {
                return ['has_access' => true];
            }

            // Check each restriction
            foreach ($restrictions as $restriction) {
                $matches = $this->checkRestrictionMatch($restriction, $student);

                // If restriction type is 'deny' and matches, deny access
                if ($restriction->access_type === 'deny' && $matches) {
                    return [
                        'has_access' => false,
                        'reason' => 'غير مسموح لك بالوصول إلى هذا المحتوى',
                        'reason_en' => 'You are not allowed to access this content',
                        'restriction' => $restriction
                    ];
                }

                // If restriction type is 'allow' and matches, allow access
                if ($restriction->access_type === 'allow' && $matches) {
                    return ['has_access' => true];
                }
            }

            // If we have 'allow' restrictions and none matched, deny access
            $hasAllowRestrictions = $restrictions->where('access_type', 'allow')->isNotEmpty();
            if ($hasAllowRestrictions) {
                return [
                    'has_access' => false,
                    'reason' => 'غير مسموح لك بالوصول إلى هذا المحتوى',
                    'reason_en' => 'You are not allowed to access this content'
                ];
            }

            return ['has_access' => true];

        } catch (\Exception $e) {
            Log::error('Error checking restrictions', [
                'model_id' => $modelId,
                'model_type' => $modelType,
                'student_id' => $student->id,
                'error' => $e->getMessage()
            ]);

            // In case of error, allow access by default
            return ['has_access' => true];
        }
    }

    /**
     * Check if a restriction matches the student
     *
     * @param mixed $restriction Restriction model
     * @param User $student Student user model
     * @return bool True if restriction matches
     */
    protected function checkRestrictionMatch($restriction, User $student): bool
    {
        switch ($restriction->restriction_type) {
            case 'user':
                return $restriction->restriction_id == $student->id;

            case 'group':
                // Check if student is member of the group
                return $student->courseGroupMemberships()
                    ->where('group_id', $restriction->restriction_id)
                    ->exists();

            case 'role':
                // Check if student has the role
                return $student->hasRole($restriction->restriction_id);

            case 'department':
                // Check if student belongs to the department
                return $student->department_id == $restriction->restriction_id;

            default:
                return false;
        }
    }

    /**
     * Check if a section is unlocked for a student
     *
     * @param CourseSection $section Section model
     * @param User $student Student user model
     * @return bool True if unlocked
     */
    public function isSectionUnlocked(CourseSection $section, User $student): bool
    {
        // If section is not locked, it's always unlocked
        if (!$section->is_locked) {
            return true;
        }

        // If no unlock conditions, section remains locked
        if (!$section->unlock_conditions || empty($section->unlock_conditions)) {
            return false;
        }

        // TODO: Implement unlock conditions logic
        // This could include:
        // - Complete previous section
        // - Complete specific modules
        // - Achieve minimum score
        // - etc.

        // For now, return locked status
        return false;
    }

    /**
     * Check if a module is unlocked for a student
     *
     * @param CourseModule $module Module model
     * @param User $student Student user model
     * @return bool True if unlocked
     */
    public function isModuleUnlocked(CourseModule $module, User $student): bool
    {
        // If no unlock conditions, module is unlocked
        if (!$module->unlock_conditions || empty($module->unlock_conditions)) {
            return true;
        }

        // TODO: Implement unlock conditions logic
        // This could include:
        // - Complete previous module
        // - Complete specific modules
        // - Achieve minimum score in previous module
        // - etc.

        // For now, return true (unlocked)
        return true;
    }

    /**
     * Get the reason why a student cannot access content
     *
     * @param mixed $model Course, Section, or Module model
     * @param User $student Student user model
     * @return array Detailed reason
     */
    public function getInaccessibleReason($model, User $student): array
    {
        if ($model instanceof Course) {
            return $this->canAccessCourse($model, $student);
        } elseif ($model instanceof CourseSection) {
            return $this->canAccessSection($model, $student);
        } elseif ($model instanceof CourseModule) {
            return $this->canAccessModule($model, $student);
        }

        return [
            'can_access' => false,
            'reason' => 'نوع محتوى غير معروف',
            'reason_en' => 'Unknown content type',
            'code' => 'UNKNOWN_TYPE'
        ];
    }
}
