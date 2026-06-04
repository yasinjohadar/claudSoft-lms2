@php
    $statusMap = [
        'issued' => ['class' => 'info', 'icon' => 'fe-send', 'label' => 'صادرة'],
        'partial' => ['class' => 'warning', 'icon' => 'fe-pie-chart', 'label' => 'جزئياً'],
        'paid' => ['class' => 'success', 'icon' => 'fe-check-circle', 'label' => 'مدفوعة'],
        'cancelled' => ['class' => 'danger', 'icon' => 'fe-x-circle', 'label' => 'ملغاة'],
        'draft' => ['class' => 'secondary', 'icon' => 'fe-edit', 'label' => 'مسودة'],
        'refunded' => ['class' => 'secondary', 'icon' => 'fe-rotate-ccw', 'label' => 'مستردة'],
    ];
    $status = $statusMap[$invoice->status] ?? ['class' => 'secondary', 'icon' => 'fe-file', 'label' => $invoice->status];
@endphp

<span class="badge bg-{{ $status['class'] }}-transparent">
    <i class="fe {{ $status['icon'] }} me-1"></i>{{ $status['label'] }}
</span>

@if($invoice->is_overdue)
    <span class="badge bg-danger-transparent">
        <i class="fe fe-alert-circle me-1"></i>متأخرة
    </span>
@endif
