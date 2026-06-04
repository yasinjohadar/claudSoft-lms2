@php
    $pct = (float) ($sectionData['percentage'] ?? 0);
    $isComplete = $pct >= 100;
@endphp

<div class="student-progress-section-result">
    <div class="student-progress-section-result__track">
        <div class="student-progress-section-result__bar {{ $isComplete ? 'is-complete' : '' }}"
             style="width: {{ max(0, min(100, $pct)) }}%"
             role="progressbar"
             aria-valuenow="{{ $pct }}"
             aria-valuemin="0"
             aria-valuemax="100"></div>
    </div>
    <span class="student-progress-section-result__pct">{{ number_format($pct, 0) }}%</span>
</div>
