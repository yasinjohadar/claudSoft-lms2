@php
    $memberValue = $stats['total_members'] ?? 0;
    $memberSub = $group->max_members
        ? 'الحد الأقصى: ' . number_format($group->max_members) . ' عضو'
        : 'بدون حد أقصى للأعضاء';

    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-users',
            'label' => 'عدد الأعضاء',
            'value' => $memberValue,
            'sub' => $memberSub,
            'countup' => true,
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-book-open',
            'label' => 'الكورسات المرتبطة',
            'value' => $group->courses->count(),
            'sub' => 'كورسات مرتبطة بهذه المجموعة',
            'countup' => true,
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-power',
            'label' => 'الحالة',
            'value' => $group->is_active ? 'نشطة' : 'غير نشطة',
            'sub' => $group->is_active ? 'المجموعة مفعّلة حالياً' : 'المجموعة متوقفة',
            'countup' => false,
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-eye',
            'label' => 'الرؤية',
            'value' => $group->is_visible ? 'مرئية' : 'مخفية',
            'sub' => ($group->is_visible_for_students ?? true) ? 'ظاهرة للطلاب' : 'مخفية عن الطلاب',
            'countup' => false,
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in mb-4">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
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
                            <h3 class="admin-stats-card__value admin-stats-card__value--text mb-1">{{ $card['value'] }}</h3>
                        @endif
                        <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
