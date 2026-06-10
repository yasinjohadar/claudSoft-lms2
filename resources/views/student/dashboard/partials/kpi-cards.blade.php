@php
    $tier = $accountTier ?? 'silver';
    $isGoldAccount = $tier === 'gold';

    $kpiCards = [
        [
            'variant' => $isGoldAccount ? 'gold' : 'silver',
            'icon' => $isGoldAccount ? 'ri-vip-crown-fill' : 'ri-medal-fill',
            'icon_type' => 'remix',
            'label' => 'نوع الحساب',
            'value' => $isGoldAccount ? 'ذهبي' : 'فضي',
            'value_text' => true,
            'sub' => $isGoldAccount ? 'مشترك في معسكرات تدريبية' : 'مجاني — اشترك للترقية',
            'route' => $isGoldAccount ? 'student.training-camps.my-enrollments' : 'student.training-camps.index',
        ],
        [
            'variant' => 'blue',
            'icon' => 'fe-book-open',
            'label' => 'إجمالي الكورسات المسجلة',
            'value' => $courseStats['total_courses'] ?? 0,
            'sub' => ($courseStats['completed'] ?? 0) . ' مكتملة',
            'route' => 'student.courses.my-courses',
            'suffix' => '',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-award',
            'label' => 'متوسط درجات الاختبارات',
            'value' => $questionModuleStats['average_score'] ?? 0,
            'sub' => 'من المحاولات المكتملة',
            'route' => 'student.question-module.stats.index',
            'suffix' => '%',
            'decimals' => true,
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'اختبارات ناجحة',
            'value' => $questionModuleStats['passed_attempts'] ?? 0,
            'sub' => 'محاولات اجتازت النجاح',
            'route' => 'student.question-module.stats.index',
            'suffix' => '',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-file-text',
            'label' => 'إجمالي المحاولات',
            'value' => $questionModuleStats['total_attempts'] ?? 0,
            'sub' => 'محاولات اختبار مكتملة',
            'route' => 'student.question-module.stats.index',
            'suffix' => '',
        ],
    ];
@endphp

<div class="row row-cols-xl-5 row-cols-lg-2 row-cols-md-2 row-cols-1 g-3 dashboard-fade-in mb-2">
    @foreach ($kpiCards as $index => $card)
        <div class="col dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
            <a href="{{ route($card['route']) }}" class="text-decoration-none d-block h-100">
                <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }} h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="admin-stats-card__icon-wrap">
                            @if (($card['icon_type'] ?? '') === 'remix')
                                <i class="{{ $card['icon'] }} admin-stats-card__icon"></i>
                            @else
                                <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                            @endif
                        </div>
                        <div class="admin-stats-card__content flex-fill min-w-0">
                            <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                            @if (!empty($card['value_text']))
                                <h3 class="admin-stats-card__value admin-stats-card__value--text mb-1">{{ $card['value'] }}</h3>
                            @else
                                <h3 class="admin-stats-card__value mb-1"
                                    data-countup="{{ $card['value'] }}"
                                    @if(!empty($card['suffix'])) data-countup-suffix="{{ $card['suffix'] }}" @endif
                                    @if(!empty($card['decimals'])) data-countup-decimals="1" @endif>0</h3>
                            @endif
                            <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>
