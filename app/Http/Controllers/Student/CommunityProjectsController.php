<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ProjectChallenge\ProjectChallenge;
use App\Models\ProjectChallenge\ProjectShowcase;
use Illuminate\Http\Request;

class CommunityProjectsController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = ProjectShowcase::published();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'this_month' => (clone $baseQuery)
                ->whereNotNull('published_at')
                ->where('published_at', '>=', now()->startOfMonth())
                ->count(),
            'with_demo' => (clone $baseQuery)
                ->whereNotNull('demo_url')
                ->where('demo_url', '!=', '')
                ->count(),
            'challenges' => (int) ((clone $baseQuery)
                ->whereNotNull('project_challenge_id')
                ->selectRaw('COUNT(DISTINCT project_challenge_id) as aggregate')
                ->value('aggregate') ?? 0),
        ];

        $challenges = ProjectChallenge::query()
            ->published()
            ->whereHas('showcases', fn ($q) => $q->published())
            ->orderBy('title')
            ->get(['id', 'title']);

        $query = ProjectShowcase::published()
            ->with(['team', 'challenge'])
            ->withCount('allComments');

        if ($request->filled('challenge_id')) {
            $query->where('project_challenge_id', $request->challenge_id);
        }

        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('summary', 'like', "%{$term}%");
            });
        }

        $showcases = $query->orderByDesc('published_at')->paginate(12)->withQueryString();

        return view('student.pages.community-projects.index', compact('showcases', 'stats', 'challenges'));
    }
}
