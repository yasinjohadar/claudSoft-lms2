<div class="d-none d-xl-block">
    <div class="table-responsive">
        <table class="table table-hover mb-0 student-invoices-table">
            <thead>
                <tr>
                    <th class="ps-4 fs-12">الفاتورة</th>
                    <th class="fs-12">المبالغ</th>
                    <th class="fs-12">الاستحقاق</th>
                    <th class="fs-12">السداد</th>
                    <th class="fs-12">الحالة</th>
                    <th class="text-end pe-4 fs-12">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    @php
                        $paidPct = $invoice->total_amount > 0
                            ? min(100, ($invoice->paid_amount / $invoice->total_amount) * 100)
                            : 0;
                        $pendingAmount = $invoice->pendingPaymentsAmount();
                        $paymentsCount = $invoice->payments->count();
                    @endphp
                    <tr class="student-my-courses-stagger {{ $invoice->is_overdue ? 'is-overdue' : '' }}" style="--stagger-delay: {{ $loop->index * 30 }}ms">
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <span class="avatar avatar-md bg-primary-transparent rounded-circle">
                                    <i class="fe fe-file-text text-primary"></i>
                                </span>
                                <div class="min-w-0">
                                    <strong class="d-block fs-13">{{ $invoice->invoice_number }}</strong>
                                    <small class="text-muted">{{ $invoice->issue_date->format('Y-m-d') }}</small>
                                    @include('student.pages.invoices.partials.invoice-programs', ['invoice' => $invoice, 'class' => 'mt-1'])
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="student-invoices-table__amounts">
                                <span><strong class="text-primary">${{ number_format($invoice->total_amount, 2) }}</strong> <small class="text-muted">إجمالي</small></span>
                                <span><strong class="text-success">${{ number_format($invoice->paid_amount, 2) }}</strong> <small class="text-muted">مدفوع</small></span>
                                <span><strong class="text-danger">${{ number_format($invoice->remaining_amount, 2) }}</strong> <small class="text-muted">متبقي</small></span>
                                @if($pendingAmount > 0)
                                    <span class="student-invoice-pending-note" title="المبلغ الذي أدخلته في طلب الدفع، ولا يُحتسب حتى توافق الإدارة">
                                        <i class="fe fe-clock"></i><strong>${{ number_format($pendingAmount, 2) }}</strong> قيد المراجعة (غير محتسب)
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="fs-13">{{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '—' }}</div>
                        </td>
                        <td style="min-width: 120px;">
                            <div class="student-invoices-table__progress">
                                <div class="student-course-card__progress-track">
                                    <div class="student-course-card__progress-bar"
                                         style="width: {{ max(0, min(100, $paidPct)) }}%"></div>
                                </div>
                                <small class="text-muted">{{ number_format($paidPct, 0) }}%</small>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @include('student.pages.invoices.partials.invoices-status-badge', ['invoice' => $invoice])
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex flex-wrap gap-1 justify-content-end">
                                @if($invoice->canAcceptStudentPayment() && isset($paymentMethods) && $paymentMethods->count() > 0)
                                    <button type="button"
                                            class="btn btn-sm btn-success rounded-pill js-open-pay-modal"
                                            data-invoice-id="{{ $invoice->id }}"
                                            data-invoice-number="{{ $invoice->invoice_number }}"
                                            data-remaining="{{ number_format($invoice->remaining_amount, 2, '.', '') }}">
                                        <i class="fe fe-credit-card me-1"></i>سداد
                                    </button>
                                @elseif($invoice->hasPendingPayment())
                                    <span class="badge bg-warning-transparent fs-11">
                                        <i class="fe fe-clock me-1"></i>قيد المراجعة
                                    </span>
                                @endif
                                @if($paymentsCount > 0)
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary rounded-pill"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#invoicePayments{{ $invoice->id }}"
                                            aria-expanded="false"
                                            aria-controls="invoicePayments{{ $invoice->id }}">
                                        <i class="fe fe-list me-1"></i>الدفعات ({{ $paymentsCount }})
                                    </button>
                                @endif
                                <a href="{{ route('student.invoices.show', $invoice->id) }}" class="btn btn-sm btn-primary rounded-pill">
                                    <i class="fe fe-eye me-1"></i>التفاصيل
                                </a>
                            </div>
                        </td>
                    </tr>
                    @if($paymentsCount > 0)
                        <tr class="student-invoices-table__payments-row">
                            <td colspan="6" class="p-0 border-0">
                                <div class="collapse" id="invoicePayments{{ $invoice->id }}">
                                    <div class="student-invoices-table__payments">
                                        <h6 class="fs-13 fw-semibold mb-3">
                                            <i class="fe fe-list me-1 text-primary"></i>تسلسل الدفعات — {{ $invoice->invoice_number }}
                                        </h6>
                                        @include('student.pages.invoices.partials.payments-timeline', ['invoice' => $invoice, 'class' => 'student-payment-timeline--grid'])
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="p-0 border-0">
                            @include('student.pages.invoices.partials.invoices-empty')
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
