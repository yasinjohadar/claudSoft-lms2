@php
    $kpiCards = [
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

<div class="row g-3 dashboard-fade-in mb-2">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
            <a href="{{ route($card['route']) }}" class="text-decoration-none d-block h-100">
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
            </a>
        </div>
    @endforeach
</div>
