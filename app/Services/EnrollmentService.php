<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use App\Models\CourseGroup;
use App\Models\CourseEnrollment;
use App\Models\BulkEnrollmentSession;
use App\Models\CourseGroupMember;
use App\Events\N8nWebhookEvent;
use App\Models\GroupCourseEnrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Exception;

/**
 * Service for managing course enrollments
 *
 * Handles individual, group, and bulk enrollments with full transaction support
 */
class EnrollmentService
{
    /**
     * Enroll a single student in a course
     *
     * @param int $courseId Course ID
     * @param int $studentId Student user ID
     * @param int|null $enrolledBy User ID who is performing the enrollment
     * @return array Result with success status and data/error
     */
    public function enrollStudent(int $courseId, int $studentId, ?int $enrolledBy = null): array
    {
        try {
            DB::beginTransaction();

            $course = Course::find($courseId);
            $student = User::find($studentId);

            if (!$course) {
                return [
                    'success' => false,
                    'error' => 'Course not found',
                    'code' => 'COURSE_NOT_FOUND'
                ];
            }

            if (!$student) {
                return [
                    'success' => false,
                    'error' => 'Student not found',
                    'code' => 'STUDENT_NOT_FOUND'
                ];
            }

            // Check if student can enroll
            $canEnrollResult = $this->canEnroll($course, $student);
            if (!$canEnrollResult['can_enroll']) {
                return [
                    'success' => false,
                    'error' => $canEnrollResult['reason'],
                    'code' => $canEnrollResult['code']
                ];
            }

            // Check if already enrolled
            $existingEnrollment = CourseEnrollment::where('course_id', $courseId)
                ->where('student_id', $studentId)
                ->first();

            if ($existingEnrollment) {
                DB::rollBack();
                return [
                    'success' => false,
                    'error' => 'Student is already enrolled in this course',
                    'code' => 'ALREADY_ENROLLED',
                    'enrollment' => $existingEnrollment
                ];
            }

            // Create enrollment
            $enrollment = CourseEnrollment::create([
                'course_id' => $courseId,
                'student_id' => $studentId,
                'enrollment_date' => now(),
                'enrollment_status' => 'active',
                'enrolled_by' => $enrolledBy,
                'completion_percentage' => 0,
                'progress' => [],
            ]);

            DB::commit();

            // Dispatch n8n webhook event
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

            Log::info('Student enrolled successfully', [
                'course_id' => $courseId,
                'student_id' => $studentId,
                'enrolled_by' => $enrolledBy,
                'enrollment_id' => $enrollment->id
            ]);

            return [
                'success' => true,
                'enrollment' => $enrollment,
                'message' => 'Student enrolled successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error enrolling student', [
                'course_id' => $courseId,
                'student_id' => $studentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'An error occurred while enrolling the student',
                'code' => 'ENROLLMENT_ERROR',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Enroll multiple students in a course
     *
     * @param int $courseId Course ID
     * @param array $studentIds Array of student user IDs
     * @param int|null $enrolledBy User ID who is performing the enrollment
     * @return array Results with success/failed counts and details
     */
    public function enrollMultipleStudents(int $courseId, array $studentIds, ?int $enrolledBy = null): array
    {
        $results = [
            'success' => true,
            'total' => count($studentIds),
            'successful' => 0,
            'failed' => 0,
            'skipped' => 0,
            'enrollments' => [],
            'errors' => []
        ];

        $course = Course::find($courseId);
        if (!$course) {
            return [
                'success' => false,
                'error' => 'Course not found',
                'code' => 'COURSE_NOT_FOUND'
            ];
        }

        foreach ($studentIds as $studentId) {
            $result = $this->enrollStudent($courseId, $studentId, $enrolledBy);

            if ($result['success']) {
                $results['successful']++;
                $results['enrollments'][] = [
                    'student_id' => $studentId,
                    'enrollment' => $result['enrollment']
                ];
            } else {
                if (isset($result['code']) && $result['code'] === 'ALREADY_ENROLLED') {
                    $results['skipped']++;
                } else {
                    $results['failed']++;
                }

                $results['errors'][] = [
                    'student_id' => $studentId,
                    'error' => $result['error'],
                    'code' => $result['code'] ?? 'UNKNOWN'
                ];
            }
        }

        $results['success'] = $results['failed'] === 0;

        Log::info('Multiple students enrollment completed', [
            'course_id' => $courseId,
            'total' => $results['total'],
            'successful' => $results['successful'],
            'failed' => $results['failed'],
            'skipped' => $results['skipped']
        ]);

        return $results;
    }

    /**
     * Enroll an entire group in a course
     *
     * @param int $courseId Course ID
     * @param int $groupId Group ID
     * @param int|null $enrolledBy User ID who is performing the enrollment
     * @return array Results with success/failed counts and details
     */
    public function enrollGroup(int $courseId, int $groupId, ?int $enrolledBy = null): array
    {
        try {
            DB::beginTransaction();

            $course = Course::find($courseId);
            $group = CourseGroup::find($groupId);

            if (!$course) {
                return [
                    'success' => false,
                    'error' => 'Course not found',
                    'code' => 'COURSE_NOT_FOUND'
                ];
            }

            if (!$group) {
                return [
                    'success' => false,
                    'error' => 'Group not found',
                    'code' => 'GROUP_NOT_FOUND'
                ];
            }

            // Get all group members
            $members = CourseGroupMember::where('group_id', $groupId)->get();

            if ($members->isEmpty()) {
                DB::rollBack();
                return [
                    'success' => false,
                    'error' => 'Group has no members',
                    'code' => 'GROUP_EMPTY'
                ];
            }

            $studentIds = $members->pluck('student_id')->toArray();

            // Enroll all students
            $enrollmentResults = $this->enrollMultipleStudents($courseId, $studentIds, $enrolledBy);

            // Create group enrollment record
            if ($enrollmentResults['successful'] > 0) {
                GroupCourseEnrollment::create([
                    'group_id' => $groupId,
                    'course_id' => $courseId,
                    'enrolled_by' => $enrolledBy,
                    'enrollment_date' => now(),
                    'total_members' => count($studentIds),
                    'successful_enrollments' => $enrollmentResults['successful'],
                    'failed_enrollments' => $enrollmentResults['failed']
                ]);
            }

            DB::commit();

            Log::info('Group enrolled successfully', [
                'course_id' => $courseId,
                'group_id' => $groupId,
                'total_members' => count($studentIds),
                'successful' => $enrollmentResults['successful'],
                'failed' => $enrollmentResults['failed']
            ]);

            return [
                'success' => true,
                'group_id' => $groupId,
                'group_name' => $group->name,
                'results' => $enrollmentResults
            ];

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error enrolling group', [
                'course_id' => $courseId,
                'group_id' => $groupId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'An error occurred while enrolling the group',
                'code' => 'GROUP_ENROLLMENT_ERROR',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Process bulk enrollment from Excel file
     *
     * @param int $courseId Course ID
     * @param string $filePath Path to uploaded Excel file
     * @param int $uploadedBy User ID who uploaded the file
     * @return array Results with session details and enrollment statistics
     */
    public function processBulkEnrollment(int $courseId, string $filePath, int $uploadedBy): array
    {
        try {
            $course = Course::find($courseId);
            if (!$course) {
                return [
                    'success' => false,
                    'error' => 'Course not found',
                    'code' => 'COURSE_NOT_FOUND'
                ];
            }

            // Create bulk enrollment session
            $session = BulkEnrollmentSession::create([
                'course_id' => $courseId,
                'uploaded_by' => $uploadedBy,
                'file_path' => $filePath,
                'file_name' => basename($filePath),
                'enrollment_type' => 'individual',
                'status' => 'processing',
                'total_students' => 0,
                'successful_enrollments' => 0,
                'failed_enrollments' => 0,
                'skipped_enrollments' => 0,
                'errors' => [],
                'success_details' => []
            ]);

            try {
                // Read Excel file
                $fullPath = Storage::path($filePath);
                $spreadsheet = IOFactory::load($fullPath);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();

                // Skip header row
                $dataRows = array_slice($rows, 1);
                $session->update(['total_students' => count($dataRows)]);

                $studentIds = [];

                // Process each row
                foreach ($dataRows as $index => $row) {
                    $rowNumber = $index + 2; // +2 because we skipped header and arrays are 0-indexed

                    // Expected format: [student_id, email, name, ...]
                    // You can customize this based on your Excel format
                    $studentIdentifier = trim($row[0] ?? '');

                    if (empty($studentIdentifier)) {
                        $session->addFailure([
                            'row' => $rowNumber,
                            'error' => 'Empty student identifier',
                            'data' => $row
                        ]);
                        continue;
                    }

                    // Try to find student by ID, email, or student_id field
                    $student = User::where('id', $studentIdentifier)
                        ->orWhere('email', $studentIdentifier)
                        ->orWhere('student_id', $studentIdentifier)
                        ->first();

                    if (!$student) {
                        $session->addFailure([
                            'row' => $rowNumber,
                            'identifier' => $studentIdentifier,
                            'error' => 'Student not found'
                        ]);
                        continue;
                    }

                    // Check if already enrolled
                    $existingEnrollment = CourseEnrollment::where('course_id', $courseId)
                        ->where('student_id', $student->id)
                        ->first();

                    if ($existingEnrollment) {
                        $session->addSkipped();
                        continue;
                    }

                    $studentIds[] = $student->id;
                }

                // Enroll all found students
                if (!empty($studentIds)) {
                    $enrollmentResults = $this->enrollMultipleStudents($courseId, $studentIds, $uploadedBy);

                    // Update session with results
                    $session->update([
                        'successful_enrollments' => $enrollmentResults['successful'],
                        'failed_enrollments' => $session->failed_enrollments + $enrollmentResults['failed'],
                        'success_details' => $enrollmentResults['enrollments']
                    ]);
                }

                $session->markAsCompleted();

                Log::info('Bulk enrollment completed', [
                    'session_id' => $session->id,
                    'course_id' => $courseId,
                    'total' => $session->total_students,
                    'successful' => $session->successful_enrollments,
                    'failed' => $session->failed_enrollments,
                    'skipped' => $session->skipped_enrollments
                ]);

                return [
                    'success' => true,
                    'session' => $session,
                    'message' => 'Bulk enrollment completed successfully'
                ];

            } catch (Exception $e) {
                $session->markAsFailed([
                    'error' => 'File processing error',
                    'details' => $e->getMessage()
                ]);

                throw $e;
            }

        } catch (Exception $e) {
            Log::error('Error processing bulk enrollment', [
                'course_id' => $courseId,
                'file_path' => $filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'An error occurred while processing bulk enrollment',
                'code' => 'BULK_ENROLLMENT_ERROR',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Unenroll a student from a course
     *
     * @param int $enrollmentId Enrollment ID
     * @return array Result with success status and message
     */
    public function unenrollStudent(int $enrollmentId): array
    {
        try {
            DB::beginTransaction();

            $enrollment = CourseEnrollment::find($enrollmentId);

            if (!$enrollment) {
                return [
                    'success' => false,
                    'error' => 'Enrollment not found',
                    'code' => 'ENROLLMENT_NOT_FOUND'
                ];
            }

            $courseId = $enrollment->course_id;
            $studentId = $enrollment->student_id;

            // Delete enrollment
            $enrollment->delete();

            DB::commit();

            Log::info('Student unenrolled successfully', [
                'enrollment_id' => $enrollmentId,
                'course_id' => $courseId,
                'student_id' => $studentId
            ]);

            return [
                'success' => true,
                'message' => 'Student unenrolled successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error unenrolling student', [
                'enrollment_id' => $enrollmentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'An error occurred while unenrolling the student',
                'code' => 'UNENROLLMENT_ERROR',
                'details' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if a student can enroll in a course
     *
     * @param Course $course Course model
     * @param User $student Student user model
     * @return array Result with can_enroll status and reason if false
     */
    public function canEnroll(Course $course, User $student): array
    {
        // Check if course is published
        if (!$course->is_published) {
            return [
                'can_enroll' => false,
                'reason' => 'Course is not published',
                'code' => 'COURSE_NOT_PUBLISHED'
            ];
        }

        // Check if course is available
        if (!$course->isAvailable()) {
            return [
                'can_enroll' => false,
                'reason' => 'Course is not available at this time',
                'code' => 'COURSE_NOT_AVAILABLE'
            ];
        }

        // Check if enrollment is open
        if (!$course->isEnrollmentOpen()) {
            return [
                'can_enroll' => false,
                'reason' => 'Enrollment is not open for this course',
                'code' => 'ENROLLMENT_NOT_OPEN'
            ];
        }

        // Check if course is full
        if ($course->isFull()) {
            return [
                'can_enroll' => false,
                'reason' => 'Course has reached maximum capacity',
                'code' => 'COURSE_FULL'
            ];
        }

        // Check if student is active
        if (!$student->is_active) {
            return [
                'can_enroll' => false,
                'reason' => 'Student account is not active',
                'code' => 'STUDENT_NOT_ACTIVE'
            ];
        }

        return [
            'can_enroll' => true,
            'reason' => null,
            'code' => null
        ];
    }
}
