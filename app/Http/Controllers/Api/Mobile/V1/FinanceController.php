<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Resources\Mobile\V1\InvoiceResource;
use App\Http\Resources\Mobile\V1\PaymentResource;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * الفواتير والمدفوعات للطالب (عرض فقط — بدون تسديد من التطبيق).
 * نفس بيانات صفحة "فواتيري" في لوحة الطالب؛ الفواتير الملغاة لا تدخل في المبالغ المستحقة.
 */
class FinanceController extends BaseMobileController
{
    private const CURRENCY = ['code' => 'USD', 'symbol' => '$'];

    public function summary(Request $request): JsonResponse
    {
        $studentId = $request->user()->id;
        $active = fn () => Invoice::where('student_id', $studentId)->where('status', '!=', 'cancelled');

        $total = (float) $active()->sum('total_amount');
        $paid = (float) $active()->sum('paid_amount');

        $nextDue = $active()->unpaid()
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->first();

        $pending = Payment::where('student_id', $studentId)->where('status', 'pending');

        return $this->success([
            'currency' => self::CURRENCY,
            'total_amount' => $total,
            'paid_amount' => $paid,
            'remaining_amount' => (float) $active()->sum('remaining_amount'),
            'paid_percentage' => $total > 0 ? round(min(100, $paid / $total * 100), 1) : 0.0,
            'invoices' => [
                'total' => $active()->count(),
                'paid' => $active()->where('status', 'paid')->count(),
                'unpaid' => $active()->unpaid()->count(),
                'overdue' => $active()->overdue()->count(),
            ],
            'pending_payments' => [
                'count' => (clone $pending)->count(),
                'amount' => (float) (clone $pending)->sum('amount'),
            ],
            'next_due' => $nextDue ? [
                'invoice_id' => (int) $nextDue->id,
                'invoice_number' => $nextDue->invoice_number,
                'due_date' => $nextDue->due_date?->toDateString(),
                'remaining_amount' => (float) $nextDue->remaining_amount,
                'is_overdue' => (bool) $nextDue->is_overdue,
            ] : null,
        ]);
    }

    public function invoices(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['nullable', 'in:unpaid,paid,overdue'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $invoices = Invoice::query()
            ->where('student_id', $request->user()->id)
            ->with(['items:id,invoice_id,description', 'pendingPayment'])
            ->when($request->input('status'), fn (Builder $q, string $status) => match ($status) {
                'unpaid' => $q->unpaid(),
                'paid' => $q->where('status', 'paid'),
                'overdue' => $q->overdue(),
            })
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15));

        return $this->success(
            InvoiceResource::collection($invoices->getCollection())->resolve($request),
            null,
            $this->paginationMeta($invoices),
        );
    }

    public function invoice(Request $request, int $invoice): JsonResponse
    {
        $model = Invoice::query()
            ->where('student_id', $request->user()->id)
            ->with(['items', 'payments' => fn ($q) => $q->with('paymentMethod')->orderByDesc('payment_date'), 'pendingPayment'])
            ->find($invoice);

        if (! $model) {
            return $this->error(__('mobile.invoice_not_found'), 404);
        }

        return $this->success(new InvoiceResource($model));
    }

    public function payments(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['nullable', 'in:completed,pending,failed'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $payments = Payment::query()
            ->where('student_id', $request->user()->id)
            ->with(['invoice:id,invoice_number', 'paymentMethod:id,name'])
            ->when($request->input('status'), fn (Builder $q, string $status) => $q->where('status', $status))
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 15));

        return $this->success(
            PaymentResource::collection($payments->getCollection())->resolve($request),
            null,
            $this->paginationMeta($payments),
        );
    }
}
