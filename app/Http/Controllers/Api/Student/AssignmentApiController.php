<?php

namespace App\Http\Controllers\Api\Student;

use App\Events\AssignmentSubmitted;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class AssignmentApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $studentId = $request->user()->id;

        $assignments = Assignment::with(['course', 'lesson', 'targetGroup'])
            ->visibleToStudent($studentId)
            ->where('is_published', true)
            ->orderBy('due_date')
            ->get();

        $submissionsByAssignment = AssignmentSubmission::where('student_id', $studentId)
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->get()
            ->groupBy('assignment_id');

        $items = $assignments->map(function ($assignment) use ($submissionsByAssignment, $studentId) {
            $submissions = $submissionsByAssignment->get($assignment->id, collect());
            $latest = $submissions->sortByDesc('attempt_number')->first();
            $status = $this->resolveStatus($assignment, $latest);

            return [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'course_id' => $assignment->course_id,
                'course_title' => $assignment->course?->title,
                'due_date' => optional($assignment->due_date)->toIso8601String(),
                'max_grade' => (float) $assignment->max_grade,
                'status' => $status,
                'grade' => $latest?->grade,
                'can_submit' => $this->canStudentSubmit($assignment, $latest),
                'submissions_count' => $submissions->count(),
            ];
        })->values();

        return response()->json(['success' => true, 'data' => ['assignments' => $items]]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $assignment = Assignment::with(['course', 'lesson', 'targetGroup'])
            ->where('is_published', true)
            ->findOrFail($id);

        if (! $assignment->isVisibleToStudent((int) $request->user()->id)) {
            abort(403, 'هذا الواجب غير متاح لمجموعتك.');
        }

        $submissions = AssignmentSubmission::where('assignment_id', $id)
            ->where('student_id', $request->user()->id)
            ->orderByDesc('attempt_number')
            ->get();

        $latest = $submissions->first();

        return response()->json([
            'success' => true,
            'data' => [
                'assignment' => $this->serializeAssignment($assignment),
                'submissions' => $submissions->map(fn ($s) => $this->serializeSubmission($s))->values(),
                'can_submit' => $this->canStudentSubmit($assignment, $latest),
                'can_resubmit' => $latest && $assignment->canResubmit($latest),
            ],
        ]);
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        $assignment = Assignment::findOrFail($id);

        if (! $assignment->isVisibleToStudent((int) $request->user()->id)) {
            abort(403, 'هذا الواجب غير متاح لمجموعتك.');
        }

        $latest = AssignmentSubmission::where('assignment_id', $id)
            ->where('student_id', $request->user()->id)
            ->orderByDesc('attempt_number')
            ->first();

        if ($latest && !$assignment->canResubmit($latest)) {
            return response()->json(['success' => false, 'message' => 'لا يمكنك إعادة تسليم هذا الواجب'], 422);
        }

        $rules = ['submission_text' => 'nullable|string'];
        if (in_array($assignment->submission_type, ['link', 'both'])) {
            $rules['links'] = 'nullable|array|max:' . $assignment->max_links;
            $rules['links.*'] = 'url';
        }
        if (in_array($assignment->submission_type, ['file', 'both'])) {
            $rules['files'] = 'nullable|array|max:' . $assignment->max_files;
            $rules['files.*'] = 'file|max:' . $assignment->max_file_size;
        }

        $validated = $request->validate($rules);
        $uploadedFiles = $this->handleFiles($request, $assignment);
        $attemptNumber = $latest ? $latest->attempt_number + 1 : 1;

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $request->user()->id,
            'submission_text' => $validated['submission_text'] ?? null,
            'submitted_links' => $validated['links'] ?? [],
            'submitted_files' => $uploadedFiles,
            'status' => 'submitted',
            'submitted_at' => now(),
            'is_late' => $assignment->due_date && now()->gt($assignment->due_date),
            'attempt_number' => $attemptNumber,
        ]);

        AssignmentSubmitted::dispatch($request->user(), $assignment, $submission);

        return response()->json([
            'success' => true,
            'message' => 'تم تسليم الواجب بنجاح',
            'data' => $this->serializeSubmission($submission),
        ]);
    }

    public function saveDraft(Request $request, int $id): JsonResponse
    {
        $assignment = Assignment::findOrFail($id);

        if (! $assignment->isVisibleToStudent((int) $request->user()->id)) {
            abort(403, 'هذا الواجب غير متاح لمجموعتك.');
        }

        $draft = AssignmentSubmission::where('assignment_id', $id)
            ->where('student_id', $request->user()->id)
            ->where('status', 'draft')
            ->first();

        if (!$draft) {
            $attemptNumber = AssignmentSubmission::where('assignment_id', $id)
                ->where('student_id', $request->user()->id)
                ->max('attempt_number') + 1;
            $draft = new AssignmentSubmission([
                'assignment_id' => $assignment->id,
                'student_id' => $request->user()->id,
                'status' => 'draft',
                'attempt_number' => $attemptNumber,
            ]);
        }

        $files = $draft->submitted_files ?? [];
        $files = array_merge($files, $this->handleFiles($request, $assignment));
        $draft->submission_text = $request->input('submission_text');
        $draft->submitted_links = $request->input('links', []);
        $draft->submitted_files = $files;
        $draft->save();

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ المسودة',
            'data' => $this->serializeSubmission($draft),
        ]);
    }

    private function handleFiles(Request $request, Assignment $assignment): array
    {
        $uploaded = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = cloud_upload('assignments/submissions/' . $assignment->id, $file);
                $uploaded[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'url' => storage_url($path),
                    'size' => $file->getSize(),
                    'type' => $file->getClientOriginalExtension(),
                ];
            }
        }
        return $uploaded;
    }

    private function resolveStatus(Assignment $assignment, ?AssignmentSubmission $latest): string
    {
        if (!$latest) {
            return ($assignment->due_date && now()->gt($assignment->due_date)) ? 'overdue' : 'pending';
        }
        if ($latest->grade !== null) {
            return 'graded';
        }
        return $latest->status === 'draft' ? 'draft' : 'submitted';
    }

    private function canStudentSubmit(Assignment $assignment, ?AssignmentSubmission $latest): bool
    {
        if (!$assignment->isAvailable()) {
            return false;
        }
        if (!$latest) {
            return !($assignment->isPastDue() && !$assignment->canSubmitLate());
        }
        return $assignment->canResubmit($latest);
    }

    private function serializeAssignment(Assignment $a): array
    {
        return [
            'id' => $a->id,
            'title' => $a->title,
            'description' => $a->description,
            'course_id' => $a->course_id,
            'course_title' => $a->course?->title,
            'due_date' => optional($a->due_date)->toIso8601String(),
            'max_grade' => (float) $a->max_grade,
            'submission_type' => $a->submission_type,
            'max_files' => $a->max_files,
            'max_links' => $a->max_links,
        ];
    }

    private function serializeSubmission(AssignmentSubmission $s): array
    {
        return [
            'id' => $s->id,
            'status' => $s->status,
            'submission_text' => $s->submission_text,
            'submitted_links' => $s->submitted_links ?? [],
            'submitted_files' => collect($s->submitted_files ?? [])->map(fn ($f) => [
                'name' => $f['name'] ?? '',
                'url' => isset($f['path']) ? storage_url($f['path']) : ($f['url'] ?? null),
            ])->values(),
            'grade' => $s->grade,
            'feedback' => $s->feedback,
            'attempt_number' => $s->attempt_number,
            'submitted_at' => optional($s->submitted_at)->toIso8601String(),
            'is_late' => (bool) $s->is_late,
        ];
    }
}
