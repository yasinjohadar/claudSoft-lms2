<div class="modal fade" id="deleteWeeklyBatchModal" tabindex="-1" aria-labelledby="deleteWeeklyBatchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="deleteWeeklyBatchModalLabel">حذف دفعة التقرير</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <span class="avatar avatar-lg bg-danger-transparent text-danger rounded-circle">
                        <i class="fe fe-trash-2 fs-20"></i>
                    </span>
                </div>
                <p class="mb-2 text-center">هل أنت متأكد من حذف دفعة التقرير:</p>
                <p class="fw-semibold text-center text-primary mb-3" id="deleteWeeklyBatchTitle"></p>
                <div class="alert alert-warning mb-0">
                    <div class="d-flex gap-2">
                        <i class="fe fe-alert-triangle mt-1"></i>
                        <div>
                            <div id="deleteWeeklyBatchStudentsText"></div>
                            <div id="deleteWeeklyBatchSubmittedWarning" class="mt-1 d-none text-danger fw-semibold"></div>
                            <div class="mt-1">لا يمكن التراجع عن هذا الإجراء.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                <form method="POST" action="{{ route('admin.weekly-reports.created.batch.destroy') }}" id="deleteWeeklyBatchForm">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="batch" id="deleteWeeklyBatchKey" value="">
                    <button type="submit" class="btn btn-danger">
                        <i class="fe fe-trash-2 me-1"></i>حذف الدفعة
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
    <script>
        (function () {
            const modalEl = document.getElementById('deleteWeeklyBatchModal');
            if (!modalEl || modalEl.dataset.bound === '1') return;
            modalEl.dataset.bound = '1';

            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            const titleEl = document.getElementById('deleteWeeklyBatchTitle');
            const studentsTextEl = document.getElementById('deleteWeeklyBatchStudentsText');
            const submittedWarningEl = document.getElementById('deleteWeeklyBatchSubmittedWarning');
            const batchKeyInput = document.getElementById('deleteWeeklyBatchKey');

            document.addEventListener('click', function (event) {
                const btn = event.target.closest('.weekly-batch-delete-btn');
                if (!btn) return;

                const title = btn.dataset.batchTitle || '';
                const studentsCount = parseInt(btn.dataset.studentsCount || '0', 10);
                const submittedCount = parseInt(btn.dataset.submittedCount || '0', 10);

                if (titleEl) titleEl.textContent = title;
                if (batchKeyInput) batchKeyInput.value = btn.dataset.batchKey || '';
                if (studentsTextEl) {
                    studentsTextEl.textContent = `سيتم حذف ${studentsCount} تقرير${studentsCount === 1 ? '' : 'اً'} من الطلاب.`;
                }
                if (submittedWarningEl) {
                    if (submittedCount > 0) {
                        submittedWarningEl.textContent = `تحذير: ${submittedCount} تقرير${submittedCount === 1 ? '' : 'اً'} تم تسليمه بالفعل.`;
                        submittedWarningEl.classList.remove('d-none');
                    } else {
                        submittedWarningEl.textContent = '';
                        submittedWarningEl.classList.add('d-none');
                    }
                }

                modal.show();
            });
        })();
    </script>
    @endpush
@endonce
