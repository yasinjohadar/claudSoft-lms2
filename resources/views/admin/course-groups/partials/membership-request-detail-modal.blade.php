<div class="modal fade" id="membershipRequestDetailModal" tabindex="-1" aria-labelledby="membershipRequestDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-semibold mb-1" id="membershipRequestDetailModalLabel">
                        <i class="fe fe-file-text me-2 text-primary"></i>
                        <span id="membershipRequestDetailTitle">بيانات طلب الانضمام</span>
                    </h5>
                    <p class="text-muted small mb-0">مراجعة بيانات الفورم قبل اتخاذ القرار</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body pt-3" id="membershipRequestDetailBody">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                    <p class="mb-0 small">جاري تحميل البيانات...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .membership-request-detail__form-data .card {
        box-shadow: none;
        border: 1px solid var(--default-border);
    }
    .membership-request-detail__form-data .card:last-child {
        margin-bottom: 0 !important;
    }
    #membershipRequestDetailModal .modal-body {
        max-height: min(78vh, 900px);
    }
</style>
