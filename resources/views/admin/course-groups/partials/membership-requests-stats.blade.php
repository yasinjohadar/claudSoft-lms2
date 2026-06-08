@php
    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-inbox',
            'label' => 'إجمالي الطلبات',
            'value' => $requests->total(),
            'sub' => 'حسب الفلتر الحالي',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-clock',
            'label' => 'قيد المراجعة',
            'value' => $pendingCount ?? 0,
            'sub' => 'طلبات بانتظار القرار',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-users',
            'label' => 'أعضاء المجموعة',
            'value' => $group->members_count ?? 0,
            'sub' => $group->max_members ? 'الحد الأقصى: ' . $group->max_members : 'بدون حد أقصى',
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in mb-4">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
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
