@php
    $pct = (int) round($enrollment->completion_percentage ?? 0);
    $pct = max(0, min(100, $pct));
@endphp
<div class="admin-enrollments-progress" title="نسبة الإنجاز: {{ $pct }}%">
    <div class="admin-enrollments-progress__bar" role="progressbar"
         aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"
         aria-label="نسبة الإنجاز">
        <div class="admin-enrollments-progress__fill {{ $pct >= 100 ? 'is-complete' : '' }}"
             style="width: {{ $pct }}%"></div>
    </div>
    <span class="admin-enrollments-progress__label {{ $pct >= 100 ? 'is-complete' : '' }}">{{ $pct }}%</span>
</div>
