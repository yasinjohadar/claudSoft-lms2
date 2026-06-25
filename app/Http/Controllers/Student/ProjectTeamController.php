<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ProjectChallenge\ProjectChallenge;
use App\Models\ProjectChallenge\ProjectStage;
use App\Models\ProjectChallenge\ProjectTeam;
use App\Models\ProjectChallenge\ProjectTeamMember;
use App\Services\ProjectChallenge\ProjectSubmissionService;
use App\Services\ProjectChallenge\ProjectTeamService;
use Illuminate\Http\Request;

class ProjectTeamController extends Controller
{
    public function __construct(
        protected ProjectTeamService $teamService,
        protected ProjectSubmissionService $submissionService
    ) {}

    public function create(string $challengeId)
    {
        $challenge = ProjectChallenge::published()->open()->findOrFail($challengeId);

        return view('student.pages.project-challenges.create-team', compact('challenge'));
    }

    public function store(Request $request, string $challengeId)
    {
        $challenge = ProjectChallenge::published()->open()->findOrFail($challengeId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'logo' => 'nullable|string|max:500',
        ]);

        try {
            $team = $this->teamService->createTeam($challenge, auth()->user(), $validated);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('student.project-teams.workspace', $team->id)
            ->with('success', 'تم إنشاء الفريق بنجاح');
    }

    public function requestJoin(Request $request, string $teamId)
    {
        $validated = $request->validate([
            'message' => 'nullable|string|max:1000',
        ]);

        $team = ProjectTeam::with('challenge')->findOrFail($teamId);

        try {
            $result = $this->teamService->requestJoin(
                $team,
                auth()->user(),
                $validated['message'] ?? null
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($result instanceof ProjectTeamMember) {
            return redirect()
                ->route('student.project-teams.workspace', $team->id)
                ->with('success', 'تم الانضمام إلى الفريق');
        }

        return back()->with('success', 'تم إرسال طلب الانضمام');
    }

    public function workspace(string $teamId)
    {
        $team = ProjectTeam::with([
            'challenge.stages',
            'activeMembers.user',
            'leader',
            'activities.actor',
            'submissions.links',
            'showcase',
        ])->findOrFail($teamId);

        if (! $team->hasMember(auth()->id())) {
            abort(403, 'ليس لديك صلاحية الوصول إلى مساحة عمل هذا الفريق');
        }

        $stages = $team->challenge->stages->map(function (ProjectStage $stage) use ($team) {
            $submission = $team->submissions->firstWhere('project_stage_id', $stage->id);
            $unlocked = $this->submissionService->isStageUnlockedForTeam($team, $stage);

            return [
                'stage' => $stage,
                'submission' => $submission,
                'unlocked' => $unlocked,
            ];
        });

        return view('student.pages.project-challenges.workspace', compact('team', 'stages'));
    }

    public function saveDraft(Request $request, string $teamId, string $stageId)
    {
        $team = ProjectTeam::findOrFail($teamId);

        if (! $team->hasMember(auth()->id())) {
            abort(403);
        }

        $stage = ProjectStage::where('project_challenge_id', $team->project_challenge_id)
            ->findOrFail($stageId);

        $validated = $request->validate([
            'links' => 'required|array|min:1',
            'links.*.link_type' => 'required|string|in:' . implode(',', array_keys(config('project_challenges.link_types', []))),
            'links.*.title' => 'nullable|string|max:255',
            'links.*.url' => 'required|url|max:2048',
            'links.*.sort_order' => 'nullable|integer|min:0',
        ]);

        $submission = $this->submissionService->getOrCreateDraftSubmission($team, $stage);

        try {
            $this->submissionService->saveDraftLinks($submission, $validated['links'], auth()->user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم حفظ المسودة');
    }

    public function submitStage(string $teamId, string $stageId)
    {
        $team = ProjectTeam::findOrFail($teamId);

        if (! $team->hasMember(auth()->id())) {
            abort(403);
        }

        $stage = ProjectStage::where('project_challenge_id', $team->project_challenge_id)
            ->findOrFail($stageId);

        $submission = $this->submissionService->getOrCreateDraftSubmission($team, $stage);

        try {
            $this->submissionService->submitStage($submission, auth()->user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تسليم المرحلة بنجاح');
    }
}
