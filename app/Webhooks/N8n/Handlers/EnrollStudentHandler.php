<?php

namespace App\Webhooks\N8n\Handlers;

use App\Models\User;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Events\N8nWebhookEvent;
use Illuminate\Support\Facades\DB;

class EnrollStudentHandler extends BaseHandler
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

            // Check if already enrolled
            $existing = CourseEnrollment::where('course_id', $courseId)
                ->where('student_id', $studentId)
                ->first();

            if ($existing) {
                DB::rollBack();
                return $this->error('Student is already enrolled in this course', [
                    'enrollment_id' => $existing->id,
                ]);
            }

            // Enroll student
            $enrollment = CourseEnrollment::create([
                'course_id' => $courseId,
                'student_id' => $studentId,
                'enrollment_date' => $payload['enrollment_date'] ?? now(),
                'enrollment_status' => $payload['enrollment_status'] ?? 'active',
                'enrolled_by' => $payload['enrolled_by'] ?? null,
                'completion_percentage' => 0,
            ]);

            DB::commit();

            // Dispatch n8n webhook event (only for active enrollments)
            if ($enrollment->enrollment_status === 'active') {
                event(new N8nWebhookEvent('student.enrolled', [
                    'student_id' => $enrollment->student_id,
                    'student_name' => $student->name,
                    'student_email' => $student->email,
                    'course_id' => $enrollment->course_id,
                    'course_title' => $course->title,
                    'enrollment_id' => $enrollment->id,
                    'enrollment_date' => $enrollment->enrollment_date->toIso8601String(),
                    'enrolled_by' => $enrollment->enrolled_by,
                ]));
            }

            $this->logSuccess('Student enrolled successfully', [
                'user_id' => $student->id,
                'course_id' => $course->id,
                'enrollment_id' => $enrollment->id,
            ]);

            return $this->success('Student enrolled successfully', [
                'enrollment_id' => $enrollment->id,
                'student_id' => $student->id,
                'student_name' => $student->name,
                'student_email' => $student->email,
                'course_id' => $course->id,
                'course_title' => $course->title,
                'enrollment_status' => $enrollment->enrollment_status,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('Failed to enroll student', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return $this->error('Failed to enroll student: ' . $e->getMessage());
        }
    }
}
