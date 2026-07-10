@php
    $statCards = [
        ['variant' => 'blue', 'icon' => 'fe-check-square', 'label' => 'إجمالي الواجبات', 'value' => $stats['total'] ?? 0],
        ['variant' => 'green', 'icon' => 'fe-check-circle', 'label' => 'تم التقييم', 'value' => $stats['graded'] ?? 0],
        ['variant' => 'yellow', 'icon' => 'fe-clock', 'label' => 'في انتظار التقييم', 'value' => $stats['submitted'] ?? 0],
        ['variant' => 'cyan', 'icon' => 'fe-percent', 'label' => 'متوسط الدرجات', 'value' => $stats['average_grade'] ?? 0, 'suffix' => '%'],
    ];
@endphp

<div class="row g-3 mb-4">
    @foreach ($statCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 student-quizzes-stagger" style="--stagger-delay: {{ $index * 60 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }} dashboard-fade-in">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        <h3 class="admin-stats-card__value mb-0" data-countup="{{ $card['value'] }}" data-countup-suffix="{{ $card['suffix'] ?? '' }}">0</h3>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
