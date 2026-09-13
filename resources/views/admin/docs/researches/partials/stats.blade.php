@php
    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-search',
            'label' => 'إجمالي الأبحاث',
            'value' => $stats['total'] ?? 0,
            'sub' => 'مجموعات التوثيق',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'أبحاث مفعّلة',
            'value' => $stats['active'] ?? 0,
            'sub' => 'متاحة للإسناد',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-file-text',
            'label' => 'صفحات مُسنَدة',
            'value' => $stats['assigned_pages'] ?? 0,
            'sub' => 'داخل أبحاث',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-alert-circle',
            'label' => 'صفحات بلا بحث',
            'value' => $stats['unassigned_pages'] ?? 0,
            'sub' => 'بانتظار الإسناد',
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
