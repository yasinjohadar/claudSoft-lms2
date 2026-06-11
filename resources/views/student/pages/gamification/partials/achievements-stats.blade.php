@php
    $tierEarned = collect($stats['by_tier'] ?? [])->sum();
    $statCards = [
        ['variant' => 'green', 'icon' => 'fe-check-circle', 'label' => 'إنجازات مكتملة', 'value' => $stats['completed'] ?? 0],
        ['variant' => 'blue', 'icon' => 'fe-layers', 'label' => 'إجمالي الإنجازات', 'value' => $stats['total_available'] ?? 0],
        ['variant' => 'orange', 'icon' => 'fe-trending-up', 'label' => 'نسبة الإكمال', 'value' => round($stats['completion_rate'] ?? 0, 1), 'suffix' => '%', 'decimals' => true],
        ['variant' => 'cyan', 'icon' => 'fe-zap', 'label' => 'قيد التقدم', 'value' => $stats['in_progress'] ?? 0],
    ];
@endphp

<div class="row g-3 mb-4 student-achievements-stats">
    @foreach ($statCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 student-my-courses-stagger" style="--stagger-delay: {{ $index * 60 }}ms">
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
