@php
    $statCards = [
        ['variant' => 'blue', 'icon' => 'fe-file-text', 'label' => 'إجمالي التقارير', 'value' => $stats['total'] ?? 0],
        ['variant' => 'yellow', 'icon' => 'fe-edit', 'label' => 'مسودة', 'value' => $stats['draft'] ?? 0],
        ['variant' => 'green', 'icon' => 'fe-check-circle', 'label' => 'تمت المراجعة', 'value' => $stats['reviewed'] ?? 0],
        ['variant' => 'cyan', 'icon' => 'fe-message-circle', 'label' => 'بها رد إدارة', 'value' => $stats['with_feedback'] ?? 0],
    ];
@endphp

<div class="row g-3 mb-4">
    @foreach ($statCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 student-quizzes-stagger" style="--stagger-delay: {{ $index * 60 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        <h3 class="admin-stats-card__value mb-0" data-countup="{{ $card['value'] }}">0</h3>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
