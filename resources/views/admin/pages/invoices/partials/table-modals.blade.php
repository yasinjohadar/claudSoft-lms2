@foreach ($invoices as $invoice)
    @if($invoice->status !== 'cancelled' && $invoice->status !== 'paid')
        <div class="modal fade" id="cancelModal{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">إلغاء الفاتورة</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
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
    @endif

    <div class="modal fade" id="forceDeleteModal{{ $invoice->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fe fe-alert-triangle me-2"></i>حذف نهائي
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger mb-3">
                        <strong>تحذير!</strong> هذا الإجراء لا يمكن التراجع عنه.
                    </div>
                    <p>هل أنت متأكد من حذف الفاتورة <strong>{{ $invoice->invoice_number }}</strong> نهائياً؟</p>
                    <ul class="list-unstyled mb-0">
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
                                    'refunded' => 'مستردة',
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
                            <i class="fe fe-trash-2 me-2"></i>حذف نهائياً
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
