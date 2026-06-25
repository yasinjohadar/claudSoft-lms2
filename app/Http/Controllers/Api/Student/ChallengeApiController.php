<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\ProgrammingChallenge;
use App\Models\ProgrammingChallengeAttempt;
use App\Services\CodeExecution\CodeExecutionService;
use App\Services\ProgrammingChallenge\ChallengeSubmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChallengeApiController extends Controller
{
    public function __construct(
        protected ChallengeSubmissionService $submissionService,
        protected CodeExecutionService $executionService
    ) {}

    public function saveDraft(Request $request, int $id): JsonResponse
    {
        $attempt = $this->resolveAttempt($request, $id);

        $validated = $request->validate([
            'files' => 'required|array',
            'files.*.file_role' => 'required|string',
            'files.*.filename' => 'required|string',
            'files.*.content' => 'nullable|string',
            'files.*.programming_language_id' => 'nullable|integer',
            'student_notes' => 'nullable|string',
        ]);

        try {
            $submission = $this->submissionService->saveDraft(
                $attempt,
                $validated['files'],
                $validated['student_notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ المسودة',
                'data' => ['submission_id' => $submission->id, 'saved_at' => now()->toIso8601String()],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        $attempt = $this->resolveAttempt($request, $id);

        $validated = $request->validate([
            'files' => 'required|array',
            'files.*.file_role' => 'required|string',
            'files.*.filename' => 'required|string',
            'files.*.content' => 'nullable|string',
            'files.*.programming_language_id' => 'nullable|integer',
            'student_notes' => 'nullable|string',
        ]);

        try {
            $result = $this->submissionService->submit(
                $attempt,
                $validated['files'],
                $validated['student_notes'] ?? null
            );

            $submission = $result['submission'];
            $autoGrade = $result['auto_grade'];
            $freshAttempt = $attempt->fresh();

            $data = [
                'submission_id' => $submission->id,
                'attempt_id' => $freshAttempt->id,
                'status' => $freshAttempt->status,
                'grade_status' => $freshAttempt->grade_status,
            ];

            if ($autoGrade !== null) {
                $data['auto_grade'] = [
                    'graded' => $autoGrade['graded'] ?? false,
                    'message' => $autoGrade['message'] ?? null,
                    'mode' => $autoGrade['mode'] ?? null,
                    'all_passed' => $autoGrade['all_passed'] ?? false,
                    'score' => $autoGrade['score'] ?? null,
                    'max_score' => $autoGrade['max_score'] ?? null,
                    'passed' => $autoGrade['passed'] ?? 0,
                    'total' => $autoGrade['total'] ?? 0,
                    'results' => $autoGrade['results'] ?? [],
                ];
            }

            return response()->json([
                'success' => true,
                'message' => $autoGrade && ($autoGrade['graded'] ?? false)
                    ? ($autoGrade['message'] ?? 'تم تسليم التحدي بنجاح')
                    : 'تم تسليم التحدي بنجاح',
                'data' => $data,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function run(Request $request, int $id): JsonResponse
    {
        $challenge = ProgrammingChallenge::findOrFail($id);
        $attempt = $this->resolveAttempt($request, $id, false);

        $validated = $request->validate([
            'files' => 'required|array',
            'files.*.file_role' => 'required|string',
            'files.*.filename' => 'required|string',
            'files.*.content' => 'nullable|string',
            'stdin' => 'nullable|string',
        ]);

        if ($challenge->isCodeRunner()) {
            try {
                $result = $this->executionService->run(
                    $challenge,
                    $validated['files'],
                    $validated['stdin'] ?? '',
                    $attempt
                );

                return response()->json([
                    'success' => $result['success'],
                    'message' => $result['message'],
                    'data' => [
                        'stdout' => $result['stdout'],
                        'stderr' => $result['stderr'],
                        'exit_code' => $result['exit_code'],
                        'duration_ms' => $result['duration_ms'] ?? null,
                        'runtime_slug' => $result['runtime_slug'] ?? null,
                    ],
                ], $result['success'] ? 200 : 503);
            } catch (\RuntimeException $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 429);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'معاينة Sandbox تتم في المتصفح',
            'data' => ['mode' => 'client_web'],
        ]);
    }

    protected function resolveAttempt(Request $request, int $challengeId, bool $mustBeInProgress = true): ProgrammingChallengeAttempt
    {
        $query = ProgrammingChallengeAttempt::where('programming_challenge_id', $challengeId)
            ->where('user_id', $request->user()->id);

        if ($request->filled('attempt_id')) {
            $query->where('id', $request->input('attempt_id'));
        } else {
            $query->where('status', 'in_progress');
        }

        if ($request->filled('module_id')) {
            $query->where('course_module_id', $request->input('module_id'));
        }

        $attempt = $query->first();

        if (! $attempt) {
            abort(404, 'لم يتم العثور على محاولة نشطة');
        }

        if ($mustBeInProgress && ! $attempt->isInProgress()) {
            abort(422, 'المحاولة مُسلَّمة بالفعل');
        }

        return $attempt;
    }
}
