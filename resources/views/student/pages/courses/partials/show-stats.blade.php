@php
    $completedCount = isset($completedModules) ? count($completedModules) : 0;
    $totalModules = $stats['total_modules'] ?? 0;
    $progressPct = ($enrollment && $totalModules > 0)
        ? min(100, round(($completedCount / $totalModules) * 100))
        : 0;

    $levelLabel = match ($course->level ?? null) {
        'beginner' => 'مبتدئ',
        'intermediate' => 'متوسط',
        'advanced' => 'متقدم',
        default => null,
    };

    $statCards = [
        ['variant' => 'blue', 'icon' => 'fe-layers', 'label' => 'الأقسام', 'value' => $stats['total_sections'] ?? 0],
        ['variant' => 'green', 'icon' => 'fe-book-open', 'label' => 'الدروس', 'value' => $totalModules],
        ['variant' => 'orange', 'icon' => 'fe-clock', 'label' => 'المدة (ساعة)', 'value' => $course->duration_in_hours ?? 0],
    ];

    if ($enrollment) {
        $statCards[] = [
            'variant' => 'cyan',
            'icon' => 'fe-trending-up',
            'label' => 'التقدم',
            'value' => $progressPct,
            'suffix' => '%',
        ];
    }
@endphp

<div class="row g-3 dashboard-fade-in mb-4 student-course-show-stats">
    @foreach ($statCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 student-my-courses-stagger" style="--stagger-delay: {{ $index * 55 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        <h3 class="admin-stats-card__value mb-0">
                            {{ $card['value'] }}{{ $card['suffix'] ?? '' }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
