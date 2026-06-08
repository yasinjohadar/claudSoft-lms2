@php
    $availableSeats = $trainingCamp->availableSeats();
    $statCards = [
        [
            'variant' => 'green',
            'icon' => 'fe-dollar-sign',
            'label' => 'سعر المعسكر',
            'value' => round($trainingCamp->price, 2),
            'prefix' => '$',
            'decimals' => true,
            'sub' => 'رسوم التسجيل',
            'countup' => true,
        ],
        [
            'variant' => 'blue',
            'icon' => 'fe-clock',
            'label' => 'مدة المعسكر',
            'value' => $trainingCamp->duration_days,
            'suffix' => ' يوم',
            'sub' => 'من البداية للنهاية',
            'countup' => true,
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-users',
            'label' => 'المقاعد المتبقية',
            'value' => $availableSeats ?? '∞',
            'sub' => $trainingCamp->max_participants
                ? $trainingCamp->current_participants . ' / ' . $trainingCamp->max_participants . ' مشارك'
                : 'بدون حد أقصى',
            'countup' => $availableSeats !== null,
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-flag',
            'label' => 'حالة المعسكر',
            'value' => $campStatusLabel,
            'sub' => $campStatus === 'ongoing' ? 'يمكنك التسجيل الآن' : ($campStatus === 'upcoming' ? 'يبدأ قريباً' : 'انتهى'),
            'countup' => false,
        ],
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
                        @if($card['countup'])
                            <h3 class="admin-stats-card__value mb-1"
                                data-countup="{{ $card['value'] }}"
                                @if(!empty($card['prefix'])) data-countup-prefix="{{ $card['prefix'] }}" @endif
                                @if(!empty($card['suffix'])) data-countup-suffix="{{ $card['suffix'] }}" @endif
                                @if(!empty($card['decimals'])) data-countup-decimals="2" @endif>0</h3>
                        @else
                            <h3 class="admin-stats-card__value admin-stats-card__value--text mb-1">{{ $card['value'] }}</h3>
                        @endif
                        <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
