@php
    $statCards = [
        ['variant' => 'blue', 'icon' => 'fe-briefcase', 'label' => 'إجمالي الأعمال', 'value' => $stats['total'] ?? 0, 'sub' => 'كل أعمالك المسجّلة'],
        ['variant' => 'cyan', 'icon' => 'fe-edit-3', 'label' => 'المسودات', 'value' => $stats['draft'] ?? 0, 'sub' => 'لم تُقدَّم بعد'],
        ['variant' => 'orange', 'icon' => 'fe-clock', 'label' => 'قيد المراجعة', 'value' => $stats['pending'] ?? 0, 'sub' => 'بانتظار موافقة الإدارة'],
        ['variant' => 'green', 'icon' => 'fe-check-circle', 'label' => 'المعتمدة', 'value' => $stats['approved'] ?? 0, 'sub' => 'ظاهرة في بورتفوليوك'],
    ];
@endphp

<div class="row g-3 dashboard-fade-in mb-4 student-works-stats">
    @foreach ($statCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 student-my-courses-stagger" style="--stagger-delay: {{ $index * 60 }}ms">
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
