@php
    $statCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-book',
            'label' => 'إجمالي الكورسات',
            'value' => $stats['total_courses'],
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-play',
            'label' => 'كورسات نشطة',
            'value' => $stats['active_courses'],
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-check-circle',
            'label' => 'كورسات مكتملة',
            'value' => $stats['completed_courses'],
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-trending-up',
            'label' => 'متوسط التقدم',
            'value' => round($stats['average_progress']),
            'suffix' => '%',
        ],
    ];
@endphp

<div class="row g-3 mb-4 student-my-courses-stats">
    @foreach ($statCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 student-my-courses-stagger" style="--stagger-delay: {{ $index * 60 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        <h3 class="admin-stats-card__value mb-0"
                            data-countup="{{ $card['value'] }}"
                            @if(!empty($card['suffix'])) data-countup-suffix="{{ $card['suffix'] }}" @endif>0</h3>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
