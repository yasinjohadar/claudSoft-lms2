@php
    $total = $scoreDistribution ? array_sum($scoreDistribution) : 0;
    $rangeClasses = ['r0', 'r1', 'r2', 'r3', 'r4'];
    $rangeIndex = 0;
@endphp

<div class="quiz-analytics-grade-list">
    @if($scoreDistribution && count($scoreDistribution) > 0)
        @foreach($scoreDistribution as $range => $count)
            @php
                $pct = $total > 0 ? ($count / $total) * 100 : 0;
                $rowClass = $rangeClasses[$rangeIndex] ?? 'r2';
                $rangeIndex++;
            @endphp
            <div class="quiz-analytics-grade-row quiz-analytics-grade-row--{{ $rowClass }}" style="--qa-bar-width: {{ $pct }}%;">
                <span class="quiz-analytics-grade-row__label">{{ $range }}%</span>
                <div class="quiz-analytics-grade-row__bar-wrap">
                    <div class="quiz-analytics-grade-row__bar" title="{{ number_format($pct, 1) }}%"></div>
                </div>
                <span class="quiz-analytics-grade-row__count">{{ $count }}</span>
            </div>
        @endforeach
    @else
        <div class="quiz-analytics-empty">
            <div><i class="fe fe-bar-chart-2 d-block"></i></div>
            <p class="mb-0">لا توجد بيانات متاحة</p>
        </div>
    @endif
</div>
