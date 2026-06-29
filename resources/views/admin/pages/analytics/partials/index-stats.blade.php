@php
    $avgMinutes = ($stats['average_time'] ?? 0) > 0 ? round($stats['average_time'] / 60, 1) : 0;

    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-file-text',
            'label' => 'إجمالي التقييمات',
            'value' => $stats['total_quizzes'] ?? 0,
            'sub' => 'اختبارات + وحدات أسئلة',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'المحاولات المكتملة',
            'value' => $stats['completed_attempts'] ?? 0,
            'sub' => 'من أصل ' . number_format($stats['total_attempts'] ?? 0) . ' محاولة',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-trending-up',
            'label' => 'متوسط الدرجات',
            'value' => round($stats['average_score'] ?? 0, 1),
            'suffix' => '%',
            'decimals' => true,
            'sub' => 'معدل النجاح: ' . number_format($stats['pass_rate'] ?? 0, 1) . '%',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-users',
            'label' => 'الطلاب النشطون',
            'value' => $stats['active_students'] ?? 0,
            'sub' => 'ضمن الفترة المحددة',
        ],
        [
            'variant' => 'yellow',
            'icon' => 'fe-percent',
            'label' => 'معدل الإكمال',
            'value' => round($stats['completion_rate'] ?? 0, 1),
            'suffix' => '%',
            'decimals' => true,
            'sub' => ($stats['in_progress'] ?? 0) . ' محاولة قيد التنفيذ',
        ],
        [
            'variant' => 'red',
            'icon' => 'fe-clock',
            'label' => 'متوسط وقت المحاولة',
            'value' => $avgMinutes,
            'suffix' => ' د',
            'decimals' => true,
            'sub' => number_format($stats['total_students'] ?? 0) . ' طالب مسجّل',
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in qa-index-animate mb-0">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item qa-index-animate" style="--stagger-delay: {{ $index * 60 }}ms">
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
