<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['student', 'items', 'payments'])
            ->orderBy('created_at', 'desc');

        // Filter by search (student name/email or invoice number)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('student', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by student
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('issue_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('issue_date', '<=', $request->to_date);
        }

        // Filter overdue
        if ($request->has('overdue') && $request->overdue == '1') {
            $query->overdue();
        }

        $invoices = $query->paginate(20);
        $students = User::role('student')->orderBy('name')->get();

        return view('admin.pages.invoices.index', compact('invoices', 'students'));
    }

    /**
     * Display the specified invoice.
     */
    public function show(string $id)
    {
        $invoice = Invoice::with(['student', 'items.campEnrollment.camp', 'payments.paymentMethod', 'payments.receivedBy'])
            ->findOrFail($id);

        return view('admin.pages.invoices.show', compact('invoice'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create()
    {
        $students = User::role('student')->orderBy('name')->get();
        return view('admin.pages.invoices.create', compact('students'));
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'student_id' => $request->student_id,
                'total_amount' => 0,
                'paid_amount' => 0,
                'remaining_amount' => 0,
                'tax_amount' => $request->tax_amount ?? 0,
                'discount_amount' => $request->discount_amount ?? 0,
                'status' => 'draft',
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            // Create invoice items
            foreach ($request->items as $item) {
                $totalPrice = $item['quantity'] * $item['unit_price'];
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalPrice,
                ]);
            }

            // Calculate totals
            $invoice->calculateTotals();
            $invoice->markAsIssued();

            DB::commit();

            return redirect()
                ->route('invoices.show', $invoice->id)
                ->with('success', 'تم إنشاء الفاتورة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء إنشاء الفاتورة: ' . $e->getMessage());
        }
    }

    /**
     * Cancel an invoice.
     */
    public function cancel(Request $request, string $id)
    {
        $request->validate([
            'reason' => 'nullable|string',
        ]);

        $invoice = Invoice::findOrFail($id);

        if ($invoice->status === 'paid') {
            return redirect()
                ->back()
                ->with('error', 'لا يمكن إلغاء فاتورة مدفوعة بالكامل');
        }

        $invoice->cancel($request->reason);

        return redirect()
            ->back()
            ->with('success', 'تم إلغاء الفاتورة بنجاح');
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(string $id)
    {
        $invoice = Invoice::findOrFail($id);

        if ($invoice->status === 'paid') {
            return redirect()
                ->back()
                ->with('error', 'هذه الفاتورة مدفوعة بالفعل');
        }

        $invoice->markAsPaid();

        return redirect()
            ->back()
            ->with('success', 'تم تحديث حالة الفاتورة إلى مدفوعة');
    }

    /**
     * Delete invoice.
     */
    public function destroy(string $id)
    {
        $invoice = Invoice::findOrFail($id);

        if ($invoice->payments()->count() > 0) {
            return redirect()
                ->back()
                ->with('error', 'لا يمكن حذف فاتورة تحتوي على مدفوعات');
        }

        $invoice->delete();

        return redirect()
            ->route('invoices.index')
            ->with('success', 'تم حذف الفاتورة بنجاح');
    }
}
