@php
    $lastUpdated = $stats['last_updated'] ?? null;
    $lastUpdatedLabel = $lastUpdated
        ? \Illuminate\Support\Carbon::parse($lastUpdated)->diffForHumans()
        : '—';

    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-file-text',
            'label' => 'إجمالي المقالات',
            'value' => $stats['total'] ?? 0,
            'sub' => 'في هذا القسم',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'منشور',
            'value' => $stats['published'] ?? 0,
            'sub' => 'متاح للطلاب',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-edit',
            'label' => 'مسودات',
            'value' => $stats['draft'] ?? 0,
            'sub' => 'قيد التحرير',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-clock',
            'label' => 'آخر تحديث',
            'value' => $lastUpdatedLabel,
            'sub' => 'أحدث مقال',
            'is_text' => true,
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in doc-cat-animate mb-4">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item doc-cat-animate" style="--stagger-delay: {{ $index * 70 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        @if (! empty($card['is_text']))
                            <h3 class="admin-stats-card__value mb-1 fs-6">{{ $card['value'] }}</h3>
                        @else
                            <h3 class="admin-stats-card__value mb-1">{{ number_format($card['value']) }}</h3>
                        @endif
                        <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
