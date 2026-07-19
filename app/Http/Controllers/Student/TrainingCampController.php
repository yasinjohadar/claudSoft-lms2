<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseGroup;
use App\Models\CourseGroupMember;
use App\Models\GroupMembershipRequest;
use App\Services\CourseGroupReceiptService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class TrainingCampController extends Controller
{
    /**
     * Available camp-style groups the student may see and join.
     * Approved camp memberships are excluded (they appear under «معسكراتي»).
     */
    public function index(Request $request)
    {
        $student = Auth::user();

        $approvedCampGroupIds = CourseGroupMember::query()
            ->where('student_id', $student->id)
            ->whereHas('group', fn ($q) => $q->where('is_camp', true))
            ->pluck('group_id');

        $query = CourseGroup::query()
            ->with(['visibilityRequirements.requiredGroup', 'courses'])
            ->withCount('members')
            ->where('is_camp', true)
            ->where('is_active', true)
            ->where('is_visible', true)
            ->where('is_visible_for_students', true)
            ->where('allow_membership_requests', true)
            ->whereNotIn('id', $approvedCampGroupIds);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'upcoming') {
                $query->whereNotNull('start_date')->where('start_date', '>', now());
            } elseif ($request->status === 'ongoing') {
                $query->where(function ($q) {
                    $q->whereNull('start_date')
                        ->orWhere('start_date', '<=', now());
                })->where(function ($q) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', now()->startOfDay());
                });
            }
        }

        $sortBy = $request->get('sort', 'start_date');
        $sortOrder = $request->get('order', 'asc');
        if (in_array($sortBy, ['start_date', 'end_date', 'price', 'name', 'created_at'], true)) {
            $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('start_date');
        }

        $visibleCamps = $query->get()->filter(
            fn (CourseGroup $group) => $group->isVisibleForStudent($student)
        )->values();

        $stats = [
            'total' => $visibleCamps->count(),
            'upcoming' => $visibleCamps->filter(fn (CourseGroup $g) => $g->isUpcoming())->count(),
            'ongoing' => $visibleCamps->filter(fn (CourseGroup $g) => $g->isOngoing() && ! $g->isUpcoming())->count(),
            'featured' => 0,
        ];

        $perPage = 12;
        $page = (int) $request->get('page', 1);
        $camps = new LengthAwarePaginator(
            $visibleCamps->forPage($page, $perPage)->values(),
            $visibleCamps->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Only pending requests remain on this list (approved members are excluded above)
        $userEnrollments = GroupMembershipRequest::query()
            ->where('student_id', $student->id)
            ->where('status', 'pending')
            ->whereIn('group_id', $visibleCamps->pluck('id'))
            ->pluck('group_id')
            ->all();

        $categories = collect();

        return view('student.pages.training-camps.index', compact('camps', 'categories', 'userEnrollments', 'stats'));
    }

    /**
     * Camp-style group details.
     */
    public function show(string $slug)
    {
        $student = Auth::user();
        $trainingCamp = CourseGroup::query()
            ->with(['courses', 'visibilityRequirements.requiredGroup'])
            ->withCount('members')
            ->where('is_camp', true)
            ->where('is_active', true)
            ->findOrFail($slug);

        $isMember = $trainingCamp->hasMember($student);
        $pendingRequest = GroupMembershipRequest::query()
            ->where('group_id', $trainingCamp->id)
            ->where('student_id', $student->id)
            ->where('status', 'pending')
            ->first();

        if (! $isMember && ! $pendingRequest && ! $trainingCamp->isVisibleForStudent($student)) {
            abort(404);
        }

        $isEnrolled = $isMember || $pendingRequest !== null;
        $enrollment = null;

        if ($isMember) {
            $member = CourseGroupMember::query()
                ->where('group_id', $trainingCamp->id)
                ->where('student_id', $student->id)
                ->first();

            $enrollment = $this->makeEnrollmentViewModel(
                status: 'approved',
                paymentStatus: $member?->payment_status ?? 'unpaid',
                camp: $trainingCamp,
                createdAt: $member?->joined_at ?? $member?->created_at ?? now(),
                cancelId: null,
                notes: null,
            );
        } elseif ($pendingRequest) {
            $enrollment = $this->makeEnrollmentViewModel(
                status: 'pending',
                paymentStatus: 'unpaid',
                camp: $trainingCamp,
                createdAt: $pendingRequest->created_at,
                cancelId: $pendingRequest->id,
                notes: $pendingRequest->message,
                hasReceipt: $pendingRequest->hasReceipt(),
            );
        }

        return view('student.pages.training-camps.show', compact('trainingCamp', 'isEnrolled', 'enrollment'));
    }

    /**
     * Submit a membership request for a camp-style group.
     */
    public function enroll(Request $request, string $id, CourseGroupReceiptService $receiptService)
    {
        $student = Auth::user();
        $camp = CourseGroup::query()
            ->where('is_camp', true)
            ->findOrFail($id);

        if (! $camp->is_active || ! $camp->allow_membership_requests) {
            return redirect()->back()->with('error', 'هذا المعسكر غير متاح حالياً');
        }

        if (! $camp->isVisibleForStudent($student)) {
            return redirect()->back()->with('error', 'هذا المعسكر غير متاح لك');
        }

        if ($camp->isFull()) {
            return redirect()->back()->with('error', 'المعسكر ممتلئ، لا توجد مقاعد متاحة');
        }

        if ($camp->hasMember($student)) {
            return redirect()->back()->with('error', 'أنت مسجل بالفعل في هذا المعسكر');
        }

        $existingRequest = GroupMembershipRequest::query()
            ->where('group_id', $camp->id)
            ->where('student_id', $student->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return redirect()->back()->with('error', 'لديك طلب تسجيل قيد المراجعة لهذا المعسكر');
        }

        $requireReceipt = (bool) ($camp->require_payment_receipt ?? true);

        $rules = [
            'notes' => 'nullable|string|max:1000',
            'terms_accepted' => 'required|accepted',
        ];

        if ($requireReceipt) {
            $rules['receipt'] = 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120';
        }

        $validated = $request->validate($rules, [
            'terms_accepted.required' => 'يجب الموافقة على شروط المعسكر.',
            'terms_accepted.accepted' => 'يجب الموافقة على شروط المعسكر.',
            'receipt.required' => 'يرجى رفع إيصال الدفع المالي.',
            'receipt.mimes' => 'صيغة الإيصال يجب أن تكون صورة أو PDF.',
            'receipt.max' => 'حجم الإيصال يجب ألا يتجاوز 5 ميغابايت.',
        ]);

        try {
            $receiptPath = null;
            $receiptDisk = null;

            if ($requireReceipt && $request->hasFile('receipt')) {
                $receiptPath = $receiptService->store(
                    $request->file('receipt'),
                    (int) $camp->id,
                    (int) $student->id
                );
                $receiptDisk = CourseGroupReceiptService::DISK;
            }

            GroupMembershipRequest::create([
                'group_id' => $camp->id,
                'student_id' => $student->id,
                'status' => 'pending',
                'terms_accepted' => true,
                'payment_date' => null,
                'message' => $validated['notes'] ?: 'طلب تسجيل في معسكر: '.$camp->name,
                'receipt_path' => $receiptPath,
                'receipt_disk' => $receiptDisk,
            ]);

            return redirect()
                ->route('student.training-camps.my-enrollments')
                ->with('success', 'تم إرسال طلب التسجيل بنجاح وهو قيد المراجعة.');
        } catch (\RuntimeException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء التسجيل: '.$e->getMessage());
        }
    }

    /**
     * Student's camp-group memberships and pending requests.
     */
    public function myEnrollments(Request $request)
    {
        $studentId = Auth::id();

        $memberships = CourseGroupMember::query()
            ->with('group')
            ->where('student_id', $studentId)
            ->whereHas('group', fn ($q) => $q->where('is_camp', true))
            ->get()
            ->map(function (CourseGroupMember $member) {
                return $this->makeEnrollmentViewModel(
                    status: 'approved',
                    paymentStatus: $member->payment_status ?? 'unpaid',
                    camp: $member->group,
                    createdAt: $member->joined_at ?? $member->created_at,
                    cancelId: null,
                    notes: null,
                    key: 'm-'.$member->id,
                );
            });

        $requests = GroupMembershipRequest::query()
            ->with('group')
            ->where('student_id', $studentId)
            ->whereHas('group', fn ($q) => $q->where('is_camp', true))
            ->whereIn('status', ['pending', 'rejected'])
            ->get()
            ->reject(function (GroupMembershipRequest $req) use ($memberships) {
                // Hide pending/rejected if already a member of the same group
                return $memberships->contains(fn ($item) => (int) ($item->camp?->id) === (int) $req->group_id);
            })
            ->map(function (GroupMembershipRequest $req) {
                return $this->makeEnrollmentViewModel(
                    status: $req->status,
                    paymentStatus: 'unpaid',
                    camp: $req->group,
                    createdAt: $req->created_at,
                    cancelId: $req->status === 'pending' ? $req->id : null,
                    notes: $req->message,
                    key: 'r-'.$req->id,
                    hasReceipt: $req->hasReceipt(),
                );
            });

        $items = $memberships->concat($requests)->sortByDesc(fn ($item) => $item->created_at)->values();

        if ($request->filled('status')) {
            $items = $items->where('status', $request->status)->values();
        }

        $stats = [
            'total' => $memberships->count() + $requests->count(),
            'pending' => $requests->where('status', 'pending')->count(),
            'approved' => $memberships->count(),
            'unpaid' => $memberships->where('payment_status', 'unpaid')->count()
                + $requests->where('status', 'pending')->count(),
        ];

        $perPage = 10;
        $page = (int) $request->get('page', 1);
        $enrollments = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('student.pages.training-camps.my-enrollments', compact('enrollments', 'stats'));
    }

    /**
     * Cancel a pending camp-group membership request.
     */
    public function cancelEnrollment(string $id)
    {
        $request = GroupMembershipRequest::query()
            ->where('id', $id)
            ->where('student_id', Auth::id())
            ->whereHas('group', fn ($q) => $q->where('is_camp', true))
            ->firstOrFail();

        if ($request->status !== 'pending') {
            return redirect()->back()->with('error', 'لا يمكن إلغاء هذا الطلب');
        }

        $request->delete();

        return redirect()->back()->with('success', 'تم إلغاء طلب التسجيل بنجاح');
    }

    /**
     * Build a lightweight enrollment object for camp views.
     */
    private function makeEnrollmentViewModel(
        string $status,
        string $paymentStatus,
        ?CourseGroup $camp,
        $createdAt,
        ?int $cancelId,
        ?string $notes,
        ?string $key = null,
        bool $hasReceipt = false,
    ): object {
        $statusLabels = [
            'pending' => 'قيد المراجعة',
            'approved' => 'مقبول',
            'rejected' => 'مرفوض',
            'cancelled' => 'ملغي',
        ];

        $paymentLabels = [
            'unpaid' => 'غير مدفوع',
            'paid' => 'مدفوع',
            'refunded' => 'مسترجع',
        ];

        return (object) [
            'id' => $cancelId ?? $key ?? uniqid('camp-', true),
            'status' => $status,
            'status_label' => $statusLabels[$status] ?? $status,
            'payment_status' => $paymentStatus,
            'payment_status_label' => $paymentLabels[$paymentStatus] ?? $paymentStatus,
            'created_at' => $createdAt,
            'notes' => $notes,
            'camp' => $camp,
            'cancel_id' => $cancelId,
            'has_receipt' => $hasReceipt,
        ];
    }
}
