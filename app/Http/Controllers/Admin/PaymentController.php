<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments.
     */
    public function index(Request $request)
    {
        $query = Payment::with(['invoice.student', 'paymentMethod', 'receivedBy'])
            ->orderBy('payment_date', 'desc');

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('invoice', function($q2) use ($search) {
                      $q2->where('invoice_number', 'like', "%{$search}%")
                         ->orWhereHas('student', function($q3) use ($search) {
                             $q3->where('name', 'like', "%{$search}%");
                         });
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment method
        if ($request->filled('payment_method_id')) {
            $query->where('payment_method_id', $request->payment_method_id);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        $payments = $query->paginate(20);
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('order')->get();

        return view('admin.pages.payments.index', compact('payments', 'paymentMethods'));
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
