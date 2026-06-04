<div class="d-none d-lg-block">
    <div class="table-responsive">
        <table class="table table-hover mb-0 student-payments-table">
            <thead>
                <tr>
                    <th class="ps-4 fs-12">#</th>
                    <th class="fs-12">رقم الدفعة</th>
                    <th class="fs-12">الفاتورة</th>
                    <th class="fs-12">المبلغ</th>
                    <th class="fs-12">الطريقة</th>
                    <th class="fs-12">التاريخ</th>
                    <th class="fs-12">الحالة</th>
                    <th class="text-end pe-4 fs-12">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr class="student-my-courses-stagger" style="--stagger-delay: {{ $loop->index * 30 }}ms">
                        <td class="ps-4 text-muted">{{ $loop->iteration + ($payments->currentPage() - 1) * $payments->perPage() }}</td>
                        <td>
                            <strong class="fs-13">{{ $payment->payment_number }}</strong>
                        </td>
                        <td>
                            @if($payment->invoice)
                                <a href="{{ route('student.invoices.show', $payment->invoice_id) }}" class="text-primary fw-semibold">
                                    {{ $payment->invoice->invoice_number }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <strong class="text-success">${{ number_format($payment->amount, 2) }}</strong>
                        </td>
                        <td>
                            @if($payment->paymentMethod)
                                <span class="badge bg-info-transparent fs-11">
                                    <i class="fe fe-credit-card me-1"></i>{{ $payment->paymentMethod->name }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="fs-13">{{ $payment->payment_date->format('Y/m/d') }}</div>
                            <small class="text-muted">{{ $payment->payment_date->format('H:i') }}</small>
                        </td>
                        <td>
                            @include('student.pages.invoices.partials.payments-status-badge', ['payment' => $payment])
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('student.payments.show', $payment->id) }}"
                               class="btn btn-sm btn-primary rounded-pill">
                                <i class="fe fe-file-text me-1"></i>الإيصال
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-0 border-0">
                            @include('student.pages.invoices.partials.payments-empty')
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
