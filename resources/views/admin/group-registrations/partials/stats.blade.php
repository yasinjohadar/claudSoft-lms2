@php
    $kpiCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-users',
            'label' => 'إجمالي التسجيلات',
            'value' => $stats['total'] ?? 0,
            'sub' => 'حسب الفلاتر الحالية',
        ],
        [
            'variant' => 'yellow',
            'icon' => 'fe-clock',
            'label' => 'معلق / قيد المعالجة',
            'value' => ($stats['pending'] ?? 0) + ($stats['processing'] ?? 0),
            'sub' => ($stats['pending'] ?? 0) . ' معلق · ' . ($stats['processing'] ?? 0) . ' معالجة',
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'مكتمل',
            'value' => $stats['completed'] ?? 0,
            'sub' => ($stats['user_created'] ?? 0) . ' حساب مُنشأ',
        ],
        [
            'variant' => 'red',
            'icon' => 'fe-x-circle',
            'label' => 'فاشل',
            'value' => $stats['failed'] ?? 0,
            'sub' => 'يحتاج إعادة معالجة',
        ],
        [
            'variant' => 'cyan',
            'icon' => 'fe-mail',
            'label' => 'بريد مُرسل',
            'value' => $stats['email_sent'] ?? 0,
            'sub' => 'رسائل ترحيبية',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-message-circle',
            'label' => 'واتساب مُرسل',
            'value' => $stats['whatsapp_sent'] ?? 0,
            'sub' => ($stats['whatsapp_failed'] ?? 0) . ' فشل إرسال',
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in gr-page-animate mb-0">
    @foreach ($kpiCards as $index => $card)
        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item gr-page-animate" style="--stagger-delay: {{ $index * 60 }}ms">
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
