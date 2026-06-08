<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\Finance\StudentPaymentSubmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class InvoiceController extends Controller
{
    public function __construct(
        protected StudentPaymentSubmissionService $paymentSubmissionService
    ) {}

    /**
     * Display student's invoices.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['items.campEnrollment.camp', 'payments', 'pendingPayment'])
            ->where('student_id', Auth::id())
            ->orderBy('issue_date', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->paginate(10);

        $stats = [
            'total_invoices' => Invoice::where('student_id', Auth::id())->count(),
            'total_amount' => Invoice::where('student_id', Auth::id())->sum('total_amount'),
            'paid_amount' => Invoice::where('student_id', Auth::id())->sum('paid_amount'),
            'remaining_amount' => Invoice::where('student_id', Auth::id())->sum('remaining_amount'),
            'overdue_count' => Invoice::where('student_id', Auth::id())->overdue()->count(),
        ];

        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('order')->get();

        return view('student.pages.invoices.index', compact('invoices', 'stats', 'paymentMethods'));
    }

    /**
     * Display the specified invoice.
     */
    public function show(string $id)
    {
        $invoice = Invoice::with(['items.campEnrollment.camp', 'payments.paymentMethod', 'pendingPayment'])
            ->where('student_id', Auth::id())
            ->findOrFail($id);

        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('order')->get();

        return view('student.pages.invoices.show', compact('invoice', 'paymentMethods'));
    }

    /**
     * Submit a payment for admin approval.
     */
    public function submitPayment(Request $request, string $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date|before_or_equal:today',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'receipt' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        $invoice = Invoice::where('student_id', Auth::id())->findOrFail($id);

        if ((float) $validated['amount'] > (float) $invoice->remaining_amount) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['amount' => 'المبلغ المدخل أكبر من المبلغ المتبقي ($' . number_format($invoice->remaining_amount, 2) . ')']);
        }

        try {
            $this->paymentSubmissionService->submit(
                $invoice,
                Auth::user(),
                $validated,
                $request->file('receipt')
            );

            return redirect()
                ->route('student.invoices.show', $invoice->id)
                ->with('success', 'تم إرسال طلب الدفع بنجاح. سيتم مراجعته من قبل الإدارة.');
        } catch (InvalidArgumentException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Stream payment receipt file.
     */
    public function downloadReceipt(string $id)
    {
        $payment = Payment::where('student_id', Auth::id())->findOrFail($id);

        if (! $payment->receipt_path) {
            abort(404, 'لا يوجد إيصال مرفق');
        }

        $disk = $payment->receipt_disk ?: StudentPaymentSubmissionService::RECEIPT_DISK;
        $filename = basename($payment->receipt_path);

        return serve_storage_file_response([$disk, 'public'], $payment->receipt_path, $filename);
    }

    /**
     * Display student's payments.
     */
    public function payments(Request $request)
    {
        $query = Payment::with(['invoice', 'paymentMethod'])
            ->where('student_id', Auth::id())
            ->orderBy('payment_date', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->paginate(10);

        $stats = [
            'total_payments' => Payment::where('student_id', Auth::id())
                ->where('status', 'completed')
                ->count(),
            'total_paid' => Payment::where('student_id', Auth::id())
                ->where('status', 'completed')
                ->sum('amount'),
            'pending_count' => Payment::where('student_id', Auth::id())
                ->where('status', 'pending')
                ->count(),
        ];

        return view('student.pages.invoices.payments', compact('payments', 'stats'));
    }

    /**
     * Display the specified payment receipt.
     */
    public function showPayment(string $id)
    {
        $payment = Payment::with(['invoice.student', 'invoice.items.campEnrollment.camp', 'paymentMethod'])
            ->where('student_id', Auth::id())
            ->findOrFail($id);

        return view('student.pages.invoices.payment-receipt', compact('payment'));
    }
}
