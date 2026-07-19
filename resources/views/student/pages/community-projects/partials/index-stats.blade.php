@php
    $statCards = [
        ['variant' => 'blue', 'icon' => 'fe-globe', 'label' => 'مشاريع منشورة', 'value' => $stats['total'] ?? 0, 'sub' => 'في المعرض'],
        ['variant' => 'cyan', 'icon' => 'fe-calendar', 'label' => 'هذا الشهر', 'value' => $stats['this_month'] ?? 0, 'sub' => 'منشورات جديدة'],
        ['variant' => 'green', 'icon' => 'fe-monitor', 'label' => 'بعرض تجريبي', 'value' => $stats['with_demo'] ?? 0, 'sub' => 'روابط Demo'],
        ['variant' => 'orange', 'icon' => 'fe-layers', 'label' => 'تحديات مشاركة', 'value' => $stats['challenges'] ?? 0, 'sub' => 'لها مشاريع معروضة'],
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
