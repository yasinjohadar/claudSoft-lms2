@php
    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-folder',
            'label' => 'إجمالي الأقسام',
            'value' => $stats['total'] ?? 0,
            'sub' => 'تصنيفات التوثيق',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'أقسام مفعّلة',
            'value' => $stats['active'] ?? 0,
            'sub' => 'ظاهرة للمستخدمين',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-code',
            'label' => 'لغات / تقنيات',
            'value' => $stats['technology'] ?? 0,
            'sub' => 'نوع technology',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-file-text',
            'label' => 'صفحات التوثيق',
            'value' => $stats['pages'] ?? 0,
            'sub' => 'إجمالي المقالات',
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in doc-cat-animate mb-4">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item doc-cat-animate" style="--stagger-delay: {{ $index * 70 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        <h3 class="admin-stats-card__value mb-1">{{ number_format($card['value']) }}</h3>
                        <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
