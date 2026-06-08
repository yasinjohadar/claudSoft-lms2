<div class="modal fade" id="submitPaymentModal" tabindex="-1" aria-labelledby="submitPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('student.invoices.pay', $invoice->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="submitPaymentModalLabel">
                        <i class="fe fe-credit-card me-2 text-primary"></i>تسديد الفاتورة
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small mb-4">
                        <i class="fe fe-info me-1"></i>
                        سيتم مراجعة طلبك من قبل الإدارة بعد رفع الإيصال. لن يُخصم المبلغ من حسابك حتى تتم الموافقة.
                    </div>

                    <div class="mb-3">
                        <label for="payment_amount" class="form-label">المبلغ <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number"
                                   class="form-control @error('amount') is-invalid @enderror"
                                   id="payment_amount"
                                   name="amount"
                                   step="0.01"
                                   min="0.01"
                                   max="{{ number_format($invoice->remaining_amount, 2, '.', '') }}"
                                   value="{{ old('amount', number_format($invoice->remaining_amount, 2, '.', '')) }}"
                                   required>
                        </div>
                        <small class="text-muted">المبلغ المتبقي: ${{ number_format($invoice->remaining_amount, 2) }}</small>
                        @error('amount')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="payment_date" class="form-label">تاريخ الدفع <span class="text-danger">*</span></label>
                        <input type="date"
                               class="form-control @error('payment_date') is-invalid @enderror"
                               id="payment_date"
                               name="payment_date"
                               max="{{ date('Y-m-d') }}"
                               value="{{ old('payment_date', date('Y-m-d')) }}"
                               required>
                        @error('payment_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="payment_method_id" class="form-label">طريقة الدفع <span class="text-danger">*</span></label>
                        <select class="form-select @error('payment_method_id') is-invalid @enderror"
                                id="payment_method_id"
                                name="payment_method_id"
                                required>
                            <option value="">اختر طريقة الدفع</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}" @selected(old('payment_method_id') == $method->id)>
                                    {{ $method->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_method_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="receipt" class="form-label">إيصال الدفع (صورة أو PDF) <span class="text-danger">*</span></label>
                        <input type="file"
                               class="form-control @error('receipt') is-invalid @enderror"
                               id="receipt"
                               name="receipt"
                               accept=".jpg,.jpeg,.png,.webp,.pdf,image/*,application/pdf"
                               required>
                        <small class="text-muted">الحد الأقصى 5 ميجابايت — صيغ مسموحة: JPG, PNG, WEBP, PDF</small>
                        @error('receipt')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label for="payment_notes" class="form-label">ملاحظات (اختياري)</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  id="payment_notes"
                                  name="notes"
                                  rows="2"
                                  maxlength="1000">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
