@php
    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-smartphone',
            'label' => 'إجمالي الأجهزة',
            'value' => $stats['total'] ?? 0,
            'sub' => 'كل الأجهزة المسجّلة',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-shield',
            'label' => 'الأجهزة الموثوقة',
            'value' => $stats['trusted'] ?? 0,
            'sub' => 'معتمدة للاستخدام',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-slash',
            'label' => 'الأجهزة المحظورة',
            'value' => $stats['blocked'] ?? 0,
            'sub' => 'محظورة من الدخول',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-check-circle',
            'label' => 'الأجهزة النشطة',
            'value' => $stats['active'] ?? 0,
            'sub' => 'غير محظورة',
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in ud-page-animate mb-0">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item ud-page-animate" style="--stagger-delay: {{ $index * 70 }}ms">
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
