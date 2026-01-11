<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingCamp;
use App\Models\CampEnrollment;
use App\Models\CourseCategory;
use App\Models\CourseGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TrainingCampController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TrainingCamp::with('category');

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'upcoming') {
                $query->upcoming();
            } elseif ($request->status === 'ongoing') {
                $query->ongoing();
            } elseif ($request->status === 'completed') {
                $query->completed();
            }
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('instructor_name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $camps = $query->orderBy('start_date', 'desc')->paginate(20);
        $categories = CourseCategory::active()->ordered()->get();

        return view('admin.pages.training-camps.index', compact('camps', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = CourseCategory::active()->ordered()->get();
        return view('admin.pages.training-camps.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:training_camps,name',
            'slug' => 'nullable|string|max:255|unique:training_camps,slug',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id' => 'nullable|exists:course_categories,id',
            'price' => 'required|numeric|min:0',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'instructor_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'order' => 'nullable|integer|min:0',
        ], [
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
        ]);

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

            DB::commit();

            return redirect()
                ->route('training-camps.index')
                ->with('success', 'تم إنشاء المعسكر التدريبي بنجاح');

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
        $camp = TrainingCamp::with('category')
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
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $camp = TrainingCamp::findOrFail($id);
        $categories = CourseCategory::active()->ordered()->get();

        return view('admin.pages.training-camps.edit', compact('camp', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $camp = TrainingCamp::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:training_camps,name,' . $id,
            'slug' => 'nullable|string|max:255|unique:training_camps,slug,' . $id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id' => 'nullable|exists:course_categories,id',
            'price' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'instructor_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'order' => 'nullable|integer|min:0',
        ], [
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
            'end_date.required' => 'تاريخ النهاية مطلوب',
            'end_date.after' => 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية',
            'max_participants.integer' => 'الحد الأقصى للمشاركين يجب أن يكون رقماً صحيحاً',
            'max_participants.min' => 'الحد الأقصى للمشاركين يجب أن يكون 1 على الأقل',
        ]);

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
                'order' => $request->order ?: 0,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($camp->image && Storage::disk('public')->exists($camp->image)) {
                    Storage::disk('public')->delete($camp->image);
                }

                $image = $request->file('image');
                $imageName = time() . '_' . Str::slug($request->name) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('training-camps', $imageName, 'public');
                $data['image'] = $imagePath;
            }

            $camp->update($data);

            DB::commit();

            return redirect()
                ->route('training-camps.index')
                ->with('success', 'تم تحديث المعسكر التدريبي بنجاح');

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
            if ($camp->image && Storage::disk('public')->exists($camp->image)) {
                Storage::disk('public')->delete($camp->image);
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
        $query = \App\Models\CampEnrollment::with(['camp', 'student'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by camp
        if ($request->filled('camp_id')) {
            $query->where('camp_id', $request->camp_id);
        }

        // Search by student name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $enrollments = $query->paginate(20);
        $camps = TrainingCamp::active()->orderBy('name')->get();

        return view('admin.pages.training-camps.enrollments', compact('enrollments', 'camps'));
    }

    /**
     * Approve enrollment (old route - single id parameter).
     */
    public function approveEnrollmentOld(string $id)
    {
        try {
            $enrollment = CampEnrollment::findOrFail($id);
            $oldStatus = $enrollment->status;
            $enrollment->update(['status' => 'approved']);

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
            $enrollment->update(['status' => 'approved']);

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

            $enrollments = $query->paginate(15);

            return response()->json([
                'success' => true,
                'enrollments' => $enrollments,
                'camp' => $camp->fresh(['category'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new enrollment for a camp.
     */
    public function storeEnrollment(Request $request, string $campId)
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

            // Check if student is already enrolled
            $existingEnrollment = CampEnrollment::where('camp_id', $campId)
                ->where('student_id', $validated['student_id'])
                ->first();

            if ($existingEnrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'الطالب مسجل بالفعل في هذا المعسكر'
                ], 422);
            }

            DB::beginTransaction();

            $enrollment = CampEnrollment::create([
                'camp_id' => $campId,
                'student_id' => $validated['student_id'],
                'status' => $validated['status'] ?? 'pending',
                'payment_status' => $validated['payment_status'] ?? 'unpaid',
                'notes' => $validated['notes'] ?? null,
                'enrollment_date' => now(),
            ]);

            // Update current_participants if status is approved
            if ($enrollment->status === 'approved') {
                $camp->increment('current_participants');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة العضو بنجاح',
                'enrollment' => $enrollment->load('student'),
                'camp' => $camp->fresh()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في التحقق من البيانات',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
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
                'enrollment' => $enrollment
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an enrollment.
     */
    public function destroyEnrollment(string $campId, string $enrollmentId)
    {
        try {
            $camp = TrainingCamp::findOrFail($campId);
            $enrollment = CampEnrollment::where('camp_id', $campId)
                ->findOrFail($enrollmentId);

            DB::beginTransaction();

            $wasApproved = $enrollment->status === 'approved';

            // Delete enrollment
            $enrollment->delete();

            // Decrement current_participants if was approved
            if ($wasApproved) {
                $camp->decrement('current_participants');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف العضو بنجاح',
                'camp' => $camp->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
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

            // Get enrolled student IDs
            $enrolledStudentIds = CampEnrollment::where('camp_id', $campId)
                ->whereIn('student_id', $validated['student_ids'])
                ->pluck('student_id')
                ->toArray();

            // Filter out already enrolled students
            $newStudentIds = array_diff($validated['student_ids'], $enrolledStudentIds);

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
            foreach ($newStudentIds as $studentId) {
                $enrollments[] = CampEnrollment::create([
                    'camp_id' => $campId,
                    'student_id' => $studentId,
                    'status' => $status,
                    'payment_status' => $paymentStatus,
                    'notes' => $notes,
                    'enrollment_date' => now(),
                ]);
            }

            // Update current_participants if status is approved
            if ($status === 'approved') {
                $camp->increment('current_participants', count($newStudentIds));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة ' . count($newStudentIds) . ' طالب بنجاح',
                'added_count' => count($newStudentIds),
                'skipped_count' => count($enrolledStudentIds),
                'camp' => $camp->fresh()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في التحقق من البيانات',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }
}
