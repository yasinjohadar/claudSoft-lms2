@php
    $statCards = [
        [
            'variant' => 'blue',
            'icon' => 'fe-dollar-sign',
            'label' => 'المجموع الإجمالي',
            'value' => round($invoice->total_amount, 2),
            'prefix' => '$',
            'decimals' => true,
            'sub' => $invoice->invoice_number,
        ],
        [
            'variant' => 'green',
            'icon' => 'fe-check-circle',
            'label' => 'المبلغ المدفوع',
            'value' => round($invoice->paid_amount, 2),
            'prefix' => '$',
            'decimals' => true,
            'sub' => $invoice->payments->count() . ' دفعة',
        ],
        [
            'variant' => 'orange',
            'icon' => 'fe-alert-circle',
            'label' => 'المبلغ المتبقي',
            'value' => round($invoice->remaining_amount, 2),
            'prefix' => '$',
            'decimals' => true,
            'sub' => $invoice->remaining_amount > 0 ? 'يحتاج تحصيل' : 'مسددة بالكامل',
        ],
    ];
@endphp

<div class="row g-3 dashboard-fade-in mb-4 no-print">
    @foreach ($statCards as $index => $card)
        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
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
                        <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
