@php
    $statCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-layers',
            'label' => 'إجمالي المحتوى',
            'value' => $stats['total_modules'],
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'تم الإكمال',
            'value' => $stats['completed_modules'],
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-clock',
            'label' => 'الوقت المستغرق',
            'display_text' => $stats['time_spent'] . ' دقيقة',
            'text_only' => true,
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-trending-up',
            'label' => 'متوسط الدرجات',
            'value' => round($stats['average_score'], 1),
            'suffix' => '%',
            'decimals' => true,
        ],
    ];
@endphp

<div class="row g-3 mb-4 student-progress-show-stats">
    @foreach ($statCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 student-my-courses-stagger" style="--stagger-delay: {{ $index * 60 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        @if(!empty($card['text_only']))
                            <h3 class="admin-stats-card__value mb-0">{{ $card['display_text'] ?? '' }}</h3>
                        @else
                            <h3 class="admin-stats-card__value mb-0"
                                data-countup="{{ $card['value'] }}"
                                @if(!empty($card['suffix'])) data-countup-suffix="{{ $card['suffix'] }}" @endif
                                @if(!empty($card['decimals'])) data-countup-decimals="1" @endif>0</h3>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
