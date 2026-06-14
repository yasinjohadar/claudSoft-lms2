@php
    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-clipboard',
            'label' => 'إجمالي الواجبات',
            'value' => $totalAssignments ?? 0,
            'sub' => 'في جميع الكورسات',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'واجبات منشورة',
            'value' => $publishedAssignments ?? 0,
            'sub' => 'متاحة للطلاب',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-edit-3',
            'label' => 'مسودات',
            'value' => $draftAssignments ?? 0,
            'sub' => 'غير منشورة بعد',
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in assignments-page-animate mb-0">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 dashboard-stagger-item assignments-page-animate" style="--stagger-delay: {{ $index * 70 }}ms">
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
