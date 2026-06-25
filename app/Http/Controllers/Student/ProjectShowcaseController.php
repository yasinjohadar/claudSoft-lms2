<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ProjectChallenge\ProjectComment;
use App\Models\ProjectChallenge\ProjectShowcase;
use App\Models\ProjectChallenge\ProjectTeam;
use App\Services\ProjectChallenge\ProjectCommentService;
use App\Services\ProjectChallenge\ProjectShowcaseService;
use Illuminate\Http\Request;

class ProjectShowcaseController extends Controller
{
    public function __construct(
        protected ProjectShowcaseService $showcaseService,
        protected ProjectCommentService $commentService
    ) {}

    public function show(string $slug)
    {
        $showcase = ProjectShowcase::published()
            ->where('slug', $slug)
            ->with([
                'team.activeMembers.user',
                'challenge',
                'comments' => fn ($q) => $q->visible()->with(['user', 'replies.user', 'likes']),
            ])
            ->firstOrFail();

        return view('student.pages.community-projects.show', compact('showcase'));
    }

    public function publishForm(string $teamId)
    {
        $team = ProjectTeam::with(['challenge', 'showcase'])->findOrFail($teamId);

        if (! $team->hasMember(auth()->id())) {
            abort(403);
        }

        $canPublish = $this->showcaseService->canPublish($team);

        return view('student.pages.project-challenges.publish-showcase', compact('team', 'canPublish'));
    }

    public function publish(Request $request, string $teamId)
    {
        $team = ProjectTeam::with('challenge')->findOrFail($teamId);

        if (! $team->hasMember(auth()->id())) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string|max:2000',
            'github_url' => 'nullable|url|max:2048',
            'demo_url' => 'nullable|url|max:2048',
            'video_url' => 'nullable|url|max:2048',
            'cover_image' => 'nullable|string|max:500',
            'screenshots' => 'nullable|array',
            'screenshots.*' => 'string|max:500',
        ]);

        try {
            $showcase = $this->showcaseService->publish($team, $validated, auth()->user());
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('student.community-projects.show', $showcase->slug)
            ->with('success', 'تم نشر العرض بنجاح');
    }

    public function storeComment(Request $request, string $slug)
    {
        $showcase = ProjectShowcase::published()->where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
            'parent_id' => 'nullable|exists:project_comments,id',
        ]);

        try {
            if (! empty($validated['parent_id'])) {
                $parent = ProjectComment::where('project_showcase_id', $showcase->id)
                    ->findOrFail($validated['parent_id']);
                $this->commentService->reply($parent, auth()->user(), $validated['body']);
            } else {
                $this->commentService->addComment($showcase, auth()->user(), $validated['body']);
            }
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'تم إضافة التعليق');
    }

    public function toggleCommentLike(Request $request, string $commentId)
    {
        $comment = ProjectComment::findOrFail($commentId);

        $result = $this->commentService->toggleLike($comment, auth()->user());

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return back();
    }
}
