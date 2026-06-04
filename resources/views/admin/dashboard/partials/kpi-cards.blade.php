@php
    $kpiCards = [
        [
            'variant' => 'orange',
            'icon' => 'fe-award',
            'label' => 'الشهادات الصادرة',
            'value' => $learningStats['certificates_issued'] ?? 0,
            'sub' => 'صادرة اليوم: ' . number_format($todayStats['certificates_today'] ?? 0),
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-book-open',
            'label' => 'الكورسات النشطة',
            'value' => $courseStats['published_courses'] ?? 0,
            'sub' => 'من أصل ' . number_format($courseStats['total_courses'] ?? 0) . ' كورس',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-user-check',
            'label' => 'الالتحاقات النشطة',
            'value' => $courseStats['active_enrollments'] ?? 0,
            'sub' => 'إجمالي: ' . number_format($courseStats['total_enrollments'] ?? 0) . ' التحاق',
        ],
        [
            'variant' => 'blue',
            'icon' => 'fe-users',
            'label' => 'إجمالي الطلاب',
            'value' => $userStats['students'] ?? 0,
            'sub' => number_format($userStats['active_today'] ?? 0) . ' نشط اليوم',
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in mb-2">
    @foreach ($kpiCards as $index => $card)
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
