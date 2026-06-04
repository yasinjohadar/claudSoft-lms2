@php
    $pct = (float) ($sectionData['percentage'] ?? 0);
@endphp

@if($pct >= 100)
    <span class="badge bg-success-transparent">
        <i class="fe fe-check-circle me-1"></i>مكتمل
    </span>
@elseif($pct > 0)
    <span class="badge bg-warning-transparent">
        <i class="fe fe-play me-1"></i>قيد التقدم
    </span>
@else
    <span class="badge bg-secondary-transparent">
        <i class="fe fe-minus-circle me-1"></i>لم يبدأ
    </span>
@endif
