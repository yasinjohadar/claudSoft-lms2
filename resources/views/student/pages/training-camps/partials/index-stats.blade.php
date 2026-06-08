@php
    $statCards = [
        ['variant' => 'blue', 'icon' => 'fe-flag', 'label' => 'معسكرات متاحة', 'value' => $stats['total'] ?? 0, 'sub' => 'حسب الفلاتر الحالية'],
        ['variant' => 'cyan', 'icon' => 'fe-calendar', 'label' => 'معسكرات قادمة', 'value' => $stats['upcoming'] ?? 0, 'sub' => 'لم تبدأ بعد'],
        ['variant' => 'green', 'icon' => 'fe-play-circle', 'label' => 'معسكرات جارية', 'value' => $stats['ongoing'] ?? 0, 'sub' => 'جارية الآن'],
        ['variant' => 'orange', 'icon' => 'fe-star', 'label' => 'معسكرات مميزة', 'value' => $stats['featured'] ?? 0, 'sub' => 'موصى بها'],
    ];
@endphp

<div class="row g-3 dashboard-fade-in mb-4">
    @foreach ($statCards as $index => $card)
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
