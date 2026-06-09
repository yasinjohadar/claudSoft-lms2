@php
    $gems = $userStats->available_gems ?? ($userStats->additional_stats['gems'] ?? 0);

    $statCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-star',
            'label' => 'النقاط',
            'value' => $dashboard['points']['total'] ?? 0,
            'sub' => 'إجمالي نقاطك',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-trending-up',
            'label' => 'المستوى',
            'value' => $levelInfo['current_level'] ?? 1,
            'sub' => ($levelInfo['total_xp'] ?? 0) . ' XP',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-award',
            'label' => 'الجواهر',
            'value' => $gems,
            'sub' => 'رصيد الجواهر',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-zap',
            'label' => 'السلسلة',
            'value' => $streakInfo['current_streak'] ?? 0,
            'sub' => 'أيام متتالية',
        ],
    ];
@endphp

<div class="row g-3 mb-4 dashboard-fade-in">
    @foreach ($statCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $index * 60 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }} h-100">
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
