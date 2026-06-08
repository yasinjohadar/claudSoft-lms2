@php
    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-users',
            'label' => 'إجمالي المستخدمين',
            'value' => $stats['total'] ?? 0,
            'sub' => 'حسب الفلاتر الحالية',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'مستخدمون نشطون',
            'value' => $stats['active'] ?? 0,
            'sub' => 'حسابات مفعّلة',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-wifi',
            'label' => 'متصلون الآن',
            'value' => $stats['online'] ?? 0,
            'sub' => 'جلسات نشطة',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-book-open',
            'label' => 'طلاب',
            'value' => $stats['students'] ?? 0,
            'sub' => 'دور student',
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
