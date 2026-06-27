<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectChallenge\ProjectChallenge;
use App\Models\ProjectChallenge\ProjectSkill;
use App\Models\ProjectChallenge\ProjectStage;
use App\Models\ProjectChallenge\ProjectTeam;
use App\Models\ProjectChallenge\ProjectTeamJoinRequest;
use App\Models\ProjectChallenge\ProjectTechnology;
use App\Services\ProjectChallenge\ProjectTeamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectChallengeController extends Controller
{
    public function __construct(
        protected ProjectTeamService $teamService
    ) {}

    public function index(Request $request)
    {
        $query = ProjectChallenge::with(['creator', 'stages'])
            ->withCount('teams')
            ->orderByDesc('created_at');

        $statusFilter = $request->get('status');
        if (in_array($statusFilter, ['published', 'draft', 'archived', 'closed'], true)) {
            $query->where('status', $statusFilter);
        }

        $challenges = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => ProjectChallenge::count(),
            'published' => ProjectChallenge::where('status', 'published')->count(),
            'draft' => ProjectChallenge::where('status', 'draft')->count(),
            'teams' => ProjectTeam::count(),
        ];

        return view('admin.pages.project-challenges.index', compact('challenges', 'stats', 'statusFilter'));
    }

    public function create()
    {
        $skills = ProjectSkill::orderBy('name')->get();
        $technologies = ProjectTechnology::orderBy('name')->get();

        return view('admin.pages.project-challenges.create', compact('skills', 'technologies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'description' => 'nullable|string',
            'difficulty' => 'required|in:easy,medium,hard,expert',
            'project_type' => 'required|in:team_project,open_challenge,hackathon,capstone',
            'points_total' => 'nullable|integer|min:0',
            'expected_duration' => 'nullable|string|max:100',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'max_teams' => 'nullable|integer|min:1',
            'min_members' => 'required|integer|min:1',
            'max_members' => 'required|integer|min:1|gte:min_members',
            'allow_late_join' => 'boolean',
            'team_approval_mode' => 'required|in:auto,admin_approval,leader_approval,hybrid',
            'showcase_threshold' => 'nullable|integer|min:0|max:100',
            'language' => 'nullable|string|max:5',
            'skill_ids' => 'nullable|array',
            'skill_ids.*' => 'exists:project_skills,id',
            'technology_ids' => 'nullable|array',
            'technology_ids.*' => 'exists:project_technologies,id',
        ]);

        DB::beginTransaction();
        try {
            $challenge = ProjectChallenge::create([
                'title' => $validated['title'],
                'slug' => $this->uniqueSlug($validated['title']),
                'summary' => $validated['summary'] ?? null,
                'description' => $validated['description'] ?? null,
                'difficulty' => $validated['difficulty'],
                'project_type' => $validated['project_type'],
                'points_total' => $validated['points_total'] ?? 0,
                'expected_duration' => $validated['expected_duration'] ?? null,
                'starts_at' => $validated['starts_at'] ?? null,
                'ends_at' => $validated['ends_at'] ?? null,
                'max_teams' => $validated['max_teams'] ?? null,
                'min_members' => $validated['min_members'],
                'max_members' => $validated['max_members'],
                'allow_late_join' => $request->boolean('allow_late_join'),
                'team_approval_mode' => $validated['team_approval_mode'],
                'showcase_threshold' => $validated['showcase_threshold'] ?? 100,
                'language' => $validated['language'] ?? 'ar',
                'status' => 'draft',
                'is_featured' => $request->boolean('is_featured'),
                'created_by' => auth()->id(),
            ]);

            if (! empty($validated['skill_ids'])) {
                $challenge->skills()->sync($validated['skill_ids']);
            }

            if (! empty($validated['technology_ids'])) {
                $challenge->technologies()->sync($validated['technology_ids']);
            }

            DB::commit();

            return redirect()
                ->route('admin.project-challenges.manage-stages', $challenge->id)
                ->with('success', 'تم إنشاء تحدي المشروع بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $challenge = ProjectChallenge::with(['skills', 'technologies'])->findOrFail($id);
        $skills = ProjectSkill::orderBy('name')->get();
        $technologies = ProjectTechnology::orderBy('name')->get();

        return view('admin.pages.project-challenges.edit', compact('challenge', 'skills', 'technologies'));
    }

    public function update(Request $request, string $id)
    {
        $challenge = ProjectChallenge::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'description' => 'nullable|string',
            'difficulty' => 'required|in:easy,medium,hard,expert',
            'project_type' => 'required|in:team_project,open_challenge,hackathon,capstone',
            'points_total' => 'nullable|integer|min:0',
            'expected_duration' => 'nullable|string|max:100',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'max_teams' => 'nullable|integer|min:1',
            'min_members' => 'required|integer|min:1',
            'max_members' => 'required|integer|min:1|gte:min_members',
            'allow_late_join' => 'boolean',
            'team_approval_mode' => 'required|in:auto,admin_approval,leader_approval,hybrid',
            'showcase_threshold' => 'nullable|integer|min:0|max:100',
            'language' => 'nullable|string|max:5',
            'skill_ids' => 'nullable|array',
            'skill_ids.*' => 'exists:project_skills,id',
            'technology_ids' => 'nullable|array',
            'technology_ids.*' => 'exists:project_technologies,id',
        ]);

        DB::beginTransaction();
        try {
            $slug = $challenge->title !== $validated['title']
                ? $this->uniqueSlug($validated['title'], $challenge->id)
                : $challenge->slug;

            $challenge->update([
                'title' => $validated['title'],
                'slug' => $slug,
                'summary' => $validated['summary'] ?? null,
                'description' => $validated['description'] ?? null,
                'difficulty' => $validated['difficulty'],
                'project_type' => $validated['project_type'],
                'points_total' => $validated['points_total'] ?? 0,
                'expected_duration' => $validated['expected_duration'] ?? null,
                'starts_at' => $validated['starts_at'] ?? null,
                'ends_at' => $validated['ends_at'] ?? null,
                'max_teams' => $validated['max_teams'] ?? null,
                'min_members' => $validated['min_members'],
                'max_members' => $validated['max_members'],
                'allow_late_join' => $request->boolean('allow_late_join'),
                'team_approval_mode' => $validated['team_approval_mode'],
                'showcase_threshold' => $validated['showcase_threshold'] ?? 100,
                'language' => $validated['language'] ?? 'ar',
                'is_featured' => $request->boolean('is_featured'),
                'updated_by' => auth()->id(),
            ]);

            $challenge->skills()->sync($validated['skill_ids'] ?? []);
            $challenge->technologies()->sync($validated['technology_ids'] ?? []);

            DB::commit();

            return redirect()
                ->route('admin.project-challenges.edit', $challenge->id)
                ->with('success', 'تم تحديث تحدي المشروع بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $challenge = ProjectChallenge::findOrFail($id);
        $challenge->delete();

        return redirect()
            ->route('admin.project-challenges.index')
            ->with('success', 'تم حذف تحدي المشروع');
    }

    public function publish(string $id)
    {
        $challenge = ProjectChallenge::with('stages')->findOrFail($id);

        if ($challenge->stages->isEmpty()) {
            return back()->with('error', 'يجب إضافة مرحلة واحدة على الأقل قبل النشر');
        }

        DB::transaction(function () use ($challenge) {
            $challenge->update(['status' => 'published']);

            $firstStage = $challenge->stages->sortBy('sort_order')->first();
            if ($firstStage && $firstStage->isLocked()) {
                $firstStage->update(['status' => 'open']);
            }
        });

        return back()->with('success', 'تم نشر تحدي المشروع');
    }

    public function manageStages(string $id)
    {
        $challenge = ProjectChallenge::with('stages')->findOrFail($id);

        return view('admin.pages.project-challenges.stages', compact('challenge'));
    }

    public function updateStages(Request $request, string $id)
    {
        $challenge = ProjectChallenge::findOrFail($id);

        $validated = $request->validate([
            'stages' => 'required|array|min:1',
            'stages.*.title' => 'required|string|max:255',
            'stages.*.description' => 'nullable|string',
            'stages.*.sort_order' => 'required|integer|min:0',
            'stages.*.duration_days' => 'nullable|integer|min:0',
            'stages.*.due_at' => 'nullable|date',
            'stages.*.max_score' => 'required|numeric|min:0',
            'stages.*.weight' => 'required|numeric|min:0',
            'stages.*.is_optional' => 'boolean',
            'stages.*.unlock_after_previous' => 'boolean',
            'stages.*.allowed_link_types' => 'nullable|array',
            'stages.*.status' => 'nullable|in:locked,open,closed',
        ]);

        DB::transaction(function () use ($challenge, $validated, $request) {
            $existingIds = [];

            foreach ($validated['stages'] as $index => $stageData) {
                $stageId = $stageData['id'] ?? null;

                $attributes = [
                    'title' => $stageData['title'],
                    'description' => $stageData['description'] ?? null,
                    'sort_order' => $stageData['sort_order'],
                    'duration_days' => $stageData['duration_days'] ?? null,
                    'due_at' => $stageData['due_at'] ?? null,
                    'max_score' => $stageData['max_score'],
                    'weight' => $stageData['weight'],
                    'is_optional' => $request->boolean("stages.{$index}.is_optional"),
                    'unlock_after_previous' => $request->boolean("stages.{$index}.unlock_after_previous", true),
                    'allowed_link_types' => $stageData['allowed_link_types'] ?? null,
                    'status' => $stageData['status'] ?? ($index === 0 ? 'open' : 'locked'),
                ];

                if ($stageId) {
                    $stage = ProjectStage::where('project_challenge_id', $challenge->id)
                        ->findOrFail($stageId);
                    $stage->update($attributes);
                    $existingIds[] = $stage->id;
                } else {
                    $stage = ProjectStage::create(array_merge($attributes, [
                        'project_challenge_id' => $challenge->id,
                    ]));
                    $existingIds[] = $stage->id;
                }
            }

            ProjectStage::where('project_challenge_id', $challenge->id)
                ->whereNotIn('id', $existingIds)
                ->delete();
        });

        return redirect()
            ->route('admin.project-challenges.manage-stages', $challenge->id)
            ->with('success', 'تم تحديث المراحل بنجاح');
    }

    public function manageTeams(string $id)
    {
        $challenge = ProjectChallenge::findOrFail($id);

        $teams = ProjectTeam::with(['leader', 'activeMembers.user'])
            ->where('project_challenge_id', $challenge->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        $pendingJoinRequests = ProjectTeamJoinRequest::with(['user', 'team'])
            ->whereHas('team', fn ($q) => $q->where('project_challenge_id', $challenge->id))
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.pages.project-challenges.teams', compact(
            'challenge',
            'teams',
            'pendingJoinRequests'
        ));
    }

    public function approveJoinRequest(string $challengeId, string $requestId)
    {
        $joinRequest = ProjectTeamJoinRequest::with('team')
            ->whereHas('team', fn ($q) => $q->where('project_challenge_id', $challengeId))
            ->findOrFail($requestId);

        try {
            $this->teamService->approveJoinRequest($joinRequest, auth()->user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم قبول طلب الانضمام');
    }

    public function rejectJoinRequest(Request $request, string $challengeId, string $requestId)
    {
        $validated = $request->validate([
            'reject_reason' => 'nullable|string|max:1000',
        ]);

        $joinRequest = ProjectTeamJoinRequest::with('team')
            ->whereHas('team', fn ($q) => $q->where('project_challenge_id', $challengeId))
            ->findOrFail($requestId);

        try {
            $this->teamService->rejectJoinRequest(
                $joinRequest,
                auth()->user(),
                $validated['reject_reason'] ?? null
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم رفض طلب الانضمام');
    }

    public function activateTeam(string $challengeId, string $teamId)
    {
        $team = ProjectTeam::where('project_challenge_id', $challengeId)->findOrFail($teamId);

        try {
            $this->teamService->activateTeam($team, auth()->user());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم تفعيل الفريق');
    }

    protected function uniqueSlug(string $title, ?int $exceptId = null): string
    {
        $base = Str::slug($title) ?: 'project-challenge';
        $slug = $base;
        $counter = 1;

        while (
            ProjectChallenge::when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
