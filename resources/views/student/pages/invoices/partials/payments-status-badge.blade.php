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

<span class="badge bg-{{ $status['class'] }}-transparent">
    <i class="fe {{ $status['icon'] }} me-1"></i>{{ $status['label'] }}
</span>
