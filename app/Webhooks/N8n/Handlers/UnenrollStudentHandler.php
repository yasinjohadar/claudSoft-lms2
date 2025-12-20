<?php

namespace App\Webhooks\N8n\Handlers;

use App\Models\User;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Events\N8nWebhookEvent;
use Illuminate\Support\Facades\DB;

class UnenrollStudentHandler extends BaseHandler
{
    public function handle(array $payload): array
    {
        try {
            // Support multiple ways to identify student and course
            $studentId = $payload['student_id'] 
                      ?? $payload['user_id'] 
                      ?? (isset($payload['student_email']) ? User::where('email', $payload['student_email'])->value('id') : null);
            
            $courseId = $payload['course_id'] 
                     ?? (isset($payload['course_slug']) ? Course::where('slug', $payload['course_slug'])->value('id') : null)
                     ?? (isset($payload['course_code']) ? Course::where('code', $payload['course_code'])->value('id') : null);

            if (!$studentId) {
                return $this->error('Student not found. Provide student_id, user_id, or student_email');
            }

            if (!$courseId) {
                return $this->error('Course not found. Provide course_id, course_slug, or course_code');
            }

            $student = User::find($studentId);
            $course = Course::find($courseId);

            if (!$student) {
                return $this->error('Student not found');
            }

            if (!$course) {
                return $this->error('Course not found');
            }

            DB::beginTransaction();

            $enrollment = CourseEnrollment::where('course_id', $courseId)
                ->where('student_id', $studentId)
                ->first();

            if (!$enrollment) {
                DB::rollBack();
                return $this->error('Enrollment not found');
            }

            // Check if can unenroll (don't allow unenrolling from completed courses)
            if ($enrollment->enrollment_status === 'completed') {
                DB::rollBack();
                return $this->error('Cannot unenroll from completed course');
            }

            $enrollment->delete();

            DB::commit();

            // Dispatch n8n webhook event
            event(new N8nWebhookEvent('student.unenrolled', [
                'student_id' => $studentId,
                'student_name' => $student->name,
                'student_email' => $student->email,
                'course_id' => $courseId,
                'course_title' => $course->title,
                'unenrolled_at' => now()->toIso8601String(),
                'reason' => $payload['reason'] ?? null,
            ]));

            $this->logSuccess('Student unenrolled successfully', [
                'user_id' => $studentId,
                'course_id' => $courseId,
            ]);

            return $this->success('Student unenrolled successfully', [
                'student_id' => $studentId,
                'student_name' => $student->name,
                'course_id' => $courseId,
                'course_title' => $course->title,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('Failed to unenroll student', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return $this->error('Failed to unenroll student: ' . $e->getMessage());
        }
    }
}
