@php
    $paidPct = $invoice->total_amount > 0
        ? min(100, ($invoice->paid_amount / $invoice->total_amount) * 100)
        : 0;
@endphp

<div class="col-lg-6 col-12 student-my-courses-stagger" style="--stagger-delay: {{ ($index ?? 0) * 50 }}ms">
    <article class="student-invoice-card {{ $invoice->is_overdue ? 'is-overdue' : '' }}">
        <div class="student-invoice-card__header">
            <div class="min-w-0">
                <h5 class="student-invoice-card__number mb-1">{{ $invoice->invoice_number }}</h5>
                <span class="student-invoice-card__date">
                    <i class="fe fe-calendar me-1"></i>{{ $invoice->issue_date->format('Y-m-d') }}
                </span>
            </div>
            <div class="d-flex flex-wrap gap-1 justify-content-end">
                @include('student.pages.invoices.partials.invoices-status-badge', ['invoice' => $invoice])
            </div>
        </div>

        <div class="student-invoice-card__amounts">
            <div class="student-invoice-card__amount">
                <span class="student-invoice-card__amount-label">الإجمالي</span>
                <span class="student-invoice-card__amount-value text-primary">${{ number_format($invoice->total_amount, 2) }}</span>
            </div>
            <div class="student-invoice-card__amount">
                <span class="student-invoice-card__amount-label">المدفوع</span>
                <span class="student-invoice-card__amount-value text-success">${{ number_format($invoice->paid_amount, 2) }}</span>
            </div>
            <div class="student-invoice-card__amount">
                <span class="student-invoice-card__amount-label">المتبقي</span>
                <span class="student-invoice-card__amount-value text-danger">${{ number_format($invoice->remaining_amount, 2) }}</span>
            </div>
            <div class="student-invoice-card__amount">
                <span class="student-invoice-card__amount-label">الاستحقاق</span>
                <span class="student-invoice-card__amount-value">{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '—' }}</span>
            </div>
        </div>

        <div class="student-invoice-card__progress">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <small class="text-muted">نسبة السداد</small>
                <small class="fw-semibold">{{ number_format($paidPct, 0) }}%</small>
            </div>
            <div class="student-course-card__progress-track">
                <div class="student-course-card__progress-bar {{ $paidPct >= 100 ? 'bg-success' : '' }}"
                     style="width: {{ max(0, min(100, $paidPct)) }}%"
                     role="progressbar"
                     aria-valuenow="{{ $paidPct }}"
                     aria-valuemin="0"
                     aria-valuemax="100"></div>
            </div>
        </div>

        @if($invoice->items->count() > 0)
            <div class="student-invoice-card__items">
                <span class="student-invoice-card__items-label">البنود</span>
                @foreach($invoice->items->take(2) as $item)
                    <p class="student-invoice-card__item mb-1">
                        <i class="fe fe-check text-success me-1"></i>{{ $item->description }}
                    </p>
                @endforeach
                @if($invoice->items->count() > 2)
                    <small class="text-muted">+ {{ $invoice->items->count() - 2 }} بند آخر</small>
                @endif
            </div>
        @endif

        <div class="d-flex flex-wrap gap-2 mt-3">
            @if($invoice->canAcceptStudentPayment() && isset($paymentMethods) && $paymentMethods->count() > 0)
                <button type="button"
                        class="btn btn-success rounded-pill flex-fill js-open-pay-modal"
                        data-invoice-id="{{ $invoice->id }}"
                        data-invoice-number="{{ $invoice->invoice_number }}"
                        data-remaining="{{ number_format($invoice->remaining_amount, 2, '.', '') }}">
                    <i class="fe fe-credit-card me-1"></i>سداد الفاتورة
                </button>
            @elseif($invoice->hasPendingPayment())
                <div class="alert alert-warning small w-100 mb-0 py-2">
                    <i class="fe fe-clock me-1"></i>طلب الدفع قيد المراجعة
                </div>
            @endif
            <a href="{{ route('student.invoices.show', $invoice->id) }}" class="btn btn-primary rounded-pill flex-fill">
                <i class="fe fe-eye me-1"></i>عرض التفاصيل
            </a>
        </div>
    </article>
</div>
