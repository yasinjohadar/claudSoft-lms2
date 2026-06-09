<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\GroupMembershipRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $student = $request->user();
        $groups = CourseGroup::with(['courses', 'creator'])
            ->withCount('members')
            ->where('is_visible', true)
            ->where('is_active', true)
            ->where('allow_membership_requests', true)
            ->where('is_visible_for_students', true)
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->orderByDesc('created_at')
            ->get()
            ->filter(fn ($g) => $g->isVisibleForStudent($student))
            ->values()
            ->map(function ($group) use ($student) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'members_count' => $group->members_count,
                    'courses' => $group->courses->map(fn ($c) => ['id' => $c->id, 'title' => $c->title]),
                    'can_request' => $group->canRequestMembership($student),
                    'has_pending_request' => $group->membershipRequests()
                        ->where('student_id', $student->id)->where('status', 'pending')->exists(),
                    'is_member' => $group->members()->where('student_id', $student->id)->exists(),
                ];
            });

        $myRequests = GroupMembershipRequest::where('student_id', $student->id)
            ->with('courseGroup')
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'group_id' => $r->course_group_id,
                'group_name' => $r->courseGroup?->name,
                'status' => $r->status,
                'created_at' => $r->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'success' => true,
            'data' => ['groups' => $groups, 'my_requests' => $myRequests],
        ]);
    }

    public function requestMembership(Request $request, CourseGroup $group): JsonResponse
    {
        $student = $request->user();
        if (!$group->canRequestMembership($student)) {
            return response()->json(['success' => false, 'message' => 'لا يمكنك طلب الانضمام لهذه المجموعة'], 422);
        }

        $validated = $request->validate(['message' => 'nullable|string|max:500']);
        $membershipRequest = GroupMembershipRequest::create([
            'course_group_id' => $group->id,
            'student_id' => $student->id,
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال طلب الانضمام',
            'data' => ['id' => $membershipRequest->id, 'status' => 'pending'],
        ]);
    }
}
