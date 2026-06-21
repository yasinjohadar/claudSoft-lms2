@php
    $waContext = $waContext ?? [];
    $waSelectedJid = $waContext['selected_jid'] ?? '';
    $waStats = $waContext['wa_stats'] ?? ['not_in_group' => 0, 'in_group' => 0, 'no_phone' => 0, 'invite_pending' => 0];

    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-inbox',
            'label' => 'إجمالي الطلبات',
            'value' => $requests->total(),
            'sub' => 'حسب الفلتر الحالي',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-clock',
            'label' => 'قيد المراجعة',
            'value' => $pendingCount ?? 0,
            'sub' => 'طلبات بانتظار القرار',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-users',
            'label' => 'أعضاء المجموعة',
            'value' => $group->members_count ?? 0,
            'sub' => $group->max_members ? 'الحد الأقصى: ' . $group->max_members : 'بدون حد أقصى',
        ],
    ];

    if ($waSelectedJid !== '' && empty($waContext['wa_load_error'] ?? null)) {
        $kpiCards[] = [
            'variant' => 'red',
            'icon' => 'ri-user-unfollow-line',
            'label' => 'غير منضمين WA',
            'value' => $waStats['not_in_group'] ?? 0,
            'sub' => 'من طلبات الانضمام — يحتاجون دعوة',
        ];
        $kpiCards[] = [
            'variant' => 'orange',
            'icon' => 'ri-mail-send-line',
            'label' => 'دُعوا ولم ينضموا',
            'value' => $waStats['invite_pending'] ?? 0,
            'sub' => 'أُرسلت دعوة واتساب — بانتظار الانضمام',
        ];
        $kpiCards[] = [
            'variant' => 'green',
            'icon' => 'ri-user-follow-line',
            'label' => 'منضمين WA',
            'value' => $waStats['in_group'] ?? 0,
            'sub' => 'من طلبات الانضمام — رقمهم موجود في WA',
        ];
        $waGroupSize = $waContext['wa_group_info']['size']
            ?? $waContext['wa_group_info']['participants_count']
            ?? null;
        if ($waGroupSize !== null) {
            $kpiCards[] = [
                'variant' => 'blue',
                'icon' => 'ri-whatsapp-line',
                'label' => 'إجمالي مجموعة WA',
                'value' => (int) $waGroupSize,
                'sub' => 'كل الأعضاء في واتساب (ليس فقط طلابنا)',
            ];
        }
    }
@endphp

<div class="row g-3 dashboard-fade-in mb-4">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-{{ count($kpiCards) > 3 ? '2' : '4' }} col-lg-4 col-md-6 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="{{ str_contains($card['icon'], 'ri-') ? $card['icon'] : 'fe ' . $card['icon'] }} admin-stats-card__icon"></i>
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
