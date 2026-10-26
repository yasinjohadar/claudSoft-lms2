<?php

namespace App\Http\Resources\Mobile\V1;

use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * فاتورة الطالب. البنود والدفعات تُضاف فقط عند تحميلها (صفحة التفاصيل).
 *
 * @mixin \App\Models\Invoice
 */
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $total = (float) $this->total_amount;
        $paid = (float) $this->paid_amount;

        return [
            'id' => (int) $this->id,
            'invoice_number' => $this->invoice_number,
            'status' => $this->status,
            'is_overdue' => (bool) $this->is_overdue,
            'total_amount' => $total,
            'paid_amount' => $paid,
            'remaining_amount' => (float) $this->remaining_amount,
            'paid_percentage' => $total > 0 ? round(min(100, $paid / $total * 100), 1) : 0.0,
            'tax_amount' => (float) $this->tax_amount,
            'discount_amount' => (float) $this->discount_amount,
            'issue_date' => $this->issue_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'title' => $this->whenLoaded('items', fn () => $this->items->first()?->description),
            'items_count' => $this->whenLoaded('items', fn () => $this->items->count()),
            'has_pending_payment' => $this->whenLoaded('pendingPayment', fn () => $this->pendingPayment !== null),
            'notes' => $this->notes,
            'items' => $this->when(
                $request->route('invoice') !== null,
                fn () => $this->items->map(fn (InvoiceItem $item) => [
                    'id' => (int) $item->id,
                    'description' => $item->description,
                    'quantity' => (int) $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total_price' => (float) $item->total_price,
                ])->values(),
            ),
            'payments' => $this->when(
                $request->route('invoice') !== null,
                fn () => PaymentResource::collection($this->payments)->resolve($request),
            ),
        ];
    }
}
