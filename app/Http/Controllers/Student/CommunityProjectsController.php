<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ProjectChallenge\ProjectShowcase;
use Illuminate\Http\Request;

class CommunityProjectsController extends Controller
{
    public function index(Request $request)
    {
        $query = ProjectShowcase::published()
            ->with(['team', 'challenge']);

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

        $showcases = $query->orderByDesc('published_at')->paginate(12);

        return view('student.pages.community-projects.index', compact('showcases'));
    }
}
