<div class="card custom-card group-show-members-card dashboard-fade-in">
    <div class="card-header border-0 pb-0">
        <h4 class="card-title mb-1">المدفوعات</h4>
        <p class="fs-12 text-muted mb-0">{{ $invoice->payments->count() }} دفعة مسجّلة</p>
    </div>
    <div class="card-body pt-3">
        @forelse($invoice->payments as $payment)
            <div class="border-bottom pb-3 mb-3">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <a href="{{ route('payments.show', $payment->id) }}"
                       class="fw-semibold text-primary text-decoration-none">
                        {{ $payment->payment_number }}
                    </a>
                    <span class="badge bg-success-transparent text-success">${{ number_format($payment->amount, 2) }}</span>
                </div>
                <p class="mb-1 small text-muted">
                    <i class="fe fe-calendar me-1"></i>{{ $payment->payment_date->format('Y-m-d') }}
                </p>
                @if($payment->paymentMethod)
                    <p class="mb-0 small text-muted">
                        <i class="fe fe-credit-card me-1"></i>{{ $payment->paymentMethod->name }}
                    </p>
                @endif
            </div>
        @empty
            <div class="group-show-empty py-4">
                <i class="fe fe-inbox group-show-empty__icon"></i>
                <h5 class="group-show-empty__title">لا توجد مدفوعات</h5>
                <p class="group-show-empty__desc mb-0">لم تُسجّل أي دفعة على هذه الفاتورة بعد.</p>
            </div>
        @endforelse

        @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
            <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}"
               class="btn btn-success-light w-100 mt-2 no-print">
                <i class="fe fe-plus me-2"></i>إضافة دفعة
            </a>
        @endif
    </div>
</div>

@if($invoice->remaining_amount > 0 && $invoice->status !== 'cancelled')
    <div class="alert alert-warning mt-3 mb-0 no-print">
        <i class="fe fe-alert-triangle me-2"></i>
        مبلغ متبقٍ <strong>${{ number_format($invoice->remaining_amount, 2) }}</strong> يحتاج تحصيل.
    </div>
@endif

@if($invoice->is_overdue)
    <div class="alert alert-danger mt-3 mb-0 no-print">
        <i class="fe fe-clock me-2"></i>
        فاتورة متأخرة — تجاوزت تاريخ الاستحقاق {{ $invoice->due_date?->format('Y-m-d') }}.
    </div>
@endif
