@php
    $context = $context ?? 'distribution';
    $cards = [
        ['variant' => 'blue', 'icon' => 'fe-users', 'label' => 'الطلاب في النطاق', 'value' => $stats['total_students'] ?? 0],
        ['variant' => 'green', 'icon' => 'fe-award', 'label' => 'الشارات النشطة', 'value' => $stats['active_badges'] ?? 0],
        ['variant' => 'orange', 'icon' => 'fe-layers', 'label' => 'إجمالي المنح', 'value' => $stats['total_awards'] ?? 0],
        ['variant' => 'cyan', 'icon' => 'fe-trending-up', 'label' => 'متوسط الشارات/طالب', 'value' => $stats['avg_badges_per_student'] ?? 0, 'decimals' => true],
        ['variant' => 'cyan', 'icon' => 'fe-check-circle', 'label' => 'طلاب لديهم شارات', 'value' => $stats['students_with_badges'] ?? 0],
        ['variant' => 'orange', 'icon' => 'fe-percent', 'label' => 'نسبة الطلاب الحاصلين', 'value' => $stats['students_with_badge_rate'] ?? 0, 'suffix' => '%', 'decimals' => true],
    ];
@endphp

<div class="row g-3 mb-0 badge-report-stats">
    @foreach ($cards as $index => $card)
        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-6 dashboard-stagger-item" style="--stagger-delay: {{ $index * 50 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        <h3 class="admin-stats-card__value mb-0"
                            data-countup="{{ $card['value'] }}"
                            @if(!empty($card['suffix'])) data-countup-suffix="{{ $card['suffix'] }}" @endif
                            @if(!empty($card['decimals'])) data-countup-decimals="1" @endif>0</h3>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
