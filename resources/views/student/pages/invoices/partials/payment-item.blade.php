@php
    $statusMap = [
        'completed' => ['class' => 'success', 'icon' => 'fe-check-circle', 'label' => 'مكتملة'],
        'pending' => ['class' => 'warning', 'icon' => 'fe-clock', 'label' => 'قيد المراجعة'],
        'failed' => ['class' => 'danger', 'icon' => 'fe-x-circle', 'label' => 'مرفوضة'],
        'cancelled' => ['class' => 'secondary', 'icon' => 'fe-slash', 'label' => 'ملغاة'],
        'refunded' => ['class' => 'secondary', 'icon' => 'fe-rotate-ccw', 'label' => 'مستردة'],
    ];
    $status = $statusMap[$payment->status] ?? ['class' => 'secondary', 'icon' => 'fe-help-circle', 'label' => $payment->status];
@endphp

<div class="student-invoice-payment-item">
    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
        <span class="student-invoice-payment-item__number">{{ $payment->payment_number }}</span>
        <div class="text-end">
            <strong class="text-success d-block">${{ number_format($payment->amount, 2) }}</strong>
            <span class="badge bg-{{ $status['class'] }}-transparent fs-10 mt-1">
                <i class="fe {{ $status['icon'] }} me-1"></i>{{ $status['label'] }}
            </span>
        </div>
    </div>
    <p class="student-invoice-payment-item__meta mb-1">
        <i class="fe fe-calendar me-1"></i>{{ $payment->payment_date->format('Y-m-d') }}
    </p>
    @if($payment->paymentMethod)
        <p class="student-invoice-payment-item__meta mb-1">
            <i class="fe fe-credit-card me-1"></i>{{ $payment->paymentMethod->name }}
        </p>
    @endif
    @if($payment->status === 'failed' && $payment->rejection_reason)
        <p class="text-danger small mb-1">
            <i class="fe fe-alert-circle me-1"></i>{{ $payment->rejection_reason }}
        </p>
    @endif
    @if($payment->has_receipt)
        <a href="{{ route('student.payments.receipt', $payment->id) }}"
           class="btn btn-sm btn-outline-primary rounded-pill mt-1"
           target="_blank">
            <i class="fe fe-paperclip me-1"></i>عرض الإيصال
        </a>
    @endif
</div>
