@php
    $statCards = [
        [
            'variant' => 'blue',
            'icon' => 'ri-file-list-3-line',
            'label' => 'إجمالي المعاملات',
            'value' => $stats['total_transactions'] ?? 0,
            'sub' => 'كل السجل',
        ],
        [
            'variant' => 'green',
            'icon' => 'ri-arrow-up-circle-line',
            'label' => 'إجمالي المكتسب',
            'value' => $stats['total_earned'] ?? 0,
            'sub' => 'نقاط مكتسبة',
            'prefix' => '+',
        ],
        [
            'variant' => 'orange',
            'icon' => 'ri-arrow-down-circle-line',
            'label' => 'إجمالي المستهلك',
            'value' => $stats['total_spent'] ?? 0,
            'sub' => 'نقاط مصروفة',
            'prefix' => '-',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'ri-calendar-check-line',
            'label' => 'هذا الشهر',
            'value' => $stats['this_month_earned'] ?? 0,
            'sub' => now()->translatedFormat('F Y'),
            'prefix' => '+',
        ],
    ];
@endphp

<div class="row g-3 mb-4 student-points-stats">
    @foreach ($statCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 student-my-courses-stagger" style="--stagger-delay: {{ $index * 60 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="ri {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        <h3 class="admin-stats-card__value mb-0">
                            {{ ($card['prefix'] ?? '') . number_format($card['value']) }}
                        </h3>
                        @if (! empty($card['sub']))
                            <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
