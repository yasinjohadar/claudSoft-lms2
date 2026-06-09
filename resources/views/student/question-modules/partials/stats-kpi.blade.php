@php
    $statCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-list',
            'label' => 'إجمالي المحاولات',
            'value' => $totalAttempts,
            'sub' => 'محاولات مكتملة',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-percent',
            'label' => 'متوسط الدرجات',
            'value' => round($averageScore, 1),
            'suffix' => '%',
            'decimals' => true,
            'sub' => 'من جميع المحاولات',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'محاولات ناجحة',
            'value' => $passedAttempts,
            'sub' => ($totalAttempts > 0 ? round(($passedAttempts / $totalAttempts) * 100, 1) : 0) . '% معدل نجاح',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-clock',
            'label' => 'ساعات التدريب',
            'value' => $totalHours,
            'suffix' => ' س',
            'decimals' => true,
            'sub' => 'وقت التدريب الإجمالي',
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
                        <h3 class="admin-stats-card__value mb-1"
                            data-countup="{{ $card['value'] }}"
                            @if(!empty($card['suffix'])) data-countup-suffix="{{ $card['suffix'] }}" @endif
                            @if(!empty($card['decimals'])) data-countup-decimals="1" @endif>0</h3>
                        <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
