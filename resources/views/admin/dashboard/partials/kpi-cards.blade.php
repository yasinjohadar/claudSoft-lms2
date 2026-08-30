@php
    /*
     * ودجات KPI بنمط Hr-System: خلفية متدرجة، النص في جهة والأيقونة الكبيرة
     * الخافتة في الجهة المقابلة، والبطاقة كلها رابط يرتفع عند التمرير.
     *
     * 'bg' من رموز القالب (bg-*-gradient) فتتبع اللون الأساسي المختار في
     * إعدادات العرض تلقائياً — لا ألوان مكتوبة يدوياً.
     */
    $kpiCards = [
        [
            'bg' => 'bg-warning-gradient',
            'icon' => 'ri-award-line',
            'label' => 'الشهادات الصادرة',
            'value' => $learningStats['certificates_issued'] ?? 0,
            'sub' => 'صادرة اليوم: ' . number_format($todayStats['certificates_today'] ?? 0),
            'route' => 'admin.certificates.index',
        ],
        [
            'bg' => 'bg-info-gradient',
            'icon' => 'ri-book-open-line',
            'label' => 'الكورسات النشطة',
            'value' => $courseStats['published_courses'] ?? 0,
            'sub' => 'من أصل ' . number_format($courseStats['total_courses'] ?? 0) . ' كورس',
            'route' => 'courses.index',
        ],
        [
            'bg' => 'bg-success-gradient',
            'icon' => 'ri-user-follow-line',
            'label' => 'الالتحاقات النشطة',
            'value' => $courseStats['active_enrollments'] ?? 0,
            'sub' => 'إجمالي: ' . number_format($courseStats['total_enrollments'] ?? 0) . ' التحاق',
            'route' => 'enrollments.all',
        ],
        [
            'bg' => 'bg-primary-gradient',
            'icon' => 'ri-team-line',
            'label' => 'إجمالي الطلاب',
            'value' => $userStats['students'] ?? 0,
            'sub' => number_format($userStats['active_today'] ?? 0) . ' نشط اليوم',
            'route' => 'users.index',
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in mb-2">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
            @php
                // الرابط اختياري: بطاقة بلا راوت صالح تُعرض بلا <a> بدل أن ترمي استثناء
                $href = (!empty($card['route']) && Route::has($card['route'])) ? route($card['route']) : null;
            @endphp
            <a @if($href) href="{{ $href }}" @endif class="kpi-card-link">
                <div class="card kpi-card overflow-hidden {{ $card['bg'] }} mb-0 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="min-w-0">
                                <h6 class="kpi-card__label">{{ $card['label'] }}</h6>
                                <h2 class="kpi-card__value" data-countup="{{ $card['value'] }}">0</h2>
                                <small class="kpi-card__sub">{{ $card['sub'] }}</small>
                            </div>
                            <div class="kpi-card__icon"><i class="{{ $card['icon'] }}"></i></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>
