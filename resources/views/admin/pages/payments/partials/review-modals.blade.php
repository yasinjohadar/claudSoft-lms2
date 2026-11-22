{{-- مودالات مشتركة لجدول المدفوعات: تعديل مبلغ دفعة معلّقة + معاينة الإيصال داخل الصفحة --}}

<div class="modal fade" id="editPaymentAmountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editPaymentAmountForm" method="POST" action="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fe fe-edit-2 me-2 text-primary"></i>تعديل مبلغ الدفعة
                        <span class="text-muted fs-14" id="editAmountPaymentNumber"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3 text-center">
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block">المبلغ الذي أدخله الطالب</small>
                                <strong class="text-warning" id="editAmountOld"></strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <small class="text-muted d-block">المتبقي على الفاتورة</small>
                                <strong class="text-danger" id="editAmountRemaining"></strong>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="editAmountInput" class="form-label fw-semibold">المبلغ الصحيح (كما في الإيصال) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0.01" class="form-control form-control-lg"
                                   id="editAmountInput" name="amount" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="editAmountReason" class="form-label">سبب التعديل (اختياري)</label>
                        <input type="text" class="form-control" id="editAmountReason" name="reason" maxlength="500"
                               placeholder="مثال: المبلغ في الإيصال 50$">
                    </div>

                    <div class="alert alert-info small mb-0">
                        <i class="fe fe-info me-1"></i>
                        يبقى طلب الدفع معلّقاً بعد التعديل، ويُسجَّل التعديل في ملاحظات الدفعة. وافق عليه بعد ذلك لاحتسابه.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary"><i class="fe fe-save me-1"></i>حفظ المبلغ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="receiptPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fe fe-file-text me-2 text-primary"></i>إيصال الدفع
                    <span class="text-muted fs-14" id="receiptPreviewTitle"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body p-2 text-center" id="receiptPreviewBody" style="min-height: 60vh;"></div>
            <div class="modal-footer justify-content-between">
                <div class="d-flex flex-wrap gap-2" id="receiptPreviewActions"></div>
                <div class="d-flex gap-2">
                    <a href="#" target="_blank" class="btn btn-outline-primary" id="receiptPreviewOpen">
                        <i class="fe fe-external-link me-1"></i>فتح في نافذة جديدة
                    </a>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var updateAmountUrl = @json(route('payments.update-amount', ['id' => '__ID__']));
        var approveUrl = @json(route('payments.approve', ['id' => '__ID__']));
        var csrf = @json(csrf_token());

        function moveToBody(el) {
            if (el && el.parentElement !== document.body) document.body.appendChild(el);
            return el;
        }

        function escapeHtml(v) {
            return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        function openEditAmountModal(d) {
            var modal = moveToBody(document.getElementById('editPaymentAmountModal'));
            document.getElementById('editPaymentAmountForm').action = updateAmountUrl.replace('__ID__', d.paymentId);
            document.getElementById('editAmountPaymentNumber').textContent = d.paymentNumber || '';
            document.getElementById('editAmountOld').textContent = '$' + d.amount;
            document.getElementById('editAmountRemaining').textContent = '$' + d.remaining;
            var input = document.getElementById('editAmountInput');
            input.value = d.amount;
            input.max = d.remaining;
            document.getElementById('editAmountReason').value = '';
            bootstrap.Modal.getOrCreateInstance(modal).show();
            setTimeout(function () { input.focus(); input.select(); }, 300);
        }

        document.addEventListener('click', function (e) {
            var editBtn = e.target.closest('.js-edit-payment-amount');
            if (editBtn) {
                e.preventDefault();
                var data = Object.assign({}, editBtn.dataset);
                var openPreview = editBtn.closest('#receiptPreviewModal');
                if (openPreview) {
                    // انتظر إغلاق المعاينة قبل فتح مودال التعديل حتى لا تتداخل الخلفيات
                    openPreview.addEventListener('hidden.bs.modal', function () { openEditAmountModal(data); }, { once: true });
                    bootstrap.Modal.getInstance(openPreview).hide();
                } else {
                    openEditAmountModal(data);
                }
                return;
            }

            var previewBtn = e.target.closest('.js-preview-receipt');
            if (previewBtn) {
                e.preventDefault();
                var pModal = moveToBody(document.getElementById('receiptPreviewModal'));
                var p = previewBtn.dataset;
                var body = document.getElementById('receiptPreviewBody');
                var url = p.receiptUrl;

                document.getElementById('receiptPreviewTitle').textContent =
                    [p.paymentNumber, p.studentName, p.amount ? '$' + p.amount : ''].filter(Boolean).join(' — ');
                document.getElementById('receiptPreviewOpen').href = url;

                body.innerHTML = p.isImage === '1'
                    ? '<img src="' + escapeHtml(url) + '" alt="إيصال الدفع" class="img-fluid rounded" style="max-height: 75vh;">'
                    : '<iframe src="' + escapeHtml(url) + '#view=FitH" title="إيصال الدفع" class="w-100 rounded border-0" style="height: 75vh;"></iframe>';

                // أزرار المراجعة السريعة للدفعات المعلّقة
                var actions = document.getElementById('receiptPreviewActions');
                actions.innerHTML = '';
                if (p.pending === '1') {
                    actions.innerHTML =
                        '<form method="POST" action="' + escapeHtml(approveUrl.replace('__ID__', p.paymentId)) + '" class="d-inline" ' +
                        'onsubmit="return confirm(\'الموافقة على هذه الدفعة بمبلغ $' + escapeHtml(p.amount) + ' وتسجيلها في حساب الطالب؟\');">' +
                        '<input type="hidden" name="_token" value="' + escapeHtml(csrf) + '">' +
                        '<button type="submit" class="btn btn-success"><i class="fe fe-check me-1"></i>موافقة ($' + escapeHtml(p.amount) + ')</button>' +
                        '</form>' +
                        '<button type="button" class="btn btn-outline-primary js-edit-payment-amount"' +
                        ' data-payment-id="' + escapeHtml(p.paymentId) + '" data-payment-number="' + escapeHtml(p.paymentNumber) + '"' +
                        ' data-amount="' + escapeHtml(p.amount) + '" data-remaining="' + escapeHtml(p.remaining) + '">' +
                        '<i class="fe fe-edit-2 me-1"></i>تعديل المبلغ</button>';
                }

                bootstrap.Modal.getOrCreateInstance(pModal).show();
            }
        });

        // أوقف تحميل الملف عند إغلاق المعاينة
        document.addEventListener('hidden.bs.modal', function (e) {
            if (e.target && e.target.id === 'receiptPreviewModal') {
                document.getElementById('receiptPreviewBody').innerHTML = '';
            }
        });
    })();
</script>
