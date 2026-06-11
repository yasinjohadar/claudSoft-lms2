@php
    $statCards = [
        [
            'variant' => 'blue',
            'icon' => 'ri-trophy-line',
            'label' => 'اللوحات المتاحة',
            'value' => $indexStats['total_boards'] ?? 0,
        ],
        [
            'variant' => 'green',
            'icon' => 'ri-user-star-line',
            'label' => 'لوحات دخلتها',
            'value' => $indexStats['ranked_boards'] ?? 0,
        ],
        [
            'variant' => 'orange',
            'icon' => 'ri-medal-line',
            'label' => 'أفضل ترتيب',
            'value' => isset($indexStats['best_rank']) ? '#'.$indexStats['best_rank'] : '—',
            'raw' => true,
        ],
        [
            'variant' => 'cyan',
            'icon' => 'ri-group-line',
            'label' => 'إجمالي المشاركين',
            'value' => $indexStats['total_participants'] ?? 0,
        ],
    ];
@endphp

<div class="row g-3 mb-4 student-leaderboards-index-stats">
    @foreach ($statCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 student-my-courses-stagger" style="--stagger-delay: {{ $index * 60 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="ri {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        @if (!empty($card['raw']))
                            <h3 class="admin-stats-card__value mb-0">{{ $card['value'] }}</h3>
                        @else
                            <h3 class="admin-stats-card__value mb-0" data-countup="{{ $card['value'] }}">0</h3>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
