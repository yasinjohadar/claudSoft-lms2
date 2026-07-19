<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ProjectChallenge\ProjectChallenge;
use App\Models\ProjectChallenge\ProjectTeam;
use App\Models\ProjectChallenge\ProjectTeamMember;
use Illuminate\Http\Request;

class ProjectChallengeController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = ProjectChallenge::published()->open();

        $userTeamIds = ProjectTeamMember::query()
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->pluck('project_team_id');

        $userTeams = ProjectTeam::with('challenge')
            ->whereIn('id', $userTeamIds)
            ->get()
            ->keyBy('project_challenge_id');

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'featured' => (clone $baseQuery)->featured()->count(),
            'my_teams' => $userTeams->count(),
            'teams' => ProjectTeam::query()
                ->whereHas('challenge', fn ($q) => $q->published()->open())
                ->count(),
        ];
        $query = ProjectChallenge::published()
            ->open()
            ->with(['skills', 'technologies'])
            ->withCount('teams');

        if ($request->filled('difficulty')) {
            $query->ofDifficulty($request->difficulty);
        }

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('summary', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        $challenges = $query->orderByDesc('created_at')->paginate(12)->withQueryString();

        return view('student.pages.project-challenges.index', compact('challenges', 'userTeams', 'stats'));
    }

    public function show(string $id)
    {
        $challenge = ProjectChallenge::published()
            ->with(['stages', 'skills', 'technologies', 'teams' => fn ($q) => $q->active()])
            ->findOrFail($id);

        $userTeam = ProjectTeam::with(['members.user', 'leader'])
            ->where('project_challenge_id', $challenge->id)
            ->whereHas('activeMembers', fn ($q) => $q->where('user_id', auth()->id()))
            ->first();

        $openTeams = ProjectTeam::active()
            ->where('project_challenge_id', $challenge->id)
            ->with('leader')
            ->withCount('activeMembers')
            ->orderBy('name')
            ->get();

        return view('student.pages.project-challenges.show', compact('challenge', 'userTeam', 'openTeams'));
    }
}
