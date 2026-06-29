@php
    $avgDuration = $stats['avg_duration']
        ? gmdate('H:i:s', (int) $stats['avg_duration'])
        : '—';

    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-layers',
            'label' => 'إجمالي الجلسات',
            'value' => $stats['total'] ?? 0,
            'sub' => 'كل الجلسات المسجّلة',
            'countup' => true,
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-radio',
            'label' => 'الجلسات النشطة',
            'value' => $stats['active'] ?? 0,
            'sub' => 'متصلة حالياً',
            'countup' => true,
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-check-circle',
            'label' => 'الجلسات المكتملة',
            'value' => $stats['completed'] ?? 0,
            'sub' => 'انتهت بشكل طبيعي',
            'countup' => true,
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-clock',
            'label' => 'متوسط المدة',
            'value' => $avgDuration,
            'sub' => 'لكل جلسة',
            'countup' => false,
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in us-page-animate mb-0">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item us-page-animate" style="--stagger-delay: {{ $index * 70 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        @if($card['countup'])
                            <h3 class="admin-stats-card__value mb-1" data-countup="{{ $card['value'] }}">0</h3>
                        @else
                            <h3 class="admin-stats-card__value mb-1">{{ $card['value'] }}</h3>
                        @endif
                        <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
