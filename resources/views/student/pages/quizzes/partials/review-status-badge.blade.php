@if($attempt->is_completed)
    @if($attempt->passed)
        <span class="badge bg-success-transparent">
            <i class="fe fe-check-circle me-1"></i>ناجح
        </span>
    @else
        <span class="badge bg-danger-transparent">
            <i class="fe fe-x-circle me-1"></i>راسب
        </span>
    @endif
@elseif($attempt->status == 'in_progress')
    <span class="badge bg-warning-transparent">
        <i class="fe fe-loader me-1"></i>قيد التنفيذ
    </span>
@else
    <span class="badge bg-secondary-transparent">
        <i class="fe fe-clock me-1"></i>{{ $attempt->status }}
    </span>
@endif
