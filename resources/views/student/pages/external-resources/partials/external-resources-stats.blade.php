@php
    $statCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-folder',
            'label' => 'إجمالي الموارد',
            'value' => $stats['total'] ?? 0,
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-link',
            'label' => 'روابط خارجية',
            'value' => $stats['links'] ?? 0,
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-file',
            'label' => 'ملفات',
            'value' => $stats['files'] ?? 0,
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-filter',
            'label' => 'نتائج التصفية',
            'value' => $stats['filtered'] ?? 0,
        ],
    ];
@endphp

<div class="row g-3 mb-4 student-external-resources-stats">
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
                            @if(($card['label'] ?? '') === 'نتائج التصفية') id="external-resources-filtered-count" @endif>0</h3>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
