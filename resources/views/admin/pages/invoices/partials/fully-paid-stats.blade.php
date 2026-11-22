@php
    $kpiCards = [
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'فواتير مسددة بالكامل',
            'value' => $stats['total_count'] ?? 0,
            'sub' => 'حسب الفلاتر الحالية',
        ],
        [
            'variant' => 'blue',
            'icon' => 'fe-dollar-sign',
            'label' => 'إجمالي المبالغ المحصّلة',
            'value' => round($stats['paid_amount'] ?? 0, 2),
            'prefix' => '$',
            'decimals' => true,
            'sub' => 'مجموع قيم هذه الفواتير',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-users',
            'label' => 'عدد الطلاب',
            'value' => $stats['students_count'] ?? 0,
            'sub' => 'طلاب سددوا فواتيرهم كاملة',
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in mb-0">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-4 col-md-6 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        <h3 class="admin-stats-card__value mb-1"
                            data-countup="{{ $card['value'] }}"
                            @if(!empty($card['prefix'])) data-countup-prefix="{{ $card['prefix'] }}" @endif
                            @if(!empty($card['decimals'])) data-countup-decimals="2" @endif>0</h3>
                        <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
