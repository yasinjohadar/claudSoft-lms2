<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\CourseGroup;
use App\Models\CourseGroupMember;
use App\Models\TrainingCamp;
use App\Services\Finance\StudentPaymentSubmissionService;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymentController extends Controller
{
    public function __construct(
        protected StudentPaymentSubmissionService $paymentSubmissionService
    ) {}
    /**
     * Display a listing of payments.
     */
    public function index(Request $request)
    {
        $query = $this->buildPaymentsQuery($request);
        $stats = $this->computePaymentStats($request);
        $payments = $query->paginate(20)->withQueryString();

        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('order')->get();
        $camps = TrainingCamp::query()->orderBy('name')->get(['id', 'name']);
        $campGroups = CourseGroup::query()
            ->where('is_camp', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        $globalPendingReviewCount = Payment::where('status', 'pending')
            ->whereNotNull('receipt_path')
            ->count();

        if ($request->ajax()) {
            return response()->json([
                'stats' => view('admin.pages.payments.partials.stats', compact('stats'))->render(),
                'table' => view('admin.pages.payments.partials.table', compact('payments'))->render(),
                'pagination' => $payments->hasPages() ? $payments->links()->render() : '',
                'count' => $payments->total(),
            ]);
        }

        return view('admin.pages.payments.index', compact(
            'payments',
            'paymentMethods',
            'camps',
            'campGroups',
            'stats',
            'globalPendingReviewCount'
        ));
    }

    /**
     * Dedicated page: only completed payments.
     */
    public function completed(Request $request)
    {
        $request->merge(['status' => 'completed']);

        return $this->renderFilteredIndex($request, 'admin.pages.payments.completed');
    }

    /**
     * Dedicated page: only student-submitted payments awaiting admin review.
     */
    public function pendingReview(Request $request)
    {
        $request->merge(['status' => 'pending_review']);

        return $this->renderFilteredIndex($request, 'admin.pages.payments.pending-review');
    }

    /**
     * Dedicated page: only payments on invoices that are not yet fully paid.
     */
    public function unpaid(Request $request)
    {
        $request->merge(['payment_status' => 'unpaid']);

        return $this->renderFilteredIndex($request, 'admin.pages.payments.unpaid');
    }

    /**
     * Shared renderer for the completed/pending-review/unpaid pages. The forced
     * filter is applied by the caller via $request->merge() before this runs, so
     * it always wins over any client-supplied query string for that dimension.
     */
    private function renderFilteredIndex(Request $request, string $view)
    {
        $query = $this->buildPaymentsQuery($request);
        $stats = $this->computePaymentStats($request);
        $payments = $query->paginate(20)->withQueryString();

        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('order')->get();
        $camps = TrainingCamp::query()->orderBy('name')->get(['id', 'name']);
        $campGroups = CourseGroup::query()
            ->where('is_camp', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        $globalPendingReviewCount = Payment::where('status', 'pending')
            ->whereNotNull('receipt_path')
            ->count();

        if ($request->ajax()) {
            return response()->json([
                'stats' => view('admin.pages.payments.partials.stats', compact('stats'))->render(),
                'table' => view('admin.pages.payments.partials.table', compact('payments'))->render(),
                'pagination' => $payments->hasPages() ? $payments->links()->render() : '',
                'count' => $payments->total(),
            ]);
        }

        return view($view, compact(
            'payments',
            'paymentMethods',
            'camps',
            'campGroups',
            'stats',
            'globalPendingReviewCount'
        ));
    }

    private function buildPaymentsQuery(Request $request)
    {
        $query = Payment::with([
            'invoice.student',
            'invoice.items.campEnrollment.camp',
            'invoice.items.itemable' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    CourseGroupMember::class => ['group'],
                ]);
            },
            'paymentMethod',
            'receivedBy',
            'student',
        ])->orderBy('payment_date', 'desc');

        $searchTerms = array_filter([
            $request->input('search'),
            $request->input('payment_number'),
        ]);

        if (!empty($searchTerms)) {
            $search = end($searchTerms);
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('receipt_number', 'like', "%{$search}%")
                    ->orWhereHas('invoice', function ($q2) use ($search) {
                        $q2->where('invoice_number', 'like', "%{$search}%")
                            ->orWhereHas('student', function ($q3) use ($search) {
                                $q3->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('student', function ($q4) use ($search) {
                        $q4->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('invoice_number')) {
            $invoiceNumber = $request->invoice_number;
            $query->whereHas('invoice', function ($q) use ($invoiceNumber) {
                $q->where('invoice_number', 'like', "%{$invoiceNumber}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'pending_review') {
                $query->where('status', 'pending')->whereNotNull('receipt_path');
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('has_receipt')) {
            if ($request->has_receipt === '1') {
                $query->whereNotNull('receipt_path');
            } elseif ($request->has_receipt === '0') {
                $query->whereNull('receipt_path');
            }
        }

        if ($request->filled('source')) {
            if ($request->source === 'student') {
                $query->whereNotNull('receipt_path');
            } elseif ($request->source === 'admin') {
                $query->whereNotNull('received_by')->whereNull('receipt_path');
            }
        }

        if ($request->filled('payment_status')) {
            if ($request->payment_status === 'fully_paid') {
                $query->whereHas('invoice', fn ($q) => $q->where('status', 'paid'));
            } elseif ($request->payment_status === 'partially_paid') {
                $query->whereHas('invoice', fn ($q) => $q->where('status', 'partial'));
            } elseif ($request->payment_status === 'unpaid') {
                $query->whereHas('invoice', fn ($q) => $q->whereIn('status', ['issued', 'draft']));
            }
        }

        if ($request->filled('payment_method_id')) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        if ($request->filled('camp_id')) {
            $campFilter = (string) $request->camp_id;

            if (str_starts_with($campFilter, 'group:')) {
                $groupId = (int) substr($campFilter, 6);
                $query->whereHas('invoice.items', function ($q) use ($groupId) {
                    $q->where('itemable_type', CourseGroupMember::class)
                        ->whereIn('itemable_id', function ($sub) use ($groupId) {
                            $sub->select('id')
                                ->from('course_group_members')
                                ->where('group_id', $groupId);
                        });
                });
            } else {
                $query->whereHas('invoice.items.campEnrollment', function ($q) use ($campFilter) {
                    $q->where('camp_id', $campFilter);
                });
            }
        }

        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', (float) $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', (float) $request->max_amount);
        }

        return $query;
    }

    private function computePaymentStats(Request $request): array
    {
        $base = $this->buildPaymentsQuery($request);

        $invoiceIds = (clone $base)
            ->reorder()
            ->select('invoice_id')
            ->whereNotNull('invoice_id')
            ->distinct()
            ->pluck('invoice_id');

        $pendingReviewQuery = (clone $base)->where('status', 'pending')->whereNotNull('receipt_path');

        return [
            'completed_amount' => (float) (clone $base)->where('status', 'completed')->sum('amount'),
            'completed_count' => (int) (clone $base)->where('status', 'completed')->count(),
            'pending_amount' => (float) (clone $base)->where('status', 'pending')->sum('amount'),
            'pending_count' => (int) (clone $base)->where('status', 'pending')->count(),
            'pending_review_amount' => (float) (clone $pendingReviewQuery)->sum('amount'),
            'pending_review_count' => (int) (clone $pendingReviewQuery)->count(),
            'cancelled_amount' => (float) (clone $base)->where('status', 'cancelled')->sum('amount'),
            'cancelled_count' => (int) (clone $base)->where('status', 'cancelled')->count(),
            'refunded_amount' => (float) (clone $base)->where('status', 'refunded')->sum('amount'),
            'refunded_count' => (int) (clone $base)->where('status', 'refunded')->count(),
            'paid_amount' => (float) (clone $base)->sum('amount'),
            'remaining_amount' => (float) Invoice::whereIn('id', $invoiceIds)->sum('remaining_amount'),
        ];
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(Request $request)
    {
        $invoiceId = $request->invoice_id;
        $invoice = null;

        if ($invoiceId) {
            $invoice = Invoice::with('student')->findOrFail($invoiceId);
        }

        $invoices = Invoice::with('student')
            ->whereIn('status', ['issued', 'partial'])
            ->orderBy('invoice_number', 'desc')
            ->get();

        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('order')->get();

        return view('admin.pages.payments.create', compact('invoices', 'paymentMethods', 'invoice'));
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_date' => 'required|date',
            'transaction_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);

        // Check if amount doesn't exceed remaining
        if ($request->amount > $invoice->remaining_amount) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'المبلغ المدخل أكبر من المبلغ المتبقي ($' . number_format($invoice->remaining_amount, 2) . ')');
        }

        try {
            DB::beginTransaction();

            // Record payment
            $payment = $invoice->recordPayment($request->amount, [
                'payment_method_id' => $request->payment_method_id,
                'payment_date' => $request->payment_date,
                'transaction_id' => $request->transaction_id,
                'notes' => $request->notes,
                'received_by' => auth()->id(),
            ]);

            // Generate receipt number
            $payment->receipt_number = Payment::generateReceiptNumber();
            $payment->save();

            DB::commit();

            return redirect()
                ->route('invoices.show', $invoice->id)
                ->with('success', 'تم تسجيل الدفعة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء تسجيل الدفعة: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(string $id)
    {
        $payment = Payment::with(['invoice.student', 'invoice.items', 'paymentMethod', 'receivedBy'])
            ->findOrFail($id);

        return view('admin.pages.payments.show', compact('payment'));
    }

    /**
     * Approve a pending student payment submission.
     */
    public function approve(string $id)
    {
        $payment = Payment::findOrFail($id);

        try {
            $this->paymentSubmissionService->approve($payment, auth()->user());

            return redirect()
                ->route('payments.show', $payment->id)
                ->with('success', 'تمت الموافقة على الدفعة وتسجيلها في حساب الطالب.');
        } catch (InvalidArgumentException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء الموافقة على الدفعة. يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * Reject a pending student payment submission.
     */
    public function reject(Request $request, string $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $payment = Payment::findOrFail($id);

        try {
            $this->paymentSubmissionService->reject($payment, auth()->user(), $request->rejection_reason);

            return redirect()
                ->route('payments.show', $payment->id)
                ->with('success', 'تم رفض طلب الدفع.');
        } catch (InvalidArgumentException $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->with('error', 'حدث خطأ أثناء رفض الدفعة. يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * Stream payment receipt file for admin review.
     */
    public function downloadReceipt(string $id)
    {
        $payment = Payment::findOrFail($id);

        if (! $payment->receipt_path) {
            abort(404, 'لا يوجد إيصال مرفق');
        }

        $disk = $payment->receipt_disk ?: StudentPaymentSubmissionService::RECEIPT_DISK;
        $filename = basename($payment->receipt_path);

        return serve_storage_file_response(
            [$disk, StudentPaymentSubmissionService::RECEIPT_DISK, 'public'],
            $payment->receipt_path,
            $filename
        );
    }

    /**
     * Cancel a payment.
     */
    public function cancel(Request $request, string $id)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $payment = Payment::findOrFail($id);

        if ($payment->status === 'cancelled') {
            return redirect()
                ->back()
                ->with('error', 'هذه الدفعة ملغاة بالفعل');
        }

        try {
            DB::beginTransaction();

            $payment->cancel($request->reason);

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'تم إلغاء الدفعة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Refund a payment.
     */
    public function refund(Request $request, string $id)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $payment = Payment::findOrFail($id);

        if ($payment->status === 'refunded') {
            return redirect()
                ->back()
                ->with('error', 'هذه الدفعة مستردة بالفعل');
        }

        try {
            DB::beginTransaction();

            $payment->refund($request->reason);

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'تم استرداد الدفعة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }
}
