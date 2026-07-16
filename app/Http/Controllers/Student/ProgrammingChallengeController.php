<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\CourseModule;
use App\Models\ProgrammingChallenge;
use App\Models\ProgrammingChallengeAttempt;
use App\Services\ProgrammingChallenge\ChallengeSubmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProgrammingChallengeController extends Controller
{
    public function __construct(
        protected ChallengeSubmissionService $submissionService
    ) {}

    public function index(Request $request)
    {
        $studentId = auth()->id();

        $query = ProgrammingChallenge::published()
            ->visibleToStudent($studentId)
            ->with('languages');

        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        if ($request->filled('type')) {
            $query->where('challenge_type', $request->type);
        }

        $challenges = $query->orderByDesc('created_at')->paginate(12);

        return view('student.pages.challenges.index', compact('challenges'));
    }

    public function storeLivePreview(Request $request)
    {
        $validated = $request->validate([
            'html' => ['required', 'string', 'max:1500000'],
        ]);

        $token = Str::lower(Str::random(40));
        $cacheKey = 'challenge_live_preview:'.$token;

        Cache::put($cacheKey, [
            'html' => $validated['html'],
            'user_id' => auth()->id(),
            'created_at' => now()->toIso8601String(),
        ], now()->addHours(12));

        return response()->json([
            'success' => true,
            'token' => $token,
            'url' => route('student.challenges.live-preview.show', ['token' => $token]),
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

    public function show(string $id)
    {
        $challenge = ProgrammingChallenge::published()
            ->with(['languages', 'files'])
            ->findOrFail($id);

        $studentId = auth()->id();

        if (! $challenge->is_standalone || ! $challenge->isVisibleToStudent($studentId)) {
            abort(403, 'هذا التحدي غير متاح لحسابك');
        }

        $attempts = $challenge->studentAttempts($studentId)
            ->with(['latestSubmission.files'])
            ->get();
        $inProgress = $attempts->where('status', 'in_progress')->first();
        $canAttempt = $challenge->canStudentAttempt($studentId);
        $lastAttempt = $attempts->first();

        return view('student.pages.challenges.show', compact(
            'challenge',
            'attempts',
            'inProgress',
            'canAttempt',
            'lastAttempt'
        ));
    }

    public function work(Request $request, string $id)
    {
        $challenge = ProgrammingChallenge::with(['languages', 'files'])->findOrFail($id);

        if (! $challenge->is_published) {
            abort(403, 'التحدي غير منشور');
        }

        $courseModuleId = $request->query('module_id');
        $courseModule = null;
        $studentId = auth()->id();

        if ($courseModuleId) {
            $courseModule = CourseModule::with('course')->findOrFail($courseModuleId);
            $this->authorizeCourseAccess($courseModule);
        } elseif (! $challenge->is_standalone || ! $challenge->isVisibleToStudent($studentId)) {
            abort(403, 'هذا التحدي غير متاح لحسابك');
        }

        $attempt = ProgrammingChallengeAttempt::where('programming_challenge_id', $challenge->id)
            ->where('user_id', $studentId)
            ->where('status', 'in_progress')
            ->when($courseModuleId, fn ($q) => $q->where('course_module_id', $courseModuleId))
            ->first();

        if (! $attempt) {
            return redirect()
                ->back()
                ->with('error', 'يجب بدء محاولة أولاً');
        }

        $this->submissionService->restoreDraftFromPreviousIfNeeded($attempt, $challenge);

        $attempt->refresh();
        $draft = $attempt->draftSubmission?->load('files') ?? $attempt->latestSubmission?->load('files');

        return view('student.pages.challenges.work', compact(
            'challenge',
            'attempt',
            'draft',
            'courseModule'
        ));
    }

    public function startAttempt(Request $request, string $id)
    {
        $challenge = ProgrammingChallenge::findOrFail($id);

        if (! $challenge->is_published) {
            return back()->with('error', 'التحدي غير متاح');
        }

        $courseModuleId = $request->input('module_id') ?? $request->query('module_id');
        $courseModule = null;
        $studentId = auth()->id();

        if ($courseModuleId) {
            $courseModule = CourseModule::findOrFail($courseModuleId);
            $this->authorizeCourseAccess($courseModule);

            if ($courseModule->modulable_id != $challenge->id) {
                abort(403);
            }
        } elseif (! $challenge->is_standalone || ! $challenge->isVisibleToStudent($studentId)) {
            abort(403, 'هذا التحدي غير متاح لحسابك');
        }

        if (! $challenge->canStudentAttempt($studentId)) {
            return back()->with('error', 'استنفدت جميع المحاولات المسموحة');
        }

        $attempt = $this->submissionService->startOrResumeAttempt(
            $challenge,
            auth()->user(),
            $courseModuleId
        );

        $workUrl = route('student.challenges.work', $challenge->id);
        if ($courseModuleId) {
            $workUrl .= '?module_id=' . $courseModuleId;
        }

        return redirect($workUrl);
    }

    protected function authorizeCourseAccess(CourseModule $module): void
    {
        $enrolled = CourseEnrollment::where('course_id', $module->course_id)
            ->where('student_id', auth()->id())
            ->where('enrollment_status', 'active')
            ->exists();

        if (! $enrolled) {
            abort(403, 'غير مسجل في هذا الكورس');
        }
    }
}
