@php
    $availableSlots = $group->max_members ? $group->getAvailableSlots() : null;
    $statCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-users',
            'label' => 'عدد الأعضاء',
            'value' => $group->members_count ?? 0,
            'suffix' => $group->max_members ? ' / ' . $group->max_members : '',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-user-plus',
            'label' => 'المقاعد المتاحة',
            'value' => $availableSlots ?? 0,
            'text' => $group->max_members ? null : 'غير محدود',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-calendar',
            'label' => 'تاريخ الإنشاء',
            'text' => $group->created_at->format('Y-m-d'),
        ],
    ];
@endphp

<div class="row g-3 mb-4 student-group-show-stats">
    @foreach ($statCards as $index => $card)
        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 student-my-courses-stagger" style="--stagger-delay: {{ $index * 60 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        @if(!empty($card['text']))
                            <h3 class="admin-stats-card__value admin-stats-card__value--text mb-0">{{ $card['text'] }}</h3>
                        @else
                            <h3 class="admin-stats-card__value mb-0">
                                <span data-countup="{{ $card['value'] }}">0</span>{{ $card['suffix'] ?? '' }}
                            </h3>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
