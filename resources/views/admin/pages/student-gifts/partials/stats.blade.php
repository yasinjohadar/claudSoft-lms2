@php
    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'ri-gift-line',
            'icon_set' => 'ri',
            'label' => 'إجمالي الهدايا',
            'value' => $stats['total'] ?? 0,
            'sub' => 'كل الهدايا المسجّلة',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-edit-3',
            'label' => 'مسودات',
            'value' => $stats['draft'] ?? 0,
            'sub' => 'بانتظار المنح',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'ممنوحة',
            'value' => $stats['granted'] ?? 0,
            'sub' => 'هدايا نشطة للطلاب',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-users',
            'label' => 'إجمالي المستلمين',
            'value' => $stats['recipients'] ?? 0,
            'sub' => 'سجلات المنح',
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in mb-0">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="{{ $card['icon_set'] ?? 'fe' }} {{ $card['icon'] }} admin-stats-card__icon"></i>
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
