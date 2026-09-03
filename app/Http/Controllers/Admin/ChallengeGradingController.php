<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseGroup;
use App\Models\ProgrammingChallengeAttempt;
use App\Services\ProgrammingChallenge\ChallengeSubmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChallengeGradingController extends Controller
{
    public function __construct(
        protected ChallengeSubmissionService $submissionService
    ) {}

    public function storeLivePreview(Request $request)
    {
        $validated = $request->validate([
            'html' => ['required', 'string', 'max:1500000'],
        ]);

        $token = Str::lower(Str::random(40));

        Cache::put('challenge_live_preview:'.$token, [
            'html' => $validated['html'],
            'user_id' => auth()->id(),
            'created_at' => now()->toIso8601String(),
        ], now()->addHours(12));

        return response()->json([
            'success' => true,
            'token' => $token,
            'url' => route('admin.challenge-grading.live-preview.show', ['token' => $token]),
        ]);
    }

    public function showLivePreview(string $token)
    {
        if (! preg_match('/^[a-z0-9]{32,64}$/', $token)) {
            abort(404);
        }

        $payload = Cache::get('challenge_live_preview:'.$token);

        if (! is_array($payload) || empty($payload['html'])) {
            return response()
                ->view('student.pages.challenges.live-preview-missing')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        }

        return response($payload['html'], 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    public function index(Request $request)
    {
        $groupId = $request->get('group_id');

        $query = ProgrammingChallengeAttempt::with(['challenge', 'student', 'latestSubmission.files'])
            ->pendingGrading();

        if (filled($groupId)) {
            $studentIds = DB::table('course_group_members')->where('group_id', $groupId)->pluck('student_id');
            $query->whereIn('user_id', $studentIds);
        }

        $attempts = $query->orderByDesc('submitted_at')
            ->paginate(20)
            ->appends($request->query());

        $stats = [
            'pending' => ProgrammingChallengeAttempt::pendingGrading()->count(),
            'graded_today' => ProgrammingChallengeAttempt::query()
                ->where('status', 'graded')
                ->whereDate('graded_at', today())
                ->count(),
            'graded_total' => ProgrammingChallengeAttempt::query()
                ->where('status', 'graded')
                ->count(),
        ];

        $pendingStudentIds = ProgrammingChallengeAttempt::pendingGrading()->pluck('user_id')->unique();
        $pendingGroupIds = DB::table('course_group_members')
            ->whereIn('student_id', $pendingStudentIds)
            ->distinct()
            ->pluck('group_id');
        $groups = CourseGroup::whereIn('id', $pendingGroupIds)->orderBy('name')->get();

        return view('admin.pages.challenge-grading.index', compact('attempts', 'stats', 'groups', 'groupId'));
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

        $submission = $attempt->submissions()
            ->where('status', '!=', 'draft')
            ->orderByDesc('submission_number')
            ->orderByDesc('id')
            ->with('files.language')
            ->first()
            ?? $attempt->latestSubmission;

        return view('admin.pages.challenge-grading.show', compact('attempt', 'submission'));
    }

    public function grade(Request $request, string $attemptId)
    {
        $attempt = ProgrammingChallengeAttempt::findOrFail($attemptId);

        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:' . ($attempt->max_score ?? $attempt->challenge->max_score),
            'feedback' => 'nullable|string|max:50000',
        ]);

        $this->submissionService->gradeAttempt(
            $attempt,
            (float) $validated['score'],
            $validated['feedback'] ?? null,
            auth()->id()
        );

        return redirect()
            ->route('programming-challenges.attempts', $attempt->programming_challenge_id)
            ->with('success', 'تم تقييم التسليم بنجاح');
    }
}
