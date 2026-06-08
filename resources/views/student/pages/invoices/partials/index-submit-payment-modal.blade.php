<div class="modal fade" id="indexSubmitPaymentModal" tabindex="-1" aria-labelledby="indexSubmitPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="indexSubmitPaymentForm" method="POST" action="" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="indexSubmitPaymentModalLabel">
                        <i class="fe fe-credit-card me-2 text-primary"></i>
                        تسديد الفاتورة <span id="indexPayInvoiceNumber" class="text-muted fs-14"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small mb-4">
                        <i class="fe fe-info me-1"></i>
                        سيتم مراجعة طلبك من قبل الإدارة بعد رفع الإيصال.
                    </div>

                    <div class="mb-3">
                        <label for="index_payment_amount" class="form-label">المبلغ <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number"
                                   class="form-control"
                                   id="index_payment_amount"
                                   name="amount"
                                   step="0.01"
                                   min="0.01"
                                   required>
                        </div>
                        <small class="text-muted">المبلغ المتبقي: $<span id="indexPayRemaining"></span></small>
                    </div>

                    <div class="mb-3">
                        <label for="index_payment_date" class="form-label">تاريخ الدفع <span class="text-danger">*</span></label>
                        <input type="date"
                               class="form-control"
                               id="index_payment_date"
                               name="payment_date"
                               max="{{ date('Y-m-d') }}"
                               value="{{ date('Y-m-d') }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="index_payment_method_id" class="form-label">طريقة الدفع <span class="text-danger">*</span></label>
                        <select class="form-select" id="index_payment_method_id" name="payment_method_id" required>
                            <option value="">اختر طريقة الدفع</option>
                            @foreach($paymentMethods ?? [] as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="index_receipt" class="form-label">إيصال الدفع (صورة أو PDF) <span class="text-danger">*</span></label>
                        <input type="file"
                               class="form-control"
                               id="index_receipt"
                               name="receipt"
                               accept=".jpg,.jpeg,.png,.webp,.pdf,image/*,application/pdf"
                               required>
                        <small class="text-muted">الحد الأقصى 5 ميجابايت</small>
                    </div>

                    <div class="mb-0">
                        <label for="index_payment_notes" class="form-label">ملاحظات (اختياري)</label>
                        <textarea class="form-control" id="index_payment_notes" name="notes" rows="2" maxlength="1000"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fe fe-upload me-1"></i>إرسال طلب الدفع
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
