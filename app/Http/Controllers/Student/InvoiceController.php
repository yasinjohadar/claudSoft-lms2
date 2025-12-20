<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /**
     * Display student's invoices.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['items.campEnrollment.camp', 'payments'])
            ->where('student_id', Auth::id())
            ->orderBy('issue_date', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->paginate(10);

        // Calculate statistics
        $stats = [
            'total_invoices' => Invoice::where('student_id', Auth::id())->count(),
            'total_amount' => Invoice::where('student_id', Auth::id())->sum('total_amount'),
            'paid_amount' => Invoice::where('student_id', Auth::id())->sum('paid_amount'),
            'remaining_amount' => Invoice::where('student_id', Auth::id())->sum('remaining_amount'),
            'overdue_count' => Invoice::where('student_id', Auth::id())->overdue()->count(),
        ];

        return view('student.pages.invoices.index', compact('invoices', 'stats'));
    }

    /**
     * Display the specified invoice.
     */
    public function show(string $id)
    {
        $invoice = Invoice::with(['items.campEnrollment.camp', 'payments.paymentMethod'])
            ->where('student_id', Auth::id())
            ->findOrFail($id);

        return view('student.pages.invoices.show', compact('invoice'));
    }

    /**
     * Display student's payments.
     */
    public function payments(Request $request)
    {
        $query = \App\Models\Payment::with(['invoice', 'paymentMethod'])
            ->where('student_id', Auth::id())
            ->orderBy('payment_date', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->paginate(10);

        // Calculate statistics
        $stats = [
            'total_payments' => \App\Models\Payment::where('student_id', Auth::id())
                ->where('status', 'completed')
                ->count(),
            'total_paid' => \App\Models\Payment::where('student_id', Auth::id())
                ->where('status', 'completed')
                ->sum('amount'),
        ];

        return view('student.pages.invoices.payments', compact('payments', 'stats'));
    }

    /**
     * Display the specified payment receipt.
     */
    public function showPayment(string $id)
    {
        $payment = \App\Models\Payment::with(['invoice.student', 'invoice.items.campEnrollment.camp', 'paymentMethod'])
            ->where('student_id', Auth::id())
            ->findOrFail($id);

        return view('student.pages.invoices.payment-receipt', compact('payment'));
    }
}
