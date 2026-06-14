@php
    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-star',
            'label' => 'إجمالي التقييمات',
            'value' => $stats['total'] ?? 0,
            'sub' => 'جميع تقييمات المنصة',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'مقبولة',
            'value' => $stats['active'] ?? 0,
            'sub' => 'ظاهرة للزوار',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-clock',
            'label' => 'في الانتظار',
            'value' => $stats['inactive'] ?? 0,
            'sub' => 'بانتظار الموافقة',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-heart',
            'label' => 'مميزة',
            'value' => $stats['featured'] ?? 0,
            'sub' => 'معروضة في الصفحة الرئيسية',
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in platform-reviews-page-animate mb-0">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item platform-reviews-page-animate" style="--stagger-delay: {{ $index * 70 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        <h3 class="admin-stats-card__value mb-1" data-countup="{{ $card['value'] }}">0</h3>
                        <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
