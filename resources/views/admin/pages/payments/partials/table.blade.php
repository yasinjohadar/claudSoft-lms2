@php
    $paymentStatusColors = [
        'pending' => 'bg-warning-transparent text-warning',
        'completed' => 'bg-success-transparent text-success',
        'failed' => 'bg-danger-transparent text-danger',
        'cancelled' => 'bg-secondary-transparent text-secondary',
        'refunded' => 'bg-info-transparent text-info',
    ];
    $paymentStatusLabels = [
        'pending' => 'بانتظار الموافقة',
        'completed' => 'مكتملة',
        'failed' => 'فاشلة',
        'cancelled' => 'ملغاة',
        'refunded' => 'مستردة',
    ];
    $invoiceStatusLabels = [
        'paid' => 'كامل',
        'partial' => 'جزئي',
        'issued' => 'غير مسدد',
        'draft' => 'غير مسدد',
    ];
    $invoiceStatusColors = [
        'paid' => 'bg-success-transparent text-success',
        'partial' => 'bg-warning-transparent text-warning',
        'issued' => 'bg-secondary-transparent text-secondary',
        'draft' => 'bg-secondary-transparent text-secondary',
    ];
@endphp

@forelse($payments as $payment)
    <tr>
        <td>
            <a href="{{ route('payments.show', $payment->id) }}" class="fw-semibold text-primary text-decoration-none">
                {{ $payment->payment_number }}
            </a>
        </td>
        <td>
            @if($payment->invoice)
                <a href="{{ route('invoices.show', $payment->invoice_id) }}" class="text-decoration-none">
                    {{ $payment->invoice->invoice_number }}
                </a>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>
            @if($payment->invoice && $payment->invoice->student)
                <strong class="d-block text-truncate">{{ $payment->invoice->student->name }}</strong>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>
            @php
                $campNames = collect(optional($payment->invoice)->items)
                    ->map(fn ($item) => optional(optional($item->campEnrollment)->camp)->name)
                    ->filter()
                    ->unique()
                    ->values();
            @endphp

            @if($campNames->isNotEmpty())
                <div class="d-flex flex-wrap gap-1">
                    @foreach($campNames as $campName)
                        <span class="group-show-chip group-show-chip--sm">{{ $campName }}</span>
                    @endforeach
                </div>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td><strong>${{ number_format($payment->amount, 2) }}</strong></td>
        <td>
            @if($payment->invoice && $payment->invoice->remaining_amount > 0)
                <span class="text-danger fw-semibold">${{ number_format($payment->invoice->remaining_amount, 2) }}</span>
            @elseif($payment->invoice)
                <span class="text-success fw-semibold">$0.00</span>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>
            @if($payment->invoice)
                @php $invoicePaymentStatus = $payment->invoice->status; @endphp
                <span class="badge {{ $invoiceStatusColors[$invoicePaymentStatus] ?? 'bg-secondary-transparent text-secondary' }}">
                    {{ $invoiceStatusLabels[$invoicePaymentStatus] ?? 'غير مسدد' }}
                </span>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td>
            @if($payment->paymentMethod)
                <span class="d-inline-flex align-items-center gap-1">
                    <i class="fe fe-credit-card text-muted"></i>
                    {{ $payment->paymentMethod->name }}
                </span>
            @else
                <span class="text-muted">—</span>
            @endif
        </td>
        <td><small>{{ $payment->payment_date->format('Y-m-d') }}</small></td>
        <td>
            <span class="badge {{ $paymentStatusColors[$payment->status] ?? 'bg-secondary-transparent text-secondary' }}">
                {{ $paymentStatusLabels[$payment->status] ?? $payment->status }}
            </span>
            @if($payment->status === 'pending' && $payment->has_receipt)
                <span class="badge bg-info-transparent text-info ms-1" title="طلب من الطالب">
                    <i class="fe fe-upload"></i>
                </span>
            @endif
        </td>
        <td>
            <div class="d-flex flex-wrap gap-1">
                <a href="{{ route('payments.show', $payment->id) }}"
                   class="btn btn-sm btn-info-light" title="عرض">
                    <i class="fe fe-eye"></i>
                </a>

                @if($payment->invoice_id && $payment->invoice && $payment->invoice->remaining_amount > 0)
                    <a href="{{ route('payments.create', ['invoice_id' => $payment->invoice_id]) }}"
                       class="btn btn-sm btn-success-light" title="استكمال المبلغ الناقص">
                        <i class="fe fe-plus-circle"></i>
                    </a>
                @endif

                @if($payment->status == 'completed')
                    <button type="button" class="btn btn-sm btn-warning-light"
                            onclick="confirmRefund({{ $payment->id }})" title="استرداد">
                        <i class="fe fe-rotate-ccw"></i>
                    </button>
                @endif

                @if($payment->status == 'pending')
                    <button type="button" class="btn btn-sm btn-danger-light"
                            onclick="confirmCancel({{ $payment->id }})" title="إلغاء">
                        <i class="fe fe-x"></i>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="11">
            <div class="group-show-empty py-5">
                <i class="fe fe-dollar-sign group-show-empty__icon"></i>
                <h5 class="group-show-empty__title">لا توجد مدفوعات</h5>
                <p class="group-show-empty__desc mb-0">جرّب تعديل الفلاتر أو سجّل دفعة جديدة.</p>
            </div>
        </td>
    </tr>
@endforelse
