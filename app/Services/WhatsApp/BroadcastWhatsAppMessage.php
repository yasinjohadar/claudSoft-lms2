<?php

namespace App\Services\WhatsApp;

use App\Models\User;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\CourseEnrollment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BroadcastWhatsAppMessage
{
    protected SendWhatsAppMessage $sendService;

    public function __construct(SendWhatsAppMessage $sendService)
    {
        $this->sendService = $sendService;
    }

    /**
     * Get students by criteria
     */
    public function getStudentsByCriteria(?int $courseId = null, ?int $groupId = null): Collection
    {
        $query = User::role('student')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->where('is_active', true);

        if ($groupId) {
            // Get students in this group
            $query->whereHas('courseGroupMemberships', function ($q) use ($groupId) {
                $q->where('group_id', $groupId);
            });
        }

        if ($courseId) {
            // Get students enrolled in the course (active enrollments only)
            $query->whereHas('courseEnrollments', function ($q) use ($courseId) {
                $q->where('course_id', $courseId)
                  ->where('enrollment_status', 'active');
            });
        }

        // Filter by valid phone format (E.164 format)
        return $query->get()->filter(function ($user) {
            return preg_match('/^\+[1-9][0-9]{1,14}$/', $user->phone);
        })->values();
    }

    /**
     * Replace placeholders in message template
     */
    public function replacePlaceholders(
        string $template,
        User $student,
        ?Course $course = null,
        ?CourseGroup $group = null
    ): string {
        $replacements = [
            '{student_name}' => $student->name,
            '{student_email}' => $student->email ?? '',
            '{course_name}' => '', // Default empty
            '{group_name}' => '', // Default empty
        ];

        // Get course from student's enrollment if not provided
        if (!$course && !$group) {
            // Try to get the first active enrollment's course
            $enrollment = $student->courseEnrollments()
                ->with('course')
                ->where('enrollment_status', 'active')
                ->first();
            
            if ($enrollment && $enrollment->course) {
                $course = $enrollment->course;
            }
        }

        if ($course) {
            $replacements['{course_name}'] = $course->title;
        }

        // Get group from student's memberships if not provided
        if (!$group) {
            // Try to get first group membership
            $membership = $student->courseGroupMemberships()
                ->with('group')
                ->first();
            
            if ($membership && $membership->group) {
                $group = $membership->group;
            }
        }

        if ($group) {
            $replacements['{group_name}'] = $group->name;
        }

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );
    }
}

