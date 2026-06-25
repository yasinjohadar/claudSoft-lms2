<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgrammingChallengeAttempt;
use App\Services\ProgrammingChallenge\ChallengeSubmissionService;
use Illuminate\Http\Request;

class ChallengeGradingController extends Controller
{
    public function __construct(
        protected ChallengeSubmissionService $submissionService
    ) {}

    public function index()
    {
        $attempts = ProgrammingChallengeAttempt::with(['challenge', 'student', 'latestSubmission.files'])
            ->pendingGrading()
            ->orderByDesc('submitted_at')
            ->paginate(20);

        return view('admin.pages.challenge-grading.index', compact('attempts'));
    }

    public function show(string $attemptId)
    {
        $attempt = ProgrammingChallengeAttempt::with([
            'challenge.languages',
            'challenge.files',
            'student',
            'latestSubmission.files.language',
            'submissions.files',
        ])->findOrFail($attemptId);

        if (! in_array($attempt->status, ['submitted', 'graded', 'returned'])) {
            return redirect()
                ->route('admin.challenge-grading.index')
                ->with('error', 'لا يمكن تصحيح محاولة لم يتم تسليمها بعد');
        }

        $submission = $attempt->latestSubmission;

        return view('admin.pages.challenge-grading.show', compact('attempt', 'submission'));
    }

    public function grade(Request $request, string $attemptId)
    {
        $attempt = ProgrammingChallengeAttempt::findOrFail($attemptId);

        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:' . ($attempt->max_score ?? $attempt->challenge->max_score),
            'feedback' => 'nullable|string|max:5000',
        ]);

        $this->submissionService->gradeAttempt(
            $attempt,
            (float) $validated['score'],
            $validated['feedback'] ?? null,
            auth()->id()
        );

        return redirect()
            ->route('admin.challenge-grading.index')
            ->with('success', 'تم تقييم التسليم بنجاح');
    }
}
