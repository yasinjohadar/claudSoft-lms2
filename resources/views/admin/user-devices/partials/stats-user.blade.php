@php
    $lastUsedLabel = $stats['last_used']?->last_used_at?->diffForHumans() ?? '—';

    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-smartphone',
            'label' => 'إجمالي الأجهزة',
            'value' => $stats['total'] ?? 0,
            'sub' => 'أجهزة هذا المستخدم',
            'countup' => true,
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-shield',
            'label' => 'الأجهزة الموثوقة',
            'value' => $stats['trusted'] ?? 0,
            'sub' => 'معتمدة للاستخدام',
            'countup' => true,
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-slash',
            'label' => 'الأجهزة المحظورة',
            'value' => $stats['blocked'] ?? 0,
            'sub' => 'محظورة من الدخول',
            'countup' => true,
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-clock',
            'label' => 'آخر جهاز مستخدم',
            'value' => $lastUsedLabel,
            'sub' => $stats['last_used']?->device_info ?? 'لا يوجد',
            'countup' => false,
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in ud-page-animate mb-0">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item ud-page-animate" style="--stagger-delay: {{ $index * 70 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        @if($card['countup'])
                            <h3 class="admin-stats-card__value mb-1" data-countup="{{ $card['value'] }}">0</h3>
                        @else
                            <h3 class="admin-stats-card__value mb-1 fs-16">{{ $card['value'] }}</h3>
                        @endif
                        <p class="admin-stats-card__sub mb-0 text-truncate" title="{{ $card['sub'] }}">{{ $card['sub'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
