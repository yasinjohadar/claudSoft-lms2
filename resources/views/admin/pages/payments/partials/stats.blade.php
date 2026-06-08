@php
    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-dollar-sign',
            'label' => 'إجمالي المدفوعات',
            'value' => round($stats['completed_amount'] ?? 0, 2),
            'prefix' => '$',
            'decimals' => true,
            'sub' => ($stats['completed_count'] ?? 0) . ' دفعة',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-clock',
            'label' => 'دفعات معلقة',
            'value' => round($stats['pending_amount'] ?? 0, 2),
            'prefix' => '$',
            'decimals' => true,
            'sub' => ($stats['pending_count'] ?? 0) . ' دفعة',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-x-circle',
            'label' => 'دفعات ملغاة',
            'value' => round($stats['cancelled_amount'] ?? 0, 2),
            'prefix' => '$',
            'decimals' => true,
            'sub' => ($stats['cancelled_count'] ?? 0) . ' دفعة',
        ],
        [
            'variant' => 'blue',
            'icon' => 'fe-rotate-ccw',
            'label' => 'مبالغ مستردة',
            'value' => round($stats['refunded_amount'] ?? 0, 2),
            'prefix' => '$',
            'decimals' => true,
            'sub' => ($stats['refunded_count'] ?? 0) . ' دفعة',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'القيمة المسددة',
            'value' => round($stats['paid_amount'] ?? 0, 2),
            'prefix' => '$',
            'decimals' => true,
            'sub' => 'إجمالي المبالغ المحصّلة',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-hourglass',
            'label' => 'القيمة المتبقية',
            'value' => round($stats['remaining_amount'] ?? 0, 2),
            'prefix' => '$',
            'decimals' => true,
            'sub' => 'على الفواتير المرتبطة',
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in mb-0">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $index * 60 }}ms">
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
