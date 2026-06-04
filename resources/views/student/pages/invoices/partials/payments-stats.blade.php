@php
    $statCards = [
        [
            'variant' => 'green',
            'icon' => 'fe-file-text',
            'label' => 'عدد المدفوعات',
            'value' => $stats['total_payments'],
        ],
        [
            'variant' => 'blue',
            'icon' => 'fe-dollar-sign',
            'label' => 'إجمالي المدفوع',
            'value' => round($stats['total_paid'], 2),
            'prefix' => '$',
            'decimals' => true,
        ],
    ];
@endphp

<div class="row g-3 mb-4 student-payments-stats">
    @foreach ($statCards as $index => $card)
        <div class="col-md-6 student-my-courses-stagger" style="--stagger-delay: {{ $index * 60 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        <h3 class="admin-stats-card__value mb-0"
                            data-countup="{{ $card['value'] }}"
                            @if(!empty($card['prefix'])) data-countup-prefix="{{ $card['prefix'] }}" @endif
                            @if(!empty($card['decimals'])) data-countup-decimals="2" @endif>0</h3>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
