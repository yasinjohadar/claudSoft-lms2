<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API للطالب: فواتيري ومدفوعاتي (JSON للتطبيق).
 * يتطلب Bearer token عبر Laravel Sanctum.
 */
class InvoiceController extends Controller
{
    public function invoices(Request $request): JsonResponse
    {
        $studentId = $request->user()->id;
        $perPage = min(max((int) $request->input('per_page', 10), 1), 50);

        $query = Invoice::query()
            ->with(['items.campEnrollment.camp', 'payments.paymentMethod'])
            ->where('student_id', $studentId)
            ->orderBy('issue_date', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $paginator = $query->paginate($perPage);

        $stats = [
            'total_invoices' => Invoice::where('student_id', $studentId)->count(),
            'total_amount' => (float) Invoice::where('student_id', $studentId)->sum('total_amount'),
            'paid_amount' => (float) Invoice::where('student_id', $studentId)->sum('paid_amount'),
            'remaining_amount' => (float) Invoice::where('student_id', $studentId)->sum('remaining_amount'),
            'overdue_count' => Invoice::where('student_id', $studentId)->overdue()->count(),
        ];

        $invoices = collect($paginator->items())->map(fn (Invoice $invoice) => $this->serializeInvoice($invoice))->values();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'invoices' => $invoices,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
        ]);
    }

    public function invoice(Request $request, string $id): JsonResponse
    {
        $studentId = $request->user()->id;

        $invoice = Invoice::query()
            ->with(['items.campEnrollment.camp', 'payments.paymentMethod'])
            ->where('student_id', $studentId)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'invoice' => $this->serializeInvoice($invoice),
            ],
        ]);
    }

    public function payments(Request $request): JsonResponse
    {
        $studentId = $request->user()->id;
        $perPage = min(max((int) $request->input('per_page', 10), 1), 50);

        $query = Payment::query()
            ->with(['invoice', 'paymentMethod'])
            ->where('student_id', $studentId)
            ->orderBy('payment_date', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $paginator = $query->paginate($perPage);

        $stats = [
            'total_payments' => Payment::where('student_id', $studentId)
                ->where('status', 'completed')
                ->count(),
            'total_paid' => (float) Payment::where('student_id', $studentId)
                ->where('status', 'completed')
                ->sum('amount'),
        ];

        $payments = collect($paginator->items())->map(fn (Payment $payment) => $this->serializePaymentListItem($payment))->values();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'payments' => $payments,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
        ]);
    }

    public function payment(Request $request, string $id): JsonResponse
    {
        $studentId = $request->user()->id;

        $payment = Payment::query()
            ->with(['invoice.student', 'invoice.items.campEnrollment.camp', 'paymentMethod'])
            ->where('student_id', $studentId)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'payment' => $this->serializePaymentDetail($payment),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeInvoice(Invoice $invoice): array
    {
        return [
            'id' => (int) $invoice->id,
            'invoice_number' => (string) $invoice->invoice_number,
            'total_amount' => (float) $invoice->total_amount,
            'paid_amount' => (float) $invoice->paid_amount,
            'remaining_amount' => (float) $invoice->remaining_amount,
            'tax_amount' => (float) $invoice->tax_amount,
            'discount_amount' => (float) $invoice->discount_amount,
            'status' => (string) $invoice->status,
            'status_label_ar' => $this->invoiceStatusLabelAr($invoice->status),
            'issue_date' => $invoice->issue_date?->toDateString(),
            'due_date' => $invoice->due_date?->toDateString(),
            'is_overdue' => (bool) $invoice->is_overdue,
            'is_paid' => (bool) $invoice->is_paid,
            'is_partial' => (bool) $invoice->is_partial,
            'notes' => $invoice->notes !== null ? (string) $invoice->notes : null,
            'items' => $invoice->items->map(fn (InvoiceItem $item) => $this->serializeInvoiceItem($item))->values()->all(),
            'payments' => $invoice->payments->map(fn (Payment $p) => $this->serializePaymentEmbedded($p))->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeInvoiceItem(InvoiceItem $item): array
    {
        $camp = null;
        if ($item->relationLoaded('campEnrollment') && $item->campEnrollment && $item->campEnrollment->relationLoaded('camp') && $item->campEnrollment->camp) {
            $c = $item->campEnrollment->camp;
            $camp = [
                'id' => (int) $c->id,
                'name' => (string) $c->name,
                'slug' => $c->slug !== null ? (string) $c->slug : null,
            ];
        }

        return [
            'id' => (int) $item->id,
            'description' => (string) $item->description,
            'quantity' => (int) $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'total_price' => (float) $item->total_price,
            'camp_enrollment_id' => $item->camp_enrollment_id !== null ? (int) $item->camp_enrollment_id : null,
            'camp' => $camp,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePaymentEmbedded(Payment $payment): array
    {
        return [
            'id' => (int) $payment->id,
            'payment_number' => (string) $payment->payment_number,
            'amount' => (float) $payment->amount,
            'status' => (string) $payment->status,
            'status_label_ar' => $this->paymentStatusLabelAr($payment->status),
            'payment_date' => $payment->payment_date?->toIso8601String(),
            'transaction_id' => $payment->transaction_id !== null ? (string) $payment->transaction_id : null,
            'receipt_number' => $payment->receipt_number !== null ? (string) $payment->receipt_number : null,
            'notes' => $payment->notes !== null ? (string) $payment->notes : null,
            'reference' => $payment->reference !== null ? (string) $payment->reference : null,
            'payment_method' => $this->serializePaymentMethod($payment->paymentMethod),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePaymentListItem(Payment $payment): array
    {
        $invoice = $payment->invoice;

        return [
            'id' => (int) $payment->id,
            'payment_number' => (string) $payment->payment_number,
            'invoice_id' => (int) $payment->invoice_id,
            'amount' => (float) $payment->amount,
            'status' => (string) $payment->status,
            'status_label_ar' => $this->paymentStatusLabelAr($payment->status),
            'payment_date' => $payment->payment_date?->toIso8601String(),
            'transaction_id' => $payment->transaction_id !== null ? (string) $payment->transaction_id : null,
            'receipt_number' => $payment->receipt_number !== null ? (string) $payment->receipt_number : null,
            'notes' => $payment->notes !== null ? (string) $payment->notes : null,
            'reference' => $payment->reference !== null ? (string) $payment->reference : null,
            'payment_method' => $this->serializePaymentMethod($payment->paymentMethod),
            'invoice' => $invoice ? [
                'id' => (int) $invoice->id,
                'invoice_number' => (string) $invoice->invoice_number,
                'status' => (string) $invoice->status,
                'status_label_ar' => $this->invoiceStatusLabelAr($invoice->status),
                'total_amount' => (float) $invoice->total_amount,
                'remaining_amount' => (float) $invoice->remaining_amount,
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePaymentDetail(Payment $payment): array
    {
        $base = $this->serializePaymentEmbedded($payment);
        $invoice = $payment->invoice;

        $base['invoice'] = $invoice ? $this->serializeInvoiceForPaymentReceipt($invoice) : null;

        return $base;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeInvoiceForPaymentReceipt(Invoice $invoice): array
    {
        $studentPayload = null;
        if ($invoice->relationLoaded('student') && $invoice->student instanceof User) {
            $studentPayload = [
                'id' => (int) $invoice->student->id,
                'name' => (string) $invoice->student->name,
            ];
        }

        return [
            'id' => (int) $invoice->id,
            'invoice_number' => (string) $invoice->invoice_number,
            'total_amount' => (float) $invoice->total_amount,
            'paid_amount' => (float) $invoice->paid_amount,
            'remaining_amount' => (float) $invoice->remaining_amount,
            'tax_amount' => (float) $invoice->tax_amount,
            'discount_amount' => (float) $invoice->discount_amount,
            'status' => (string) $invoice->status,
            'status_label_ar' => $this->invoiceStatusLabelAr($invoice->status),
            'issue_date' => $invoice->issue_date?->toDateString(),
            'due_date' => $invoice->due_date?->toDateString(),
            'is_overdue' => (bool) $invoice->is_overdue,
            'notes' => $invoice->notes !== null ? (string) $invoice->notes : null,
            'student' => $studentPayload,
            'items' => $invoice->items->map(fn (InvoiceItem $item) => $this->serializeInvoiceItem($item))->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function serializePaymentMethod(?PaymentMethod $method): ?array
    {
        if (!$method) {
            return null;
        }

        return [
            'id' => (int) $method->id,
            'name' => (string) $method->name,
            'name_en' => $method->name_en !== null ? (string) $method->name_en : null,
            'description' => $method->description !== null ? (string) $method->description : null,
            'is_active' => (bool) $method->is_active,
            'requires_transaction_id' => (bool) $method->requires_transaction_id,
            'order' => (int) $method->order,
        ];
    }

    private function invoiceStatusLabelAr(string $status): string
    {
        return match ($status) {
            'draft' => 'مسودة',
            'issued' => 'صادرة',
            'partial' => 'مدفوعة جزئياً',
            'paid' => 'مدفوعة',
            'cancelled' => 'ملغاة',
            'refunded' => 'مستردة',
            default => $status,
        };
    }

    private function paymentStatusLabelAr(string $status): string
    {
        return match ($status) {
            'pending' => 'قيد الانتظار',
            'completed' => 'مكتملة',
            'failed' => 'فاشلة',
            'cancelled' => 'ملغاة',
            'refunded' => 'مستردة',
            default => $status,
        };
    }
}
