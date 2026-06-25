<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectChallenge\ProjectChallenge;
use App\Models\ProjectChallenge\ProjectStage;
use App\Models\ProjectChallenge\ProjectTeam;
use App\Models\ProjectChallenge\ProjectTeamMember;
use App\Models\User;
use App\Services\ProjectChallenge\ProjectTeamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectTeamController extends Controller
{
    public function __construct(
        protected ProjectTeamService $teamService
    ) {}

    public function searchStudents(Request $request): JsonResponse
    {
        $term = trim((string) $request->input('q', ''));
        $ids = collect($request->input('ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values();

        $query = User::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'student'));

        if ($ids->isNotEmpty()) {
            $query->whereIn('id', $ids);
        } elseif (mb_strlen($term) >= 2) {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('name_ar', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        } else {
            return response()->json(['results' => []]);
        }

        $results = $query->orderBy('name')->limit(50)->get(['id', 'name', 'name_ar', 'email'])
            ->map(fn (User $student) => [
                'id' => $student->id,
                'text' => $this->formatStudentLabel($student),
            ]);

        return response()->json(['results' => $results]);
    }

    public function store(Request $request, string $challengeId)
    {
        $challenge = ProjectChallenge::findOrFail($challengeId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'leader_id' => 'required|exists:users,id',
            'status' => 'nullable|in:pending,active,disqualified,completed',
        ]);

        try {
            $team = $this->teamService->createTeamAsAdmin($challenge, $validated, auth()->user());
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.project-challenges.teams.show', [$challenge->id, $team->id])
            ->with('success', 'تم إنشاء الفريق بنجاح');
    }

    public function show(string $challengeId, string $teamId)
    {
        $challenge = ProjectChallenge::with('stages')->findOrFail($challengeId);
        $team = ProjectTeam::with(['leader', 'activeMembers.user', 'submissions.stage'])
            ->where('project_challenge_id', $challenge->id)
            ->findOrFail($teamId);

        $submissionsByStage = $team->submissions->keyBy('project_stage_id');
        $teamRoles = config('project_challenges.team_roles', []);
        $stages = $challenge->stages->sortBy('sort_order');

        return view('admin.pages.project-challenges.team-manage', compact(
            'challenge',
            'team',
            'submissionsByStage',
            'teamRoles',
            'stages'
        ));
    }

    public function update(Request $request, string $challengeId, string $teamId)
    {
        $team = ProjectTeam::with('challenge')
            ->where('project_challenge_id', $challengeId)
            ->findOrFail($teamId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'leader_id' => 'nullable|exists:users,id',
            'status' => 'required|in:pending,active,disqualified,completed',
        ]);

        try {
            $this->teamService->updateTeamAsAdmin($team, $validated, auth()->user());
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تحديث الفريق بنجاح');
    }

    public function addMember(Request $request, string $challengeId, string $teamId)
    {
        $team = ProjectTeam::with('challenge')
            ->where('project_challenge_id', $challengeId)
            ->findOrFail($teamId);

        $validRoles = array_keys(config('project_challenges.team_roles', []));

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:' . implode(',', $validRoles),
        ]);

        $user = User::findOrFail($validated['user_id']);

        try {
            $this->teamService->addMemberAsAdmin($team, $user, $validated['role'], auth()->user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تمت إضافة العضو بنجاح');
    }

    public function removeMember(string $challengeId, string $teamId, string $userId)
    {
        $team = ProjectTeam::where('project_challenge_id', $challengeId)->findOrFail($teamId);
        $user = User::findOrFail($userId);

        try {
            $this->teamService->removeMember($team, auth()->user(), $user);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تمت إزالة العضو');
    }

    public function updateMemberRole(Request $request, string $challengeId, string $teamId, string $userId)
    {
        $team = ProjectTeam::where('project_challenge_id', $challengeId)->findOrFail($teamId);
        $member = ProjectTeamMember::query()
            ->where('project_team_id', $team->id)
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->firstOrFail();

        $validRoles = array_keys(config('project_challenges.team_roles', []));

        $validated = $request->validate([
            'role' => 'required|in:' . implode(',', $validRoles),
        ]);

        try {
            $this->teamService->changeMemberRole($team, $member, $validated['role'], auth()->user());
        } catch (\RuntimeException|\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تحديث دور العضو');
    }

    public function unlockStage(string $challengeId, string $teamId, string $stageId)
    {
        $team = ProjectTeam::where('project_challenge_id', $challengeId)->findOrFail($teamId);
        $stage = ProjectStage::where('project_challenge_id', $challengeId)->findOrFail($stageId);

        try {
            $this->teamService->unlockStageForTeam($team, $stage, auth()->user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم فتح المرحلة لهذا الفريق');
    }

    protected function formatStudentLabel(User $student): string
    {
        $name = $student->name_ar ?: $student->name;

        return trim("{$name} ({$student->email})");
    }
}
