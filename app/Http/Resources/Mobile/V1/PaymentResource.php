<?php

namespace App\Http\Resources\Mobile\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Payment
 */
class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'payment_number' => $this->payment_number,
            'amount' => (float) $this->amount,
            'status' => $this->status,
            'payment_date' => $this->payment_date?->toIso8601String(),
            'method' => $this->paymentMethod?->name,
            'receipt_number' => $this->receipt_number,
            'reference' => $this->reference,
            'notes' => $this->notes,
            // سبب رفض الدفعة (عند مراجعة الإدارة) — يساعد الطالب على فهم الحالة.
            'rejection_reason' => $this->status === 'failed' ? $this->rejection_reason : null,
            'invoice' => $this->whenLoaded('invoice', fn () => $this->invoice ? [
                'id' => (int) $this->invoice->id,
                'invoice_number' => $this->invoice->invoice_number,
            ] : null),
        ];
    }
}
