@php
    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-help-circle',
            'label' => 'إجمالي الأسئلة',
            'value' => $stats['total'] ?? 0,
            'sub' => 'حسب الفلاتر الحالية',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'أسئلة نشطة',
            'value' => $stats['active'] ?? 0,
            'sub' => 'ضمن نتائج البحث',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-layers',
            'label' => 'أنواع الأسئلة',
            'value' => $stats['types'] ?? 0,
            'sub' => 'أنواع مدعومة',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-book-open',
            'label' => 'الكورسات',
            'value' => $stats['courses'] ?? 0,
            'sub' => 'كورسات منشورة',
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in qb-page-animate mb-0">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item qb-page-animate" style="--stagger-delay: {{ $index * 70 }}ms">
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
