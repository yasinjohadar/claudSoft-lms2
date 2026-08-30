@php
    /*
     * ودجات KPI بنمط Hr-System — نفس مكوّن لوحة الأدمن (kpi-card).
     * الذهبي/الفضي يستخدمان kpi-card--gold / --silver لأن Bootstrap
     * لا يوفّر تدرّجاً مقابلاً لهما.
     */
    $tier = $accountTier ?? 'silver';
    $isGoldAccount = $tier === 'gold';

    $kpiCards = [
        [
            'bg' => $isGoldAccount ? 'kpi-card--gold' : 'kpi-card--silver',
            'icon' => $isGoldAccount ? 'ri-vip-crown-fill' : 'ri-medal-fill',
            'label' => 'نوع الحساب',
            'value' => $isGoldAccount ? 'ذهبي' : 'فضي',
            'value_text' => true,
            'sub' => $isGoldAccount ? 'منضم لمجموعة معسكر مدفوعة' : 'مجموعة عادية أو غير منضم',
            'route' => 'student.groups.index',
        ],
        [
            'bg' => 'bg-primary-gradient',
            'icon' => 'ri-book-open-line',
            'label' => 'إجمالي الكورسات المسجلة',
            'value' => $courseStats['total_courses'] ?? 0,
            'sub' => ($courseStats['completed'] ?? 0) . ' مكتملة',
            'route' => 'student.courses.my-courses',
        ],
        [
            'bg' => 'bg-info-gradient',
            'icon' => 'ri-award-line',
            'label' => 'متوسط درجات الاختبارات',
            'value' => $questionModuleStats['average_score'] ?? 0,
            'sub' => 'من المحاولات المكتملة',
            'route' => 'student.question-module.stats.index',
            'suffix' => '%',
            'decimals' => true,
        ],
        [
            'bg' => 'bg-success-gradient',
            'icon' => 'ri-checkbox-circle-line',
            'label' => 'اختبارات ناجحة',
            'value' => $questionModuleStats['passed_attempts'] ?? 0,
            'sub' => 'محاولات اجتازت النجاح',
            'route' => 'student.question-module.stats.index',
        ],
        [
            'bg' => 'bg-warning-gradient',
            'icon' => 'ri-file-list-3-line',
            'label' => 'إجمالي المحاولات',
            'value' => $questionModuleStats['total_attempts'] ?? 0,
            'sub' => 'محاولات اختبار مكتملة',
            'route' => 'student.question-module.stats.index',
        ],
    ];
@endphp

<div class="row row-cols-xl-5 row-cols-lg-2 row-cols-md-2 row-cols-1 g-3 dashboard-fade-in mb-2">
    @foreach ($kpiCards as $index => $card)
        <div class="col dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
            @php
                $href = (!empty($card['route']) && Route::has($card['route'])) ? route($card['route']) : null;
            @endphp
            <a @if($href) href="{{ $href }}" @endif class="kpi-card-link">
                <div class="card kpi-card overflow-hidden {{ $card['bg'] }} mb-0 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="min-w-0">
                                <h6 class="kpi-card__label">{{ $card['label'] }}</h6>
                                @if (!empty($card['value_text']))
                                    <h2 class="kpi-card__value kpi-card__value--text">{{ $card['value'] }}</h2>
                                @else
                                    <h2 class="kpi-card__value"
                                        data-countup="{{ $card['value'] }}"
                                        @if(!empty($card['suffix'])) data-countup-suffix="{{ $card['suffix'] }}" @endif
                                        @if(!empty($card['decimals'])) data-countup-decimals="1" @endif>0</h2>
                                @endif
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
