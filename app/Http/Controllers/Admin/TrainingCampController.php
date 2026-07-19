<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingCamp;
use App\Models\CampEnrollment;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseGroup;
use App\Models\User;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\TrainingCampEnrollmentService;
use App\Services\TrainingCampReceiptService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use InvalidArgumentException;

class TrainingCampController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $campsQuery = $this->buildCampsQuery($request);
        $stats = $this->buildCampsStats($campsQuery);
        $camps = (clone $campsQuery)->orderBy('start_date', 'desc')->paginate(20)->withQueryString();
        $categories = CourseCategory::active()->ordered()->get();

        if ($request->ajax()) {
            return response()->json([
                'table_html' => view('admin.pages.training-camps._camps_table', compact('camps'))->render(),
                'stats_html' => view('admin.pages.training-camps.partials.camps-stats', compact('stats'))->render(),
                'count' => $camps->total(),
            ]);
        }

        return view('admin.pages.training-camps.index', compact('camps', 'categories', 'stats'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<TrainingCamp>
     */
    private function buildCampsQuery(Request $request)
    {
        $query = TrainingCamp::with('category');

        if ($request->filled('status')) {
            if ($request->status === 'upcoming') {
                $query->upcoming();
            } elseif ($request->status === 'ongoing') {
                $query->ongoing();
            } elseif ($request->status === 'completed') {
                $query->completed();
            }
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('instructor_name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<TrainingCamp>  $query
     * @return array{total: int, ongoing: int, upcoming: int, active: int}
     */
    private function buildCampsStats($query): array
    {
        return [
            'total' => (clone $query)->count(),
            'ongoing' => (clone $query)->ongoing()->count(),
            'upcoming' => (clone $query)->upcoming()->count(),
            'active' => (clone $query)->where('is_active', true)->count(),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<CampEnrollment>
     */
    private function buildCampEnrollmentsQuery(Request $request)
    {
        $query = CampEnrollment::query()
            ->select([
                'id',
                'camp_id',
                'student_id',
                'status',
                'payment_status',
                'created_at',
            ])
            ->with([
                'camp:id,name,start_date',
                'student:id,name,email',
            ])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('camp_id')) {
            $query->where('camp_id', $request->camp_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<CampEnrollment>  $query
     * @return array{total: int, pending: int, approved: int, unpaid: int}
     */
    private function buildCampEnrollmentsStats($query): array
    {
        return [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'unpaid' => (clone $query)->where('payment_status', 'unpaid')->count(),
        ];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = CourseCategory::active()->ordered()->get();
        $courses = Course::query()->orderBy('title')->get(['id', 'title']);

        return view('admin.pages.training-camps.create', compact('categories', 'courses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(array_merge($this->campBaseRules(), [
            'name' => 'required|string|max:255|unique:training_camps,name',
            'slug' => 'nullable|string|max:255|unique:training_camps,slug',
            'start_date' => 'required|date|after_or_equal:today',
        ]), $this->campValidationMessages());

        $audienceRows = $this->normalizeAudienceRows($request);

        try {
            DB::beginTransaction();

            // Calculate duration in days
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $durationDays = $startDate->diffInDays($endDate) + 1;

            $data = [
                'name' => $request->name,
                'slug' => $request->slug ?: Str::slug($request->name),
                'description' => $request->description,
                'category_id' => $request->category_id,
                'price' => $request->price,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'duration_days' => $durationDays,
                'instructor_name' => $request->instructor_name,
                'location' => $request->location,
                'max_participants' => $request->max_participants,
                'current_participants' => 0,
                'is_active' => $request->boolean('is_active'),
                'is_featured' => $request->boolean('is_featured'),
                'require_payment_receipt' => $request->boolean('require_payment_receipt'),
                'order' => $request->order ?: 0,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . Str::slug($request->name) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('training-camps', $imageName, 'public');
                $data['image'] = $imagePath;
            }

            $camp = TrainingCamp::create($data);
            $this->syncAudienceTargets($camp, $audienceRows);

            DB::commit();

            return redirect()
                ->route('training-camps.index')
                ->with('success', 'تم إنشاء المعسكر التدريبي بنجاح');

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء المعسكر: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $camp = TrainingCamp::with(['category', 'targets.course', 'targets.group'])
            ->withCount('enrollments')
            ->findOrFail($id);

        // Get all students for dropdown
        $students = User::role('student')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        // Get all active course groups
        $courseGroups = CourseGroup::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.pages.training-camps.show', compact('camp', 'students', 'courseGroups'));
    }

    /**
     * JSON payload for selecting a camp in admin modals (e.g. group members table).
     */
    public function modalData(string $camp)
    {
        $campModel = TrainingCamp::with('category')
            ->withCount('enrollments')
            ->findOrFail($camp);

        return response()->json([
            'success' => true,
            'camp' => [
                'id' => $campModel->id,
                'name' => $campModel->name,
                'description' => $campModel->description,
                'price' => (float) $campModel->price,
                'start_date' => optional($campModel->start_date)->format('Y-m-d'),
                'end_date' => optional($campModel->end_date)->format('Y-m-d'),
                'duration_days' => $campModel->duration_days,
                'instructor_name' => $campModel->instructor_name,
                'location' => $campModel->location,
                'max_participants' => $campModel->max_participants,
                'current_participants' => $campModel->current_participants,
                'enrollments_count' => $campModel->enrollments_count,
                'category_name' => optional($campModel->category)->name,
                'is_active' => (bool) $campModel->is_active,
                'show_url' => route('training-camps.show', $campModel->id),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $camp = TrainingCamp::with(['targets.course', 'targets.group'])->findOrFail($id);
        $categories = CourseCategory::active()->ordered()->get();
        $courses = Course::query()->orderBy('title')->get(['id', 'title']);

        return view('admin.pages.training-camps.edit', compact('camp', 'categories', 'courses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $camp = TrainingCamp::findOrFail($id);

        $request->validate(array_merge($this->campBaseRules(), [
            'name' => 'required|string|max:255|unique:training_camps,name,' . $id,
            'slug' => 'nullable|string|max:255|unique:training_camps,slug,' . $id,
            'start_date' => 'required|date',
        ]), $this->campValidationMessages());

        $audienceRows = $this->normalizeAudienceRows($request);

        try {
            DB::beginTransaction();

            // Calculate duration in days
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $durationDays = $startDate->diffInDays($endDate) + 1;

            $data = [
                'name' => $request->name,
                'slug' => $request->slug ?: Str::slug($request->name),
                'description' => $request->description,
                'category_id' => $request->category_id,
                'price' => $request->price,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'duration_days' => $durationDays,
                'instructor_name' => $request->instructor_name,
                'location' => $request->location,
                'max_participants' => $request->max_participants,
                'is_active' => $request->boolean('is_active'),
                'is_featured' => $request->boolean('is_featured'),
                'require_payment_receipt' => $request->boolean('require_payment_receipt'),
                'order' => $request->order ?: 0,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($camp->image && cloud_file_exists($camp->image)) {
                    cloud_delete_file($camp->image);
                }

                $image = $request->file('image');
                $imageName = time() . '_' . Str::slug($request->name) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('training-camps', $imageName, 'public');
                $data['image'] = $imagePath;
            }

            $camp->update($data);
            $this->syncAudienceTargets($camp, $audienceRows);

            DB::commit();

            return redirect()
                ->route('training-camps.index')
                ->with('success', 'تم تحديث المعسكر التدريبي بنجاح');

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث المعسكر: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $camp = TrainingCamp::findOrFail($id);

            // Check if there are enrollments
            if ($camp->enrollments()->count() > 0) {
                $message = 'لا يمكن حذف المعسكر لأنه يحتوي على تسجيلات';
                
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message
                    ]);
                }
                
                return redirect()
                    ->back()
                    ->with('error', $message);
            }

            // Delete image if exists
            if ($camp->image && cloud_file_exists($camp->image)) {
                cloud_delete_file($camp->image);
            }

            $camp->delete();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم حذف المعسكر التدريبي بنجاح'
                ]);
            }

            return redirect()
                ->route('training-camps.index')
                ->with('success', 'تم حذف المعسكر التدريبي بنجاح');

        } catch (\Exception $e) {
            \Log::error('Training camp deletion error: ' . $e->getMessage(), [
                'exception' => $e,
                'camp_id' => $id
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء حذف المعسكر: ' . $e->getMessage()
                ]);
            }

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء حذف المعسكر: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(string $id)
    {
        try {
            $camp = TrainingCamp::findOrFail($id);
            $camp->is_active = !$camp->is_active;
            $camp->save();

            $status = $camp->is_active ? 'مفعّل' : 'معطّل';

            return redirect()
                ->back()
                ->with('success', "تم {$status} المعسكر بنجاح");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(string $id)
    {
        try {
            $camp = TrainingCamp::findOrFail($id);
            $camp->is_featured = !$camp->is_featured;
            $camp->save();

            $status = $camp->is_featured ? 'مميز' : 'غير مميز';

            return redirect()
                ->back()
                ->with('success', "المعسكر الآن {$status}");

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Display all enrollment requests.
     */
    public function enrollments(Request $request)
    {
        $enrollmentsQuery = $this->buildCampEnrollmentsQuery($request);
        $stats = $this->buildCampEnrollmentsStats($enrollmentsQuery);
        $enrollments = (clone $enrollmentsQuery)->paginate(20)->withQueryString();
        $camps = TrainingCamp::active()->orderBy('name')->get(['id', 'name']);

        if ($request->ajax()) {
            return response()->json([
                'table_html' => view('admin.pages.training-camps.partials.enrollments-table', [
                    'enrollments' => $enrollments,
                ])->render(),
                'stats_html' => view('admin.pages.training-camps.partials.enrollments-stats', compact('stats'))->render(),
                'count' => $enrollments->total(),
            ]);
        }

        return view('admin.pages.training-camps.enrollments', compact('enrollments', 'camps', 'stats'));
    }

    /**
     * Approve enrollment (old route - single id parameter).
     */
    public function approveEnrollmentOld(string $id)
    {
        try {
            $enrollment = CampEnrollment::findOrFail($id);
            $oldStatus = $enrollment->status;
            $enrollment->update([
                'status' => 'approved',
                'payment_status' => $enrollment->hasReceipt() ? 'paid' : $enrollment->payment_status,
            ]);

            // Update participants count
            if ($oldStatus !== 'approved') {
                $enrollment->camp->increment('current_participants');
            }

            return redirect()
                ->back()
                ->with('success', 'تمت الموافقة على الطلب بنجاح');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Approve enrollment.
     */
    public function approveEnrollment(Request $request, string $campId, string $enrollmentId)
    {
        try {
            DB::beginTransaction();

            $camp = TrainingCamp::findOrFail($campId);
            $enrollment = CampEnrollment::where('camp_id', $campId)
                ->findOrFail($enrollmentId);

            $oldStatus = $enrollment->status;
            $updates = ['status' => 'approved'];

            if ($enrollment->hasReceipt() && $enrollment->payment_status !== 'paid') {
                $updates['payment_status'] = 'paid';
            }

            $enrollment->update($updates);

            // Update participants count
            if ($oldStatus !== 'approved') {
                $camp->increment('current_participants');
            }

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تمت الموافقة على الطلب بنجاح',
                    'enrollment' => $enrollment->fresh(['student']),
                    'camp' => $camp->fresh()
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'تمت الموافقة على الطلب بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Reject enrollment (old route - single id parameter).
     */
    public function rejectEnrollmentOld(Request $request, string $id)
    {
        try {
            DB::beginTransaction();

            $enrollment = CampEnrollment::findOrFail($id);
            $oldStatus = $enrollment->status;

            // Update status and add rejection notes
            $enrollment->update([
                'status' => 'rejected',
                'notes' => $request->notes
            ]);

            // Decrement current participants if it was approved
            if ($oldStatus === 'approved') {
                $enrollment->camp->decrement('current_participants');
            }

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'تم رفض الطلب');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Reject enrollment.
     */
    public function rejectEnrollment(Request $request, string $campId, string $enrollmentId)
    {
        try {
            DB::beginTransaction();

            $camp = TrainingCamp::findOrFail($campId);
            $enrollment = CampEnrollment::where('camp_id', $campId)
                ->findOrFail($enrollmentId);

            $oldStatus = $enrollment->status;

            // Update status and add rejection notes
            $enrollment->update([
                'status' => 'rejected',
                'notes' => $request->input('notes', $enrollment->notes)
            ]);

            // Decrement current participants if it was approved
            if ($oldStatus === 'approved') {
                $camp->decrement('current_participants');
            }

            DB::commit();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم رفض الطلب',
                    'enrollment' => $enrollment->fresh(['student']),
                    'camp' => $camp->fresh()
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'تم رفض الطلب');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Update enrollment status (old route - single id parameter).
     */
    public function updateEnrollmentStatusOld(Request $request, string $id)
    {
        try {
            DB::beginTransaction();

            $enrollment = CampEnrollment::findOrFail($id);
            $newStatus = $request->input('status');

            // Validate status
            $validStatuses = ['pending', 'approved', 'rejected', 'cancelled'];
            if (!in_array($newStatus, $validStatuses)) {
                return redirect()
                    ->back()
                    ->with('error', 'حالة غير صحيحة');
            }

            $oldStatus = $enrollment->status;

            // Update status
            $enrollment->update([
                'status' => $newStatus,
                'notes' => $request->input('notes', $enrollment->notes)
            ]);

            // Handle participants count
            if ($oldStatus === 'approved' && $newStatus !== 'approved') {
                // If was approved and now changed, decrement
                $enrollment->camp->decrement('current_participants');
            } elseif ($oldStatus !== 'approved' && $newStatus === 'approved') {
                // If now approved, increment
                $enrollment->camp->increment('current_participants');
            }

            DB::commit();

            $statusLabels = [
                'pending' => 'قيد الانتظار',
                'approved' => 'مقبول',
                'rejected' => 'مرفوض',
                'cancelled' => 'ملغي'
            ];

            return redirect()
                ->back()
                ->with('success', 'تم تغيير الحالة إلى: ' . ($statusLabels[$newStatus] ?? $newStatus));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Update enrollment status.
     */
    public function updateEnrollmentStatus(Request $request, string $campId, string $enrollmentId)
    {
        try {
            DB::beginTransaction();

            $camp = TrainingCamp::findOrFail($campId);
            $enrollment = CampEnrollment::where('camp_id', $campId)
                ->findOrFail($enrollmentId);
            
            $newStatus = $request->input('status');

            // Validate status
            $validStatuses = ['pending', 'approved', 'rejected', 'cancelled'];
            if (!in_array($newStatus, $validStatuses)) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'حالة غير صحيحة'
                    ], 422);
                }
                return redirect()
                    ->back()
                    ->with('error', 'حالة غير صحيحة');
            }

            $oldStatus = $enrollment->status;

            // Update status
            $enrollment->update([
                'status' => $newStatus,
                'notes' => $request->input('notes', $enrollment->notes)
            ]);

            // Handle participants count
            if ($oldStatus === 'approved' && $newStatus !== 'approved') {
                // If was approved and now changed, decrement
                $camp->decrement('current_participants');
            } elseif ($oldStatus !== 'approved' && $newStatus === 'approved') {
                // If now approved, increment
                $camp->increment('current_participants');
            }

            DB::commit();

            $statusLabels = [
                'pending' => 'قيد الانتظار',
                'approved' => 'مقبول',
                'rejected' => 'مرفوض',
                'cancelled' => 'ملغي'
            ];

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تغيير الحالة إلى: ' . ($statusLabels[$newStatus] ?? $newStatus),
                    'enrollment' => $enrollment->fresh(['student']),
                    'camp' => $camp->fresh()
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'تم تغيير الحالة إلى: ' . ($statusLabels[$newStatus] ?? $newStatus));

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Update enrollment payment status.
     */
    public function updateEnrollmentPaymentStatus(Request $request, string $campId, string $enrollmentId)
    {
        try {
            $camp = TrainingCamp::findOrFail($campId);
            $enrollment = CampEnrollment::where('camp_id', $campId)
                ->findOrFail($enrollmentId);

            $newStatus = $request->input('payment_status');
            $validStatuses = ['unpaid', 'paid', 'refunded'];

            if (! in_array($newStatus, $validStatuses, true)) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'حالة دفع غير صحيحة',
                    ], 422);
                }

                return redirect()->back()->with('error', 'حالة دفع غير صحيحة');
            }

            $enrollment->update(['payment_status' => $newStatus]);

            $labels = [
                'unpaid' => 'غير مدفوع',
                'paid' => 'مدفوع',
                'refunded' => 'مسترجع',
            ];

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تغيير حالة الدفع إلى: '.($labels[$newStatus] ?? $newStatus),
                    'enrollment' => $enrollment->fresh(['student']),
                    'camp' => $camp->fresh(),
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'تم تغيير حالة الدفع إلى: '.($labels[$newStatus] ?? $newStatus));
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ: '.$e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ: '.$e->getMessage());
        }
    }

    /**
     * Get camp enrollments with filters and pagination.
     */
    public function campEnrollments(Request $request, string $campId)
    {
        try {
            $camp = TrainingCamp::findOrFail($campId);

            $query = CampEnrollment::with('student')
                ->where('camp_id', $campId)
                ->orderBy('created_at', 'desc');

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by payment status
            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }

            // Search by student name or email
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('student', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $enrollments = $query->paginate(15)->withQueryString();

            return response()->json([
                'success' => true,
                'table_html' => view('admin.pages.training-camps.partials.camp-enrollments-table', [
                    'enrollments' => $enrollments,
                    'camp' => $camp,
                ])->render(),
                'count' => $enrollments->total(),
                'enrollments' => $enrollments,
                'camp' => $camp->fresh(['category'])->loadCount('enrollments'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new individual enrollment.
     */
    public function createIndividualEnrollment(string $campId)
    {
        $camp = TrainingCamp::with('category')->findOrFail($campId);
        $courseGroups = CourseGroup::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        
        return view('admin.pages.training-camps.enrollments.create-individual', compact('camp', 'courseGroups'));
    }

    /**
     * Show the form for creating a new bulk enrollment.
     */
    public function createBulkEnrollment(string $campId)
    {
        $camp = TrainingCamp::with('category')->findOrFail($campId);
        $courseGroups = CourseGroup::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        
        return view('admin.pages.training-camps.enrollments.create-bulk', compact('camp', 'courseGroups'));
    }

    /**
     * Store a new enrollment for a camp.
     */
    public function storeEnrollment(Request $request, string $campId, TrainingCampEnrollmentService $enrollmentService)
    {
        try {
            $camp = TrainingCamp::findOrFail($campId);

            $validated = $request->validate([
                'student_id' => 'required|exists:users,id',
                'status' => 'nullable|in:pending,approved,rejected,cancelled',
                'payment_status' => 'nullable|in:unpaid,paid,refunded',
                'notes' => 'nullable|string|max:1000',
            ], [
                'student_id.required' => 'الطالب مطلوب',
                'student_id.exists' => 'الطالب المحدد غير موجود',
            ]);

            $enrollmentService->enrollStudent(
                $camp,
                (int) $validated['student_id'],
                $validated['status'] ?? null,
                $validated['payment_status'] ?? null,
                $validated['notes'] ?? null
            );

            return redirect()->route('training-camps.show', $campId)
                ->with('success', 'تم إضافة العضو بنجاح');

        } catch (InvalidArgumentException $e) {
            return redirect()->route('training-camps.enrollments.create-individual', $campId)
                ->withErrors(['student_id' => $e->getMessage()])
                ->withInput();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('training-camps.enrollments.create-individual', $campId)
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->route('training-camps.enrollments.create-individual', $campId)
                ->withErrors(['error' => 'حدث خطأ: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show enrollment details.
     */
    public function showEnrollment(string $campId, string $enrollmentId)
    {
        try {
            $camp = TrainingCamp::findOrFail($campId);
            $enrollment = CampEnrollment::with(['student', 'invoiceItems.invoice'])
                ->where('camp_id', $campId)
                ->findOrFail($enrollmentId);

            return response()->json([
                'success' => true,
                'enrollment' => [
                    'id' => $enrollment->id,
                    'status' => $enrollment->status,
                    'status_label' => $enrollment->status_label,
                    'payment_status' => $enrollment->payment_status,
                    'payment_status_label' => $enrollment->payment_status_label,
                    'enrollment_date' => $enrollment->enrollment_date,
                    'notes' => $enrollment->notes,
                    'has_receipt' => $enrollment->hasReceipt(),
                    'receipt_url' => $enrollment->hasReceipt()
                        ? route('training-camps.enrollments.receipt', [$campId, $enrollment->id])
                        : null,
                    'student' => $enrollment->student ? [
                        'id' => $enrollment->student->id,
                        'name' => $enrollment->student->name,
                        'email' => $enrollment->student->email,
                    ] : null,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View/download enrollment payment receipt from cloud storage.
     */
    public function enrollmentReceipt(
        Request $request,
        string $campId,
        string $enrollmentId,
        TrainingCampReceiptService $receiptService
    ): Response {
        $enrollment = CampEnrollment::where('camp_id', $campId)
            ->findOrFail($enrollmentId);

        abort_unless($enrollment->hasReceipt(), 404);

        $receipt = $receiptService->retrieve(
            $enrollment->receipt_path,
            $enrollment->receipt_disk
        );

        abort_if($receipt === null, 404, 'تعذر العثور على إيصال الدفع.');

        $extension = strtolower(pathinfo($enrollment->receipt_path, PATHINFO_EXTENSION));
        $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'pdf'], true)
            ? $extension
            : 'bin';
        $filename = "camp-enrollment-receipt-{$enrollment->id}.{$extension}";
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return response($receipt['content'], 200, [
            'Content-Type' => $receipt['mime_type'] ?: 'application/octet-stream',
            'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Delete an enrollment.
     */
    public function destroyEnrollment(string $campId, string $enrollmentId, TrainingCampEnrollmentService $enrollmentService)
    {
        try {
            $camp = TrainingCamp::findOrFail($campId);
            $enrollment = CampEnrollment::where('camp_id', $campId)
                ->findOrFail($enrollmentId);

            $enrollmentService->removeEnrollment($enrollment);

            return response()->json([
                'success' => true,
                'message' => 'تم حذف العضو بنجاح',
                'camp' => $camp->fresh(),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search students by name, email, or phone.
     */
    public function searchStudents(Request $request, string $campId)
    {
        try {
            $camp = TrainingCamp::findOrFail($campId);
            $search = $request->input('q', '');

            if (strlen($search) < 2) {
                return response()->json([
                    'results' => []
                ]);
            }

            // Get enrolled student IDs to exclude
            $enrolledStudentIds = CampEnrollment::where('camp_id', $campId)
                ->pluck('student_id')
                ->toArray();

            $query = User::role('student')
                ->whereNotIn('id', $enrolledStudentIds)
                ->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('full_phone', 'like', "%{$search}%");
                })
                ->orderBy('name')
                ->limit(50)
                ->get(['id', 'name', 'email', 'phone', 'full_phone']);

            $results = $query->map(function($student) {
                return [
                    'id' => $student->id,
                    'text' => $student->name . ' (' . $student->email . ')',
                    'name' => $student->name,
                    'email' => $student->email,
                    'phone' => $student->phone ?? $student->full_phone ?? '-'
                ];
            });

            return response()->json([
                'results' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'results' => [],
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get students in a specific group.
     */
    public function getGroupStudents(Request $request, string $campId, string $groupId)
    {
        try {
            $camp = TrainingCamp::findOrFail($campId);
            $group = CourseGroup::findOrFail($groupId);

            // Get enrolled student IDs to exclude
            $enrolledStudentIds = CampEnrollment::where('camp_id', $campId)
                ->pluck('student_id')
                ->toArray();

            // Get students in the group
            $students = $group->students()
                ->whereNotIn('users.id', $enrolledStudentIds)
                ->orderBy('users.name')
                ->get(['users.id', 'users.name', 'users.email', 'users.phone', 'users.full_phone']);

            return response()->json([
                'success' => true,
                'students' => $students->map(function($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->name,
                        'email' => $student->email,
                        'phone' => $student->phone ?? $student->full_phone ?? '-'
                    ];
                }),
                'group' => [
                    'id' => $group->id,
                    'name' => $group->name
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk store enrollments for multiple students.
     */
    public function bulkStoreEnrollments(Request $request, string $campId)
    {
        try {
            $camp = TrainingCamp::findOrFail($campId);

            $validated = $request->validate([
                'student_ids' => 'required|array|min:1',
                'student_ids.*' => 'required|exists:users,id',
                'status' => 'nullable|in:pending,approved,rejected,cancelled',
                'payment_status' => 'nullable|in:unpaid,paid,refunded',
                'notes' => 'nullable|string|max:1000',
            ], [
                'student_ids.required' => 'يجب اختيار طالب واحد على الأقل',
                'student_ids.array' => 'يجب اختيار طلاب',
                'student_ids.min' => 'يجب اختيار طالب واحد على الأقل',
            ]);

            // Convert student_ids to integers to ensure type consistency
            $studentIds = array_map('intval', $validated['student_ids']);

            // Get enrolled student IDs (also as integers)
            $enrolledStudentIds = CampEnrollment::where('camp_id', $campId)
                ->whereIn('student_id', $studentIds)
                ->pluck('student_id')
                ->map(function($id) {
                    return (int) $id;
                })
                ->toArray();

            // Filter out already enrolled students
            $newStudentIds = array_diff($studentIds, $enrolledStudentIds);

            if (empty($newStudentIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'جميع الطلاب المحددين مسجلين بالفعل في هذا المعسكر'
                ], 422);
            }

            DB::beginTransaction();

            $status = $validated['status'] ?? 'pending';
            $paymentStatus = $validated['payment_status'] ?? 'unpaid';
            $notes = $validated['notes'] ?? null;

            $enrollments = [];
            $actuallyAddedCount = 0;

            foreach ($newStudentIds as $studentId) {
                // Use firstOrCreate to avoid duplicate entries even if race condition occurs
                $enrollment = CampEnrollment::firstOrCreate(
                    [
                        'camp_id' => $campId,
                        'student_id' => (int) $studentId,
                    ],
                    [
                        'status' => $status,
                        'payment_status' => $paymentStatus,
                        'notes' => $notes,
                        'enrollment_date' => now(),
                    ]
                );

                // Count only newly created enrollments
                if ($enrollment->wasRecentlyCreated) {
                    $actuallyAddedCount++;
                    $enrollments[] = $enrollment;

                    // Create invoice for the camp enrollment
                    $invoice = Invoice::create([
                        'invoice_number' => Invoice::generateInvoiceNumber(),
                        'student_id' => (int) $studentId,
                        'total_amount' => $camp->price,
                        'paid_amount' => 0,
                        'remaining_amount' => $camp->price,
                        'tax_amount' => 0,
                        'discount_amount' => 0,
                        'status' => 'issued',
                        'issue_date' => now(),
                        'due_date' => $camp->start_date,
                        'notes' => 'فاتورة التسجيل في معسكر: ' . $camp->name,
                        'created_by' => auth()->id(),
                    ]);

                    // Create invoice item
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'description' => 'رسوم التسجيل في معسكر: ' . $camp->name,
                        'quantity' => 1,
                        'unit_price' => $camp->price,
                        'total_price' => $camp->price,
                        'camp_enrollment_id' => $enrollment->id,
                    ]);
                }
            }

            // Update current_participants only for actually added students and only if status is approved
            if ($status === 'approved' && $actuallyAddedCount > 0) {
                $camp->increment('current_participants', $actuallyAddedCount);
            }

            DB::commit();

            $skippedCount = count($enrolledStudentIds) + (count($newStudentIds) - $actuallyAddedCount);

            $message = 'تم إضافة ' . $actuallyAddedCount . ' طالب بنجاح';
            if ($skippedCount > 0) {
                $message .= ' (تم تخطي ' . $skippedCount . ' طالب مسجل مسبقاً)';
            }

            return redirect()->route('training-camps.show', $campId)
                ->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('training-camps.enrollments.create-bulk', $campId)
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            
            // Handle duplicate entry error specifically
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
                return redirect()->route('training-camps.enrollments.create-bulk', $campId)
                    ->withErrors(['student_ids' => 'بعض الطلاب المحددين مسجلين بالفعل في هذا المعسكر. يرجى تحديث الصفحة والمحاولة مرة أخرى.'])
                    ->withInput();
            }

            return redirect()->route('training-camps.enrollments.create-bulk', $campId)
                ->withErrors(['error' => 'حدث خطأ في قاعدة البيانات: ' . $e->getMessage()])
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('training-camps.enrollments.create-bulk', $campId)
                ->withErrors(['error' => 'حدث خطأ: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * @return array<string, string>
     */
    protected function campBaseRules(): array
    {
        return [
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id' => 'nullable|exists:course_categories,id',
            'price' => 'required|numeric|min:0',
            'end_date' => 'required|date|after:start_date',
            'instructor_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'order' => 'nullable|integer|min:0',
            'targets' => 'required|array|min:1',
            'targets.*.course_id' => 'required|exists:courses,id',
            'targets.*.group_ids' => 'required|array|min:1',
            'targets.*.group_ids.*' => 'integer|exists:course_groups,id',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function campValidationMessages(): array
    {
        return [
            'name.required' => 'اسم المعسكر مطلوب',
            'name.unique' => 'اسم المعسكر موجود بالفعل',
            'slug.unique' => 'المعرف موجود بالفعل',
            'image.image' => 'يجب أن يكون الملف صورة',
            'image.mimes' => 'نوع الصورة غير مدعوم',
            'image.max' => 'حجم الصورة يجب أن يكون أقل من 2 ميجابايت',
            'category_id.exists' => 'التصنيف المحدد غير موجود',
            'price.required' => 'السعر مطلوب',
            'price.numeric' => 'السعر يجب أن يكون رقماً',
            'start_date.required' => 'تاريخ البداية مطلوب',
            'start_date.after_or_equal' => 'تاريخ البداية يجب أن يكون اليوم أو بعده',
            'end_date.required' => 'تاريخ النهاية مطلوب',
            'end_date.after' => 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية',
            'max_participants.integer' => 'الحد الأقصى للمشاركين يجب أن يكون رقماً صحيحاً',
            'max_participants.min' => 'الحد الأقصى للمشاركين يجب أن يكون 1 على الأقل',
            'targets.required' => 'يجب تحديد جمهور مستهدف (كورس ومجموعة واحدة على الأقل)',
            'targets.min' => 'يجب تحديد جمهور مستهدف (كورس ومجموعة واحدة على الأقل)',
            'targets.*.course_id.required' => 'اختر كورساً لكل صف جمهور',
            'targets.*.group_ids.required' => 'اختر مجموعة واحدة على الأقل لكل كورس',
            'targets.*.group_ids.min' => 'اختر مجموعة واحدة على الأقل لكل كورس',
        ];
    }

    /**
     * @return array<int, array{course_id: int, group_ids: array<int, int>}>
     */
    protected function normalizeAudienceRows(Request $request): array
    {
        $raw = $request->input('targets', []);
        if (! is_array($raw)) {
            throw ValidationException::withMessages([
                'targets' => 'يجب تحديد جمهور مستهدف (كورس ومجموعة واحدة على الأقل)',
            ]);
        }

        $rows = [];
        $seenCourses = [];

        foreach ($raw as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $courseId = isset($row['course_id']) && $row['course_id'] !== ''
                ? (int) $row['course_id']
                : null;

            if (! $courseId) {
                continue;
            }

            if (isset($seenCourses[$courseId])) {
                throw ValidationException::withMessages([
                    "targets.{$index}.course_id" => 'لا تكرر نفس الكورس أكثر من مرة — اختر مجموعات متعددة داخل صف واحد.',
                ]);
            }
            $seenCourses[$courseId] = true;

            $groupIds = collect($row['group_ids'] ?? [])
                ->filter(fn ($id) => $id !== null && $id !== '')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            if ($groupIds === []) {
                throw ValidationException::withMessages([
                    "targets.{$index}.group_ids" => 'اختر مجموعة واحدة على الأقل لكل كورس.',
                ]);
            }

            foreach ($groupIds as $groupId) {
                $this->assertTargetGroupLinkedToCourse($groupId, $courseId);
            }

            $rows[] = [
                'course_id' => $courseId,
                'group_ids' => $groupIds,
            ];
        }

        if ($rows === []) {
            throw ValidationException::withMessages([
                'targets' => 'يجب تحديد جمهور مستهدف (كورس ومجموعة واحدة على الأقل)',
            ]);
        }

        return $rows;
    }

    /**
     * @param  array<int, array{course_id: int, group_ids: array<int, int>}>  $rows
     */
    protected function syncAudienceTargets(TrainingCamp $camp, array $rows): void
    {
        $camp->targets()->delete();

        foreach ($rows as $row) {
            foreach ($row['group_ids'] as $groupId) {
                $camp->targets()->create([
                    'course_id' => $row['course_id'],
                    'group_id' => $groupId,
                ]);
            }
        }
    }

    protected function assertTargetGroupLinkedToCourse(int $groupId, int $courseId): void
    {
        $isLinked = DB::table('course_group_courses')
            ->where('course_id', $courseId)
            ->where('group_id', $groupId)
            ->exists();

        if (! $isLinked) {
            throw ValidationException::withMessages([
                'targets' => 'إحدى المجموعات غير مرتبطة بالكورس المحدد.',
            ]);
        }
    }
}
