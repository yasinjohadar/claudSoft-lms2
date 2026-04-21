<?php

namespace App\Events;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentEnrolledInCourse
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $student,
        public Course $course,
        public CourseEnrollment $enrollment
    ) {}
}
