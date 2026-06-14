@php
    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-book-open',
            'label' => 'إجمالي الدروس',
            'value' => $totalLessons ?? 0,
            'sub' => 'في جميع الكورسات',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'دروس منشورة',
            'value' => $publishedLessons ?? 0,
            'sub' => 'متاحة للطلاب',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-clock',
            'label' => 'وقت القراءة الإجمالي',
            'value' => $totalReadingTime ?? 0,
            'sub' => 'دقيقة',
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in lessons-page-animate mb-0">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 dashboard-stagger-item lessons-page-animate" style="--stagger-delay: {{ $index * 70 }}ms">
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
