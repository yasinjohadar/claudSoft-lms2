@php
    $statCards = [
        [
            'variant' => 'blue',
            'icon' => 'ri-star-line',
            'label' => 'إجمالي النقاط',
            'value' => $totalPoints ?? 0,
        ],
        [
            'variant' => 'green',
            'icon' => 'ri-wallet-3-line',
            'label' => 'النقاط المتاحة',
            'value' => $availablePoints ?? 0,
        ],
        [
            'variant' => 'orange',
            'icon' => 'ri-shopping-cart-line',
            'label' => 'النقاط المستهلكة',
            'value' => $spentPoints ?? 0,
        ],
        [
            'variant' => 'cyan',
            'icon' => 'ri-calendar-check-line',
            'label' => 'كسب هذا الشهر',
            'value' => $monthlyEarned ?? 0,
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
                        <h3 class="admin-stats-card__value mb-0" data-countup="{{ $card['value'] }}">0</h3>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
