@php
    $statCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-list',
            'label' => 'إجمالي المحاولات',
            'value' => $stats['total_attempts'] ?? 0,
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'محاولات ناجحة',
            'value' => $stats['passed_attempts'] ?? 0,
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-percent',
            'label' => 'متوسط النتيجة',
            'value' => round($stats['average_score'] ?? 0, 1),
            'suffix' => '%',
            'decimals' => true,
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-clock',
            'label' => 'محاولات مكتملة',
            'value' => $stats['completed_attempts'] ?? 0,
        ],
    ];
@endphp

<div class="row g-3 mb-4 student-quizzes-stats">
    @foreach ($statCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 student-quizzes-stagger" style="--stagger-delay: {{ $index * 60 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
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
