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

        $challenges = $query->orderByDesc('created_at')->paginate(12);

        $userTeamIds = ProjectTeamMember::query()
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->pluck('project_team_id');

        $userTeams = ProjectTeam::with('challenge')
            ->whereIn('id', $userTeamIds)
            ->get()
            ->keyBy('project_challenge_id');

        return view('student.pages.project-challenges.index', compact('challenges', 'userTeams'));
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
