<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectChallenge\ProjectStageSubmission;
use App\Services\ProjectChallenge\ProjectGradingService;
use Illuminate\Http\Request;

class ProjectGradingController extends Controller
{
    public function __construct(
        protected ProjectGradingService $gradingService
    ) {}

    public function index()
    {
        $submissions = ProjectStageSubmission::with([
            'team.challenge',
            'stage',
            'submitter',
        ])
            ->whereIn('status', ['submitted', 'under_review'])
            ->orderByDesc('submitted_at')
            ->paginate(20);

        return view('admin.pages.project-grading.index', compact('submissions'));
    }

    public function show(string $submissionId)
    {
        $submission = ProjectStageSubmission::with([
            'team.challenge.stages',
            'team.activeMembers.user',
            'stage',
            'submitter',
            'links',
            'reviewer',
        ])->findOrFail($submissionId);

        if (! in_array($submission->status, ['submitted', 'under_review', 'approved', 'rejected', 'resubmit_required'], true)) {
            return redirect()
                ->route('admin.project-grading.index')
                ->with('error', 'لا يمكن تصحيح تسليم غير مُرسَل');
        }

        return view('admin.pages.project-grading.show', compact('submission'));
    }

    public function grade(Request $request, string $submissionId)
    {
        $submission = ProjectStageSubmission::with('stage')->findOrFail($submissionId);

        $maxScore = (float) ($submission->max_score ?? $submission->stage->max_score);

        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:' . $maxScore,
            'feedback' => 'nullable|string|max:5000',
            'progress_percent' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:approved,rejected,resubmit_required',
            'reject_reason' => 'nullable|string|max:1000',
        ]);

        try {
            $this->gradingService->gradeSubmission(
                $submission,
                (float) $validated['score'],
                $validated['feedback'] ?? $validated['reject_reason'] ?? null,
                auth()->user(),
                isset($validated['progress_percent']) ? (float) $validated['progress_percent'] : null,
                $validated['status']
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.project-grading.index')
            ->with('success', 'تم تقييم التسليم بنجاح');
    }
}
