@php
    $statCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-file-text',
            'label' => 'إجمالي المحاولات',
            'value' => $stats['total_attempts'] ?? 0,
            'sub' => ($stats['completed_attempts'] ?? 0) . ' مكتملة',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-trending-up',
            'label' => 'متوسط الدرجات',
            'value' => round($stats['average_score'] ?? 0, 1),
            'suffix' => '%',
            'decimals' => true,
            'sub' => 'أعلى: ' . number_format($stats['highest_score'] ?? 0, 1) . '%',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-percent',
            'label' => 'معدل النجاح',
            'value' => round($stats['pass_rate'] ?? 0, 1),
            'suffix' => '%',
            'decimals' => true,
            'sub' => 'أقل: ' . number_format($stats['lowest_score'] ?? 0, 1) . '%',
        ],
        [
            'variant' => 'yellow',
            'icon' => 'fe-clock',
            'label' => 'متوسط الوقت',
            'value' => $stats['average_time'] ? round($stats['average_time'] / 60, 1) : 0,
            'suffix' => ' د',
            'decimals' => true,
            'sub' => ($stats['in_progress'] ?? 0) . ' قيد التنفيذ',
        ],
    ];
@endphp

<div class="row g-3 mb-4">
    @foreach ($statCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 student-quizzes-stagger" style="--stagger-delay: {{ $index * 60 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        <h3 class="admin-stats-card__value mb-1"
                            data-countup="{{ $card['value'] }}"
                            @if(!empty($card['suffix'])) data-countup-suffix="{{ $card['suffix'] }}" @endif
                            @if(!empty($card['decimals'])) data-countup-decimals="1" @endif>0</h3>
                        @if(!empty($card['sub']))
                            <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
