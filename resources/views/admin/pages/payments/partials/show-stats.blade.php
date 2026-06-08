@php
    $statCards = [
        [
            'variant' => 'green',
            'icon' => 'fe-dollar-sign',
            'label' => 'مبلغ الدفعة',
            'value' => round($payment->amount, 2),
            'prefix' => '$',
            'decimals' => true,
            'sub' => 'المبلغ المحصّل في هذه الدفعة',
        ],
    ];

    if ($payment->invoice) {
        $statCards[] = [
            'variant' => 'blue',
            'icon' => 'fe-file-text',
            'label' => 'إجمالي الفاتورة',
            'value' => round($payment->invoice->total_amount, 2),
            'prefix' => '$',
            'decimals' => true,
            'sub' => $payment->invoice->invoice_number,
        ];
        $statCards[] = [
            'variant' => 'cyan',
            'icon' => 'fe-check-circle',
            'label' => 'المبلغ المدفوع',
            'value' => round($payment->invoice->paid_amount, 2),
            'prefix' => '$',
            'decimals' => true,
            'sub' => 'على الفاتورة المرتبطة',
        ];
        $statCards[] = [
            'variant' => 'orange',
            'icon' => 'fe-alert-circle',
            'label' => 'المبلغ المتبقي',
            'value' => round($payment->invoice->remaining_amount, 2),
            'prefix' => '$',
            'decimals' => true,
            'sub' => 'باقي على الفاتورة',
        ];
    }

    $colClass = count($statCards) === 1 ? 'col-12' : (count($statCards) === 4 ? 'col-xl-3 col-lg-6 col-md-6 col-sm-12' : 'col-xl-4 col-lg-4 col-md-6 col-sm-12');
@endphp

<div class="row g-3 dashboard-fade-in mb-4 no-print">
    @foreach ($statCards as $index => $card)
        <div class="{{ $colClass }} dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
            <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="admin-stats-card__icon-wrap">
                        <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                    </div>
                    <div class="admin-stats-card__content flex-fill min-w-0">
                        <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                        <h3 class="admin-stats-card__value mb-1"
                            data-countup="{{ $card['value'] }}"
                            @if(!empty($card['prefix'])) data-countup-prefix="{{ $card['prefix'] }}" @endif
                            @if(!empty($card['decimals'])) data-countup-decimals="2" @endif>0</h3>
                        <p class="admin-stats-card__sub mb-0 text-truncate">{{ $card['sub'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
