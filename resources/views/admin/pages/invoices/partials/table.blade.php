@forelse ($invoices as $invoice)
    <tr>
        <td>{{ $loop->iteration + ($invoices->currentPage() - 1) * $invoices->perPage() }}</td>

        <td>
            <strong>{{ $invoice->invoice_number }}</strong>
        </td>

        <td>
            <div>
                <strong>{{ $invoice->student->name }}</strong>
                <br><small class="text-muted">{{ $invoice->student->email }}</small>
            </div>
        </td>

        <td>
            @php
                $campNames = $invoice->items
                    ->map(function ($item) {
                        return optional(optional($item->campEnrollment)->camp)->name;
                    })
                    ->filter()
                    ->unique()
                    ->values();
            @endphp

            @if($campNames->isNotEmpty())
                @foreach($campNames as $campName)
                    <span class="badge bg-primary-transparent text-primary me-1">{{ $campName }}</span>
                @endforeach
            @else
                <span class="text-muted">-</span>
            @endif
        </td>

        <td>{{ $invoice->issue_date->format('Y-m-d') }}</td>

        <td>
            {{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '-' }}
            @if($invoice->is_overdue)
                <br><span class="badge bg-danger overdue-badge">متأخرة</span>
            @endif
        </td>

        <td><strong>${{ number_format($invoice->total_amount, 2) }}</strong></td>
        <td class="text-success">${{ number_format($invoice->paid_amount, 2) }}</td>
        <td class="text-danger">${{ number_format($invoice->remaining_amount, 2) }}</td>

        <td>
            @php
                $statusColors = [
                    'draft' => 'bg-secondary',
                    'issued' => 'bg-info',
                    'partial' => 'bg-warning text-dark',
                    'paid' => 'bg-success',
                    'cancelled' => 'bg-danger',
                    'refunded' => 'bg-dark'
                ];
                $statusLabels = [
                    'draft' => 'مسودة',
                    'issued' => 'صادرة',
                    'partial' => 'جزئياً',
                    'paid' => 'مدفوعة',
                    'cancelled' => 'ملغاة',
                    'refunded' => 'مستردة'
                ];
            @endphp
            <span class="badge {{ $statusColors[$invoice->status] ?? 'bg-secondary' }} invoice-status-badge">
                {{ $statusLabels[$invoice->status] ?? $invoice->status }}
            </span>
        </td>

        <td>
            <div class="btn-group" role="group">
                <a href="{{ route('invoices.show', $invoice->id) }}"
                   class="btn btn-sm btn-info" title="عرض التفاصيل">
                    <i class="fas fa-eye"></i>
                </a>

                @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                    <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}"
                       class="btn btn-sm btn-success" title="إضافة دفعة">
                        <i class="fas fa-plus"></i>
                    </a>
                @endif

                @if($invoice->status !== 'cancelled' && $invoice->status !== 'paid')
                    <button type="button" class="btn btn-sm btn-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#cancelModal{{ $invoice->id }}"
                            title="إلغاء">
                        <i class="fas fa-ban"></i>
                    </button>
                @endif

                <button type="button" class="btn btn-sm btn-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#forceDeleteModal{{ $invoice->id }}"
                        title="حذف نهائي">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>

            <!-- Cancel Modal -->
            <div class="modal fade" id="cancelModal{{ $invoice->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">إلغاء الفاتورة</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('invoices.cancel', $invoice->id) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">سبب الإلغاء (اختياري)</label>
                                    <textarea class="form-control" name="reason" rows="3"
                                              placeholder="أدخل سبب الإلغاء..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                                <button type="submit" class="btn btn-danger">إلغاء الفاتورة</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Force Delete Modal -->
            <div class="modal fade" id="forceDeleteModal{{ $invoice->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle me-2"></i>حذف نهائي
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger">
                                <strong>تحذير!</strong> هذا الإجراء لا يمكن التراجع عنه.
                            </div>
                            <p>هل أنت متأكد من حذف الفاتورة <strong>{{ $invoice->invoice_number }}</strong> نهائياً؟</p>
                            <ul class="list-unstyled">
                                <li><strong>الطالب:</strong> {{ $invoice->student->name }}</li>
                                <li><strong>المبلغ الإجمالي:</strong> ${{ number_format($invoice->total_amount, 2) }}</li>
                                <li><strong>الحالة:</strong> 
                                    @php
                                        $statusLabels = [
                                            'draft' => 'مسودة',
                                            'issued' => 'صادرة',
                                            'partial' => 'مدفوعة جزئياً',
                                            'paid' => 'مدفوعة',
                                            'cancelled' => 'ملغاة',
                                            'refunded' => 'مستردة'
                                        ];
                                    @endphp
                                    {{ $statusLabels[$invoice->status] ?? $invoice->status }}
                                </li>
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                            <form action="{{ route('invoices.force-delete', $invoice->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('POST')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash-alt me-2"></i>حذف نهائياً
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="11" class="text-center py-5">
            <div class="text-muted">
                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                <h5>لا توجد فواتير</h5>
            </div>
        </td>
    </tr>
@endforelse

