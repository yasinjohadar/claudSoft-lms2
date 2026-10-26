<?php

namespace App\Http\Resources\Mobile\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * كورس مسجّل فيه الطالب (عنصر في قائمة "كورساتي").
 *
 * @mixin \App\Models\CourseEnrollment
 */
class EnrolledCourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $course = $this->course;

        return [
            'id' => (int) $course->id,
            'slug' => $course->slug,
            'title' => $course->title,
            'short_description' => $course->short_description,
            'image_url' => $course->image ? course_image_url($course->image) : null,
            'level' => $course->level,
            'duration_in_hours' => $course->duration_in_hours !== null ? (float) $course->duration_in_hours : null,
            'instructor' => $course->instructor ? [
                'id' => (int) $course->instructor->id,
                'name' => $course->instructor->name_ar ?: $course->instructor->name,
            ] : null,
            'enrollment' => [
                'id' => (int) $this->id,
                'status' => $this->enrollment_status,
                'completion_percentage' => (float) $this->completion_percentage,
                'enrolled_at' => $this->enrollment_date?->toIso8601String(),
                'last_accessed_at' => $this->last_accessed_at?->toIso8601String(),
                'completed_at' => $this->completed_at?->toIso8601String(),
            ],
        ];
    }
}
