<div class="card custom-card student-quizzes-panel student-invoice-show-items">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <h5 class="mb-0 fw-bold">{{ $invoice->invoice_number }}</h5>
                    @include('student.pages.invoices.partials.invoices-status-badge', ['invoice' => $invoice])
                </div>
                <div class="student-invoice-show-meta">
                    <span><i class="fe fe-calendar me-1"></i>الإصدار: {{ $invoice->issue_date->format('Y-m-d') }}</span>
                    <span><i class="fe fe-clock me-1"></i>الاستحقاق: {{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '—' }}</span>
                </div>
                @include('student.pages.invoices.partials.invoice-programs', ['invoice' => $invoice, 'class' => 'mt-2'])
            </div>
        </div>

        <h6 class="fw-semibold mb-3">بنود الفاتورة</h6>
        <div class="table-responsive">
            <table class="table table-hover mb-0 student-invoices-table student-invoice-show-table">
                <thead>
                    <tr>
                        <th class="ps-3 fs-12">الوصف</th>
                        <th class="fs-12">الكمية</th>
                        <th class="fs-12">سعر الوحدة</th>
                        <th class="pe-3 fs-12">المجموع</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                        <tr>
                            <td class="ps-3">
                                <strong class="d-block fs-13">{{ $item->description }}</strong>
                                @if($program = $item->relatedProgram())
                                    <small class="text-muted">
                                        <i class="fe {{ $program['icon'] }} me-1"></i>{{ $program['type'] }}: {{ $program['name'] }}
                                    </small>
                                @endif
                            </td>
                            <td>{{ $item->quantity }}</td>
                            <td>${{ number_format($item->unit_price, 2) }}</td>
                            <td class="pe-3 fw-semibold">${{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="student-invoice-show-table__total">
                        <td colspan="3" class="text-end ps-3"><strong>المجموع الإجمالي</strong></td>
                        <td class="pe-3 fw-bold text-primary">${{ number_format($invoice->total_amount, 2) }}</td>
                    </tr>
                    <tr class="student-invoice-show-table__paid">
                        <td colspan="3" class="text-end ps-3"><strong>المبلغ المدفوع</strong></td>
                        <td class="pe-3 fw-bold text-success">${{ number_format($invoice->paid_amount, 2) }}</td>
                    </tr>
                    <tr class="student-invoice-show-table__remaining">
                        <td colspan="3" class="text-end ps-3"><strong>المبلغ المتبقي</strong></td>
                        <td class="pe-3 fw-bold text-danger">${{ number_format($invoice->remaining_amount, 2) }}</td>
                    </tr>
                    @if(($pendingAmount = $invoice->pendingPaymentsAmount()) > 0)
                        <tr>
                            <td colspan="3" class="text-end ps-3">
                                <strong>قيد المراجعة</strong>
                                <small class="text-muted d-block">المبلغ الذي أدخلته — لا يُحتسب حتى تقبله الإدارة</small>
                            </td>
                            <td class="pe-3 fw-bold text-warning">${{ number_format($pendingAmount, 2) }}</td>
                        </tr>
                    @endif
                </tfoot>
            </table>
        </div>

        @if($invoice->notes)
            <div class="student-invoice-show-notes mt-4">
                <h6 class="student-invoice-show-notes__title">
                    <i class="fe fe-info me-2"></i>ملاحظات
                </h6>
                <p class="mb-0">{{ $invoice->notes }}</p>
            </div>
        @endif
    </div>
</div>
