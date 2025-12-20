<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\TrainingCamp;
use App\Models\CampEnrollment;
use App\Models\CourseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TrainingCampController extends Controller
{
    /**
     * Display a listing of available training camps for students.
     */
    public function index(Request $request)
    {
        $query = TrainingCamp::with('category')
            ->active()
            ->where('end_date', '>=', now()->startOfDay());

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by price range
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('instructor_name', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort', 'start_date');
        $sortOrder = $request->get('order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $camps = $query->paginate(12);
        $categories = CourseCategory::active()->ordered()->get();

        // Get user enrollments
        $userEnrollments = [];
        if (Auth::check()) {
            $userEnrollments = CampEnrollment::where('student_id', Auth::id())
                ->pluck('camp_id')
                ->toArray();
        }

        return view('student.pages.training-camps.index', compact('camps', 'categories', 'userEnrollments'));
    }

    /**
     * Display the specified training camp details.
     */
    public function show(string $slug)
    {
        $trainingCamp = TrainingCamp::with(['category', 'enrollments'])
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        // Check if user is already enrolled
        $isEnrolled = false;
        $enrollment = null;

        if (Auth::check()) {
            $enrollment = CampEnrollment::where('camp_id', $trainingCamp->id)
                ->where('student_id', Auth::id())
                ->first();
            $isEnrolled = $enrollment !== null;
        }

        return view('student.pages.training-camps.show', compact('trainingCamp', 'isEnrolled', 'enrollment'));
    }

    /**
     * Enroll student in a training camp.
     */
    public function enroll(Request $request, string $id)
    {
        $camp = TrainingCamp::findOrFail($id);

        // Check if camp is active
        if (!$camp->is_active) {
            return redirect()
                ->back()
                ->with('error', 'هذا المعسكر غير متاح حالياً');
        }

        // Check if camp is full
        if ($camp->isFull()) {
            return redirect()
                ->back()
                ->with('error', 'المعسكر ممتلئ، لا توجد مقاعد متاحة');
        }

        // Check if already enrolled
        $existingEnrollment = CampEnrollment::where('camp_id', $camp->id)
            ->where('student_id', Auth::id())
            ->first();

        if ($existingEnrollment) {
            return redirect()
                ->back()
                ->with('error', 'أنت مسجل بالفعل في هذا المعسكر');
        }

        try {
            DB::beginTransaction();

            // Create enrollment
            $enrollment = CampEnrollment::create([
                'camp_id' => $camp->id,
                'student_id' => Auth::id(),
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'notes' => $request->notes,
            ]);

            // Increment current participants
            $camp->increment('current_participants');

            // Create invoice for the camp enrollment
            $invoice = \App\Models\Invoice::create([
                'invoice_number' => \App\Models\Invoice::generateInvoiceNumber(),
                'student_id' => Auth::id(),
                'total_amount' => $camp->price,
                'paid_amount' => 0,
                'remaining_amount' => $camp->price,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'status' => 'issued',
                'issue_date' => now(),
                'due_date' => $camp->start_date,
                'notes' => 'فاتورة التسجيل في معسكر: ' . $camp->name,
                'created_by' => null, // System generated
            ]);

            // Create invoice item
            \App\Models\InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'رسوم التسجيل في معسكر: ' . $camp->name,
                'quantity' => 1,
                'unit_price' => $camp->price,
                'total_price' => $camp->price,
                'camp_enrollment_id' => $enrollment->id,
            ]);

            DB::commit();

            return redirect()
                ->route('student.training-camps.my-enrollments')
                ->with('success', 'تم إرسال طلب التسجيل بنجاح! سيتم مراجعته قريباً');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء التسجيل: ' . $e->getMessage());
        }
    }

    /**
     * Display student's enrollments.
     */
    public function myEnrollments()
    {
        $enrollments = CampEnrollment::with(['camp.category'])
            ->where('student_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('student.pages.training-camps.my-enrollments', compact('enrollments'));
    }

    /**
     * Cancel enrollment.
     */
    public function cancelEnrollment(string $id)
    {
        $enrollment = CampEnrollment::where('id', $id)
            ->where('student_id', Auth::id())
            ->firstOrFail();

        // Check if payment is made
        if ($enrollment->payment_status === 'paid') {
            return redirect()
                ->back()
                ->with('error', 'لا يمكن إلغاء التسجيل بعد الدفع، يرجى التواصل مع الإدارة');
        }

        try {
            DB::beginTransaction();

            // Update enrollment status
            $enrollment->update(['status' => 'cancelled']);

            // Decrement current participants
            $enrollment->camp->decrement('current_participants');

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'تم إلغاء التسجيل بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء إلغاء التسجيل: ' . $e->getMessage());
        }
    }
}
