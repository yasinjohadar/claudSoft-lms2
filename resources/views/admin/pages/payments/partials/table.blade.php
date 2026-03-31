@forelse($payments as $payment)
    <tr>
        <td>
            <a href="{{ route('payments.show', $payment->id) }}" class="text-primary fw-semibold">
                {{ $payment->payment_number }}
            </a>
        </td>
        <td>
            @if($payment->invoice)
                <a href="{{ route('invoices.show', $payment->invoice_id) }}">
                    {{ $payment->invoice->invoice_number }}
                </a>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>
            @if($payment->invoice && $payment->invoice->student)
                {{ $payment->invoice->student->name }}
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>
            @php
                $campNames = collect(optional($payment->invoice)->items)
                    ->map(fn($item) => optional(optional($item->campEnrollment)->camp)->name)
                    ->filter()
                    ->unique()
                    ->values();
            @endphp

            @if($campNames->isNotEmpty())
                @foreach($campNames as $campName)
                    <span class="badge bg-info-transparent me-1">{{ $campName }}</span>
                @endforeach
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td class="fw-bold">${{ number_format($payment->amount, 2) }}</td>
        <td>
            @if($payment->invoice && $payment->invoice->remaining_amount > 0)
                <span class="text-danger fw-bold">${{ number_format($payment->invoice->remaining_amount, 2) }}</span>
            @elseif($payment->invoice)
                <span class="text-success">$0.00</span>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>
            @if($payment->invoice)
                @php
                    $invoicePaymentStatus = $payment->invoice->status;
                    $invoiceStatusLabels = [
                        'paid' => 'كامل',
                        'partial' => 'جزئي',
                        'issued' => 'غير مسدد',
                        'draft' => 'غير مسدد',
                    ];
                    $invoiceStatusColors = [
                        'paid' => 'bg-success',
                        'partial' => 'bg-warning',
                        'issued' => 'bg-secondary',
                        'draft' => 'bg-secondary',
                    ];
                @endphp
                <span class="badge {{ $invoiceStatusColors[$invoicePaymentStatus] ?? 'bg-secondary' }}">
                    {{ $invoiceStatusLabels[$invoicePaymentStatus] ?? 'غير مسدد' }}
                </span>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>
            @if($payment->paymentMethod)
                <i class="bi bi-credit-card me-1"></i>{{ $payment->paymentMethod->name }}
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
        <td>
            @php
                $statusColors = [
                    'pending' => 'bg-warning',
                    'completed' => 'bg-success',
                    'failed' => 'bg-danger',
                    'cancelled' => 'bg-secondary',
                    'refunded' => 'bg-info'
                ];
                $statusLabels = [
                    'pending' => 'معلقة',
                    'completed' => 'مكتملة',
                    'failed' => 'فاشلة',
                    'cancelled' => 'ملغاة',
                    'refunded' => 'مستردة'
                ];
            @endphp
            <span class="badge {{ $statusColors[$payment->status] ?? 'bg-secondary' }}">
                {{ $statusLabels[$payment->status] ?? $payment->status }}
            </span>
        </td>
        <td>
            <div class="btn-group">
                <a href="{{ route('payments.show', $payment->id) }}"
                   class="btn btn-sm btn-info" title="عرض">
                    <i class="fas fa-eye"></i>
                </a>

                @if($payment->status == 'completed')
                    <button type="button" class="btn btn-sm btn-warning"
                            onclick="confirmRefund({{ $payment->id }})" title="استرداد">
                        <i class="fas fa-undo"></i>
                    </button>
                @endif

                @if($payment->status == 'pending')
                    <button type="button" class="btn btn-sm btn-danger"
                            onclick="confirmCancel({{ $payment->id }})" title="إلغاء">
                        <i class="fas fa-times"></i>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="11" class="text-center py-5">
            <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
            <p class="text-muted mb-0">لا توجد مدفوعات</p>
        </td>
    </tr>
@endforelse
