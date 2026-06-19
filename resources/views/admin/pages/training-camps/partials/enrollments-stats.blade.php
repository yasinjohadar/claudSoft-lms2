@php
    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-inbox',
            'label' => 'إجمالي الطلبات',
            'value' => $stats['total'] ?? 0,
            'sub' => 'حسب الفلاتر الحالية',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-clock',
            'label' => 'قيد الانتظار',
            'value' => $stats['pending'] ?? 0,
            'sub' => 'بانتظار المراجعة',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'موافق عليها',
            'value' => $stats['approved'] ?? 0,
            'sub' => 'تسجيلات مقبولة',
        ],
        [
            'variant' => 'red',
            'icon' => 'fe-dollar-sign',
            'label' => 'غير مدفوعة',
            'value' => $stats['unpaid'] ?? 0,
            'sub' => 'بانتظار الدفع',
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in mb-0">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
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
