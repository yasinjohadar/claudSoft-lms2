@php
    $statCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-users',
            'label' => 'إجمالي المجموعات',
            'value' => $stats['total'] ?? 0,
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-user-plus',
            'label' => 'متاح للانضمام',
            'value' => $stats['available'] ?? 0,
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-clock',
            'label' => 'طلباتي المعلقة',
            'value' => $stats['pending'] ?? 0,
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-filter',
            'label' => 'نتائج التصفية',
            'value' => $stats['filtered'] ?? 0,
            'id' => 'student-groups-filtered-count',
        ],
    ];
@endphp

<div class="row g-3 mb-4 student-groups-stats">
    @foreach ($statCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 student-my-courses-stagger" style="--stagger-delay: {{ $index * 60 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        <h3 class="admin-stats-card__value mb-0"
                            data-countup="{{ $card['value'] }}"
                            @if(!empty($card['id'])) id="{{ $card['id'] }}" @endif>0</h3>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
