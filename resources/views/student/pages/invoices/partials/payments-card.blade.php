<div class="col-12 student-my-courses-stagger d-lg-none" style="--stagger-delay: {{ ($index ?? 0) * 40 }}ms">
    <article class="student-payment-card">
        <div class="student-payment-card__header">
            <div class="min-w-0">
                <h6 class="student-payment-card__number mb-1">{{ $payment->payment_number }}</h6>
                @if($payment->invoice)
                    <a href="{{ route('student.invoices.show', $payment->invoice_id) }}" class="student-payment-card__invoice">
                        <i class="fe fe-file-text me-1"></i>{{ $payment->invoice->invoice_number }}
                    </a>
                @endif
            </div>
            @include('student.pages.invoices.partials.payments-status-badge', ['payment' => $payment])
        </div>

        <div class="student-payment-card__amount">
            <span class="student-payment-card__amount-label">المبلغ</span>
            <span class="student-payment-card__amount-value">${{ number_format($payment->amount, 2) }}</span>
        </div>

        <div class="student-payment-card__meta">
            <span><i class="fe fe-calendar me-1"></i>{{ $payment->payment_date->format('Y-m-d H:i') }}</span>
            @if($payment->paymentMethod)
                <span><i class="fe fe-credit-card me-1"></i>{{ $payment->paymentMethod->name }}</span>
            @endif
        </div>

        @if($payment->status === 'failed' && $payment->rejection_reason)
            <p class="text-danger small mb-2">
                <i class="fe fe-alert-circle me-1"></i>{{ $payment->rejection_reason }}
            </p>
        @endif

        <div class="d-flex flex-wrap gap-2 mt-3">
            @if($payment->has_receipt)
                <a href="{{ route('student.payments.receipt', $payment->id) }}"
                   class="btn btn-outline-primary rounded-pill flex-fill"
                   target="_blank">
                    <i class="fe fe-paperclip me-1"></i>الإيصال المرفوع
                </a>
            @endif
            @if($payment->status === 'completed')
                <a href="{{ route('student.payments.show', $payment->id) }}" class="btn btn-primary rounded-pill flex-fill">
                    <i class="fe fe-file-text me-1"></i>تفاصيل الدفعة
                </a>
            @endif
        </div>
    </article>
</div>
