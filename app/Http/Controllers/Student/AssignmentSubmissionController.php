<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Events\AssignmentSubmitted;

class AssignmentSubmissionController extends Controller
{
    /**
     * Display all assignments for the student with statistics.
     */
    public function index()
    {
        $studentId = auth()->id();

        // Get all enrolled courses for the student
        $enrolledCourseIds = \App\Models\CourseEnrollment::where('student_id', $studentId)
            ->where('enrollment_status', 'active')
            ->pluck('course_id');

        // Get all published assignments from enrolled courses
        $assignments = Assignment::with(['course', 'lesson'])
            ->whereIn('course_id', $enrolledCourseIds)
            ->where('is_published', true)
            ->orderBy('due_date', 'asc')
            ->get();

        // Get all submissions for these assignments by this student
        $submissionsByAssignment = AssignmentSubmission::where('student_id', $studentId)
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->get()
            ->groupBy('assignment_id');

        // Calculate statistics
        $stats = [
            'total' => $assignments->count(),
            'submitted' => 0,
            'graded' => 0,
            'pending' => 0,
            'overdue' => 0,
            'average_grade' => 0,
            'total_points' => 0,
            'earned_points' => 0,
        ];

        $courseStats = [];
        $assignmentsData = [];

        foreach ($assignments as $assignment) {
            $submissions = $submissionsByAssignment->get($assignment->id, collect());
            $latestSubmission = $submissions->sortByDesc('attempt_number')->first();

            $status = 'pending';
            $grade = null;
            $feedback = null;

            if ($latestSubmission) {
                if ($latestSubmission->grade !== null) {
                    $status = 'graded';
                    $grade = $latestSubmission->grade;
                    $feedback = $latestSubmission->feedback;
                    $stats['graded']++;
                    $stats['earned_points'] += $grade;
                } else {
                    $status = 'submitted';
                    $stats['submitted']++;
                }
            } else {
                if ($assignment->due_date && now()->gt($assignment->due_date)) {
                    $status = 'overdue';
                    $stats['overdue']++;
                } else {
                    $stats['pending']++;
                }
            }

            $stats['total_points'] += $assignment->max_grade;

            // Calculate per-course statistics
            $courseId = $assignment->course_id;
            if (!isset($courseStats[$courseId])) {
                $courseStats[$courseId] = [
                    'course' => $assignment->course,
                    'total' => 0,
                    'graded' => 0,
                    'submitted' => 0,
                    'pending' => 0,
                    'total_points' => 0,
                    'earned_points' => 0,
                    'average_grade' => 0,
                ];
            }

            $courseStats[$courseId]['total']++;
            $courseStats[$courseId]['total_points'] += $assignment->max_grade;

            if ($status == 'graded') {
                $courseStats[$courseId]['graded']++;
                $courseStats[$courseId]['earned_points'] += $grade;
            } elseif ($status == 'submitted') {
                $courseStats[$courseId]['submitted']++;
            } elseif ($status == 'pending' || $status == 'overdue') {
                $courseStats[$courseId]['pending']++;
            }

            $assignmentsData[] = [
                'assignment' => $assignment,
                'latest_submission' => $latestSubmission,
                'submissions_count' => $submissions->count(),
                'status' => $status,
                'grade' => $grade,
                'feedback' => $feedback,
                'can_submit' => $this->canStudentSubmit($assignment, $latestSubmission),
            ];
        }

        // Calculate overall average grade
        if ($stats['graded'] > 0 && $stats['total_points'] > 0) {
            $stats['average_grade'] = round(($stats['earned_points'] / $stats['total_points']) * 100, 1);
        }

        // Calculate per-course average grades
        foreach ($courseStats as &$courseStat) {
            if ($courseStat['graded'] > 0 && $courseStat['total_points'] > 0) {
                $courseStat['average_grade'] = round(($courseStat['earned_points'] / $courseStat['total_points']) * 100, 1);
            }
        }

        return view('student.assignments.index', compact('assignmentsData', 'stats', 'courseStats'));
    }

    /**
     * Display assignment details for student.
     */
    public function show($id)
    {
        $assignment = Assignment::with(['course', 'lesson'])
            ->where('is_published', true)
            ->findOrFail($id);

        // Get student's submissions for this assignment
        $submissions = AssignmentSubmission::where('assignment_id', $id)
            ->where('student_id', auth()->id())
            ->orderBy('attempt_number', 'desc')
            ->get();

        $latestSubmission = $submissions->first();

        // Check if can submit/resubmit
        $canSubmit = $this->canStudentSubmit($assignment, $latestSubmission);
        $canResubmit = $latestSubmission && $assignment->canResubmit($latestSubmission);

        return view('student.assignments.show', compact(
            'assignment',
            'submissions',
            'latestSubmission',
            'canSubmit',
            'canResubmit'
        ));
    }

    /**
     * Store or update a submission.
     */
    public function submit(Request $request, $id)
    {
        $assignment = Assignment::findOrFail($id);

        // Get latest submission
        $latestSubmission = AssignmentSubmission::where('assignment_id', $id)
            ->where('student_id', auth()->id())
            ->orderBy('attempt_number', 'desc')
            ->first();

        // Check if can submit
        if ($latestSubmission && !$assignment->canResubmit($latestSubmission)) {
            return back()->withErrors(['error' => 'لا يمكنك إعادة تسليم هذا الواجب']);
        }

        // Validate based on submission type
        $rules = [
            'submission_text' => 'nullable|string',
        ];

        if (in_array($assignment->submission_type, ['link', 'both'])) {
            $rules['links'] = 'nullable|array|max:' . $assignment->max_links;
            $rules['links.*'] = 'url';
        }

        if (in_array($assignment->submission_type, ['file', 'both'])) {
            $rules['files'] = 'nullable|array|max:' . $assignment->max_files;
            $rules['files.*'] = 'file|max:' . $assignment->max_file_size;
        }

        $validated = $request->validate($rules);

        // Handle file uploads
        $uploadedFiles = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('assignments/submissions/' . $assignment->id, 'public');
                $uploadedFiles[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getClientOriginalExtension(),
                ];
            }
        }

        // Determine if it's late
        $isLate = $assignment->due_date && now()->gt($assignment->due_date);

        // Calculate attempt number
        $attemptNumber = $latestSubmission ? $latestSubmission->attempt_number + 1 : 1;

        // Create new submission
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => auth()->id(),
            'submission_text' => $validated['submission_text'] ?? null,
            'submitted_links' => $validated['links'] ?? [],
            'submitted_files' => $uploadedFiles,
            'status' => 'submitted',
            'submitted_at' => now(),
            'is_late' => $isLate,
            'attempt_number' => $attemptNumber,
        ]);

        // Dispatch AssignmentSubmitted event for gamification
        AssignmentSubmitted::dispatch(auth()->user(), $assignment, $submission);

        return redirect()->route('student.assignments.show', $assignment->id)
            ->with('success', 'تم تسليم الواجب بنجاح');
    }

    /**
     * Save submission as draft.
     */
    public function saveDraft(Request $request, $id)
    {
        $assignment = Assignment::findOrFail($id);

        // Get or create draft submission
        $draft = AssignmentSubmission::where('assignment_id', $id)
            ->where('student_id', auth()->id())
            ->where('status', 'draft')
            ->first();

        if (!$draft) {
            $attemptNumber = AssignmentSubmission::where('assignment_id', $id)
                ->where('student_id', auth()->id())
                ->max('attempt_number') + 1;

            $draft = new AssignmentSubmission([
                'assignment_id' => $assignment->id,
                'student_id' => auth()->id(),
                'status' => 'draft',
                'attempt_number' => $attemptNumber,
            ]);
        }

        // Handle file uploads
        $uploadedFiles = $draft->submitted_files ?? [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('assignments/submissions/' . $assignment->id, 'public');
                $uploadedFiles[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getClientOriginalExtension(),
                ];
            }
        }

        $draft->submission_text = $request->input('submission_text');
        $draft->submitted_links = $request->input('links', []);
        $draft->submitted_files = $uploadedFiles;
        $draft->save();

        return back()->with('success', 'تم حفظ المسودة بنجاح');
    }

    /**
     * Delete a file from submission.
     */
    public function deleteFile(Request $request, $submissionId)
    {
        $submission = AssignmentSubmission::where('id', $submissionId)
            ->where('student_id', auth()->id())
            ->firstOrFail();

        $fileIndex = $request->input('index');

        if (isset($submission->submitted_files[$fileIndex])) {
            $file = $submission->submitted_files[$fileIndex];
            Storage::disk('public')->delete($file['path']);

            $files = $submission->submitted_files;
            unset($files[$fileIndex]);
            $submission->update(['submitted_files' => array_values($files)]);
        }

        return back()->with('success', 'تم حذف الملف بنجاح');
    }

    /**
     * Check if student can submit.
     */
    private function canStudentSubmit($assignment, $latestSubmission)
    {
        // If not available yet
        if (!$assignment->isAvailable()) {
            return false;
        }

        // If no previous submission, can submit
        if (!$latestSubmission) {
            // Check if past due and late submission not allowed
            if ($assignment->isPastDue() && !$assignment->canSubmitLate()) {
                return false;
            }
            return true;
        }

        // If already submitted, check if can resubmit
        return $assignment->canResubmit($latestSubmission);
    }
}
