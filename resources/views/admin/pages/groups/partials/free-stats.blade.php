@php
    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-layers',
            'label' => 'إجمالي المجموعات المجانية',
            'value' => $totalGroups,
            'sub' => 'غير مصنّفة كمعسكر',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'مجموعات نشطة',
            'value' => $activeGroups,
            'sub' => 'نشطة حالياً',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-users',
            'label' => 'إجمالي الأعضاء',
            'value' => $totalMembers,
            'sub' => 'في المجموعات المجانية',
        ],
        [
            'variant' => 'gold',
            'icon' => 'fe-flag',
            'label' => 'المجموعات المدفوعة',
            'value' => $otherTypeGroups ?? 0,
            'sub' => 'للمقارنة فقط',
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in mb-4">
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
