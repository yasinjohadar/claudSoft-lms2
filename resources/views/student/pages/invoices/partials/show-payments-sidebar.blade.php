<div class="card custom-card student-quizzes-panel student-invoice-show-payments">
    <div class="card-body">
        <div class="d-flex align-items-center gap-2 mb-4">
            <span class="avatar avatar-sm bg-success-transparent">
                <i class="fe fe-credit-card text-success"></i>
            </span>
            <h6 class="card-title mb-0">المدفوعات</h6>
        </div>

        @if($invoice->hasPendingPayment())
            <div class="alert alert-warning small mb-3">
                <i class="fe fe-clock me-1"></i>
                <strong>طلب دفع قيد المراجعة.</strong>
                سيتم إشعارك عند الموافقة أو الرفض.
            </div>
        @endif

        @if($invoice->payments->count() > 0)
            <div class="student-invoice-payments-list">
                @foreach($invoice->payments->sortByDesc('created_at') as $payment)
                    @include('student.pages.invoices.partials.payment-item', ['payment' => $payment])
                @endforeach
            </div>
        @else
            <div class="student-my-courses-empty text-center py-4">
                <div class="student-my-courses-empty__icon mb-3" style="width: 64px; height: 64px; font-size: 1.5rem;">
                    <i class="fe fe-inbox"></i>
                </div>
                <p class="text-muted mb-0">لا توجد مدفوعات بعد</p>
            </div>
        @endif
    </div>
</div>

@if($invoice->remaining_amount > 0 && $invoice->status !== 'cancelled' && ! $invoice->hasPendingPayment())
    <div class="student-invoice-show-alert mt-3">
        <i class="fe fe-alert-triangle"></i>
        <div>
            <strong>تنبيه</strong>
            <span class="text-muted">لديك مبلغ متبقي <strong class="text-danger">${{ number_format($invoice->remaining_amount, 2) }}</strong> يجب دفعه.</span>
        </div>
    </div>
@endif

@if($invoice->is_overdue)
    <div class="student-invoice-show-alert student-invoice-show-alert--danger mt-3">
        <i class="fe fe-alert-circle"></i>
        <div>
            <strong>فاتورة متأخرة</strong>
            <span class="text-muted">تجاوزت تاريخ الاستحقاق {{ $invoice->due_date?->format('Y-m-d') }}.</span>
        </div>
    </div>
@endif
