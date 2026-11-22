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

<div class="modal fade" id="approvePaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="approvePaymentForm" method="POST" action="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fe fe-check-circle me-2 text-success"></i>تأكيد الموافقة على الدفعة
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <small class="text-muted d-block">المبلغ الذي سيتم اعتماده</small>
                        <div class="fs-1 fw-bold text-success lh-sm" id="approveAmount"></div>
                        <span class="badge mt-1" id="approveResultBadge"></span>
                    </div>

                    <ul class="list-group list-group-flush mb-3 small">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">الطالب</span><strong id="approveStudent"></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between gap-3 px-0">
                            <span class="text-muted text-nowrap">المعسكر / الدبلوم</span><strong class="text-primary text-end" id="approveProgram"></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">الفاتورة / الدفعة</span><span><span id="approveInvoice"></span> · <span id="approvePaymentNumber"></span></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">طريقة الدفع</span><span id="approveMethod"></span>
                        </li>
                    </ul>

                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="border rounded p-2 h-100">
                                <small class="text-muted d-block">قيمة الفاتورة</small>
                                <strong id="approveTotal"></strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2 h-100">
                                <small class="text-muted d-block">المدفوع بعد الاعتماد</small>
                                <strong class="text-success" id="approvePaidAfter"></strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2 h-100">
                                <small class="text-muted d-block">المتبقي بعد الاعتماد</small>
                                <strong class="text-danger" id="approveRemainingAfter"></strong>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-danger small mt-3 mb-0 d-none" id="approveTooBig">
                        <i class="fe fe-alert-triangle me-1"></i>المبلغ أكبر من المتبقي على الفاتورة — عدّل المبلغ قبل الموافقة.
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-outline-primary js-edit-payment-amount" id="approveEditBtn">
                        <i class="fe fe-edit-2 me-1"></i>تعديل المبلغ
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-success" id="approveSubmitBtn">
                            <i class="fe fe-check me-1"></i>موافقة وتسجيل الدفعة
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        var updateAmountUrl = @json(route('payments.update-amount', ['id' => '__ID__']));
        var approveUrl = @json(route('payments.approve', ['id' => '__ID__']));

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

        function money(v) {
            return '$' + (Math.round(Number(v || 0) * 100) / 100).toFixed(2);
        }

        function openApproveModal(d) {
            var modal = moveToBody(document.getElementById('approvePaymentModal'));
            var amount = Number(d.amount || 0);
            var total = Number(d.total || 0);
            var paid = Number(d.paid || 0);
            var remaining = Number(d.remaining || 0);
            var paidAfter = paid + amount;
            var remainingAfter = Math.max(0, Math.round((remaining - amount) * 100) / 100);
            var tooBig = amount - remaining > 0.004;

            document.getElementById('approvePaymentForm').action = approveUrl.replace('__ID__', d.paymentId);
            document.getElementById('approveAmount').textContent = money(amount);
            document.getElementById('approveStudent').textContent = d.studentName || '—';
            document.getElementById('approveProgram').textContent = d.program || '—';
            document.getElementById('approveInvoice').textContent = d.invoiceNumber || '—';
            document.getElementById('approvePaymentNumber').textContent = d.paymentNumber || '';
            document.getElementById('approveMethod').textContent = d.method || '—';
            document.getElementById('approveTotal').textContent = money(total);
            document.getElementById('approvePaidAfter').textContent = money(paidAfter);
            document.getElementById('approveRemainingAfter').textContent = money(remainingAfter);

            var badge = document.getElementById('approveResultBadge');
            if (tooBig) {
                badge.className = 'badge mt-1 bg-danger-transparent text-danger';
                badge.textContent = 'أكبر من المتبقي (' + money(remaining) + ')';
            } else if (remainingAfter <= 0) {
                badge.className = 'badge mt-1 bg-success-transparent text-success';
                badge.textContent = 'ستُسدَّد الفاتورة بالكامل';
            } else {
                badge.className = 'badge mt-1 bg-warning-transparent text-warning';
                badge.textContent = 'دفعة جزئية — يبقى ' + money(remainingAfter);
            }
            document.getElementById('approveTooBig').classList.toggle('d-none', !tooBig);
            document.getElementById('approveSubmitBtn').disabled = tooBig;

            var editBtn = document.getElementById('approveEditBtn');
            editBtn.dataset.paymentId = d.paymentId;
            editBtn.dataset.paymentNumber = d.paymentNumber || '';
            editBtn.dataset.amount = d.amount;
            editBtn.dataset.remaining = d.remaining;

            bootstrap.Modal.getOrCreateInstance(modal).show();
        }

        // يفتح مودالاً بعد إغلاق المودال المفتوح (إن وُجد) حتى لا تتداخل الخلفيات
        function openAfterClosing(trigger, opener) {
            var open = trigger.closest('.modal.show');
            if (open) {
                open.addEventListener('hidden.bs.modal', opener, { once: true });
                bootstrap.Modal.getInstance(open).hide();
            } else {
                opener();
            }
        }

        document.addEventListener('click', function (e) {
            var editBtn = e.target.closest('.js-edit-payment-amount');
            if (editBtn) {
                e.preventDefault();
                var data = Object.assign({}, editBtn.dataset);
                openAfterClosing(editBtn, function () { openEditAmountModal(data); });
                return;
            }

            var approveBtn = e.target.closest('.js-approve-payment');
            if (approveBtn) {
                e.preventDefault();
                var aData = Object.assign({}, approveBtn.dataset);
                openAfterClosing(approveBtn, function () { openApproveModal(aData); });
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
                    var approveBtnEl = document.createElement('button');
                    approveBtnEl.type = 'button';
                    approveBtnEl.className = 'btn btn-success js-approve-payment';
                    approveBtnEl.innerHTML = '<i class="fe fe-check me-1"></i>موافقة ($' + escapeHtml(p.amount) + ')';
                    Object.keys(p).forEach(function (k) { approveBtnEl.dataset[k] = p[k]; });
                    actions.appendChild(approveBtnEl);

                    var editBtnEl = document.createElement('button');
                    editBtnEl.type = 'button';
                    editBtnEl.className = 'btn btn-outline-primary js-edit-payment-amount';
                    editBtnEl.innerHTML = '<i class="fe fe-edit-2 me-1"></i>تعديل المبلغ';
                    editBtnEl.dataset.paymentId = p.paymentId;
                    editBtnEl.dataset.paymentNumber = p.paymentNumber || '';
                    editBtnEl.dataset.amount = p.amount;
                    editBtnEl.dataset.remaining = p.remaining;
                    actions.appendChild(editBtnEl);
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
