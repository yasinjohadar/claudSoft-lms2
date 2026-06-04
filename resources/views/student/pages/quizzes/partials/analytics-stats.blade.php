@php
    $statCards = [
        ['variant' => 'blue', 'icon' => 'fe-percent', 'label' => 'متوسط النتيجة', 'value' => $overallMetrics['average_score'] ?? 0, 'suffix' => '%', 'decimals' => true],
        ['variant' => 'green', 'icon' => 'fe-check-circle', 'label' => 'معدل النجاح', 'value' => $overallMetrics['pass_rate'] ?? 0, 'suffix' => '%', 'decimals' => true],
        ['variant' => 'cyan', 'icon' => 'fe-clock', 'label' => 'متوسط الوقت', 'value' => $overallMetrics['average_time'] ?? 0, 'suffix' => ' د'],
        ['variant' => 'orange', 'icon' => 'fe-list', 'label' => 'إجمالي المحاولات', 'value' => $overallMetrics['total_attempts'] ?? 0],
    ];
@endphp

<div class="row g-3 mb-4">
    @foreach($statCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 student-quizzes-stagger" style="--stagger-delay: {{ $index * 60 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        <h3 class="admin-stats-card__value mb-0"
                            data-countup="{{ $card['value'] }}"
                            @if(!empty($card['suffix'])) data-countup-suffix="{{ $card['suffix'] }}" @endif
                            @if(!empty($card['decimals'])) data-countup-decimals="1" @endif>0</h3>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
