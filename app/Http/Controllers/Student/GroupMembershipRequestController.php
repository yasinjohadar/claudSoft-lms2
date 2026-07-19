<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\GroupMembershipRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupMembershipRequestController extends Controller
{
    /**
     * Display a listing of available groups.
     */
    public function index(Request $request)
    {
        $student = Auth::user();

        $query = CourseGroup::with([
                'courses', 
                'members', 
                'creator', 
                'visibilityRequirements.requiredGroup.members'
            ])
            ->withCount('members')
            ->where('is_visible', true)
            ->where('is_active', true)
            ->where('allow_membership_requests', true)
            ->where('is_visible_for_students', true)
            ->where('is_camp', false);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by course
        if ($request->filled('course_id')) {
            $query->whereHas('courses', function($q) use ($request) {
                $q->where('courses.id', $request->course_id);
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $groups = $query->get();

        // Filter groups based on visibility requirements
        $visibleGroups = $groups->filter(function ($group) use ($student) {
            return $group->isVisibleForStudent($student);
        });

        // Check request status for each visible group
        foreach ($visibleGroups as $group) {
            $group->can_request = $group->canRequestMembership($student);
            $group->has_pending_request = $group->membershipRequests()
                ->where('student_id', $student->id)
                ->where('status', 'pending')
                ->exists();
        }

        // Paginate filtered results
        $perPage = $request->get('per_page', 15);
        $currentPage = $request->get('page', 1);
        $items = $visibleGroups->values();
        $total = $items->count();
        $items = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $filteredGroups = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $stats = [
            'total' => $total,
            'available' => $visibleGroups->filter(function ($group) {
                $isFull = $group->max_members && $group->members_count >= $group->max_members;

                return ! $isFull && ! $group->has_pending_request;
            })->count(),
            'pending' => GroupMembershipRequest::where('student_id', $student->id)
                ->where('status', 'pending')
                ->count(),
            'filtered' => $total,
        ];

        $courses = Course::whereHas('groups', function ($q) {
            $q->where('course_groups.is_visible', true)
                ->where('course_groups.is_active', true)
                ->where('course_groups.allow_membership_requests', true)
                ->where('course_groups.is_visible_for_students', true)
                ->where('course_groups.is_camp', false);
        })
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('student.groups.index', [
            'groups' => $filteredGroups,
            'stats' => $stats,
            'courses' => $courses,
        ]);
    }

    /**
     * Display the specified group.
     */
    public function show($id)
    {
        $student = Auth::user();
        $group = CourseGroup::with(['courses', 'members.student', 'creator'])
            ->withCount('members')
            ->findOrFail($id);

        if ($group->is_camp) {
            return redirect()->route('student.training-camps.show', $group->id);
        }

        // Check if student can request membership
        $canRequest = $group->canRequestMembership($student);
        $hasPendingRequest = $group->membershipRequests()
            ->where('student_id', $student->id)
            ->where('status', 'pending')
            ->exists();

        return view('student.groups.show', compact('group', 'canRequest', 'hasPendingRequest'));
    }

    /**
     * Store a newly created membership request.
     */
    public function store(Request $request, $groupId)
    {
        $student = Auth::user();
        $group = CourseGroup::findOrFail($groupId);

        if ($group->is_camp) {
            return redirect()
                ->route('student.training-camps.show', $group->id)
                ->with('info', 'يرجى التسجيل من صفحة المعسكر');
        }

        // Validate request
        $validated = $request->validate([
            'terms_accepted' => 'required|boolean|accepted',
            'payment_date' => 'nullable|date|after_or_equal:today',
            'message' => 'required|string|min:10|max:1000',
        ], [
            'terms_accepted.required' => 'يجب الموافقة على شروط المعسكر',
            'terms_accepted.accepted' => 'يجب الموافقة على شروط المعسكر',
            'payment_date.date' => 'تاريخ تسديد الرسوم غير صحيح',
            'payment_date.after_or_equal' => 'تاريخ تسديد الرسوم يجب أن يكون اليوم أو بعده',
            'message.required' => 'يجب كتابة وسيلة الدفع وأي ملاحظات إضافية للإدارة.',
            'message.min' => 'يرجى كتابة رسالة أكثر تفصيلاً تتضمن وسيلة الدفع والملاحظات.',
            'message.max' => 'الرسالة يجب أن تكون أقل من 1000 حرف',
        ]);

        // Check if student can request membership
        if (!$group->canRequestMembership($student)) {
            return redirect()->back()
                ->with('error', 'لا يمكنك طلب الانضمام لهذه المجموعة');
        }

        // Check if there's already a pending request
        $existingRequest = $group->membershipRequests()
            ->where('student_id', $student->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return redirect()->back()
                ->with('error', 'لديك طلب انضمام قيد المراجعة لهذه المجموعة بالفعل');
        }

        // Create membership request
        GroupMembershipRequest::create([
            'group_id' => $group->id,
            'student_id' => $student->id,
            'status' => 'pending',
            'terms_accepted' => true,
            'payment_date' => $validated['payment_date'] ?? null,
            'message' => $validated['message'],
        ]);

        return redirect()->route('student.groups.show', $group->id)
            ->with('success', 'تم إرسال طلب الانضمام بنجاح. سيتم مراجعته من قبل الإدارة.');
    }

    /**
     * Display the student's membership requests.
     */
    public function myRequests(Request $request)
    {
        $student = Auth::user();

        $query = GroupMembershipRequest::with(['group.courses', 'group.creator'])
            ->where('student_id', $student->id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $requests = $query->paginate($request->get('per_page', 15));

        $stats = [
            'total' => GroupMembershipRequest::where('student_id', $student->id)->count(),
            'pending' => GroupMembershipRequest::where('student_id', $student->id)->where('status', 'pending')->count(),
            'approved' => GroupMembershipRequest::where('student_id', $student->id)->where('status', 'approved')->count(),
            'rejected' => GroupMembershipRequest::where('student_id', $student->id)->where('status', 'rejected')->count(),
            'filtered' => $requests->total(),
        ];

        return view('student.groups.my-requests', compact('requests', 'stats'));
    }
}
