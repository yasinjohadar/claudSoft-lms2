<div class="modal fade" id="platformReviewModal" tabindex="-1" aria-labelledby="platformReviewModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 align-items-start">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <div id="prmAvatar" class="avatar avatar-lg rounded-circle bg-primary-transparent flex-shrink-0 overflow-hidden"></div>
                    <div class="min-w-0">
                        <h5 class="modal-title fw-bold mb-1" id="platformReviewModalTitle">—</h5>
                        <div class="small text-muted" id="prmMeta"></div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="platform-reviews-stars fs-18" id="prmStars"></span>
                    <span class="fw-semibold" id="prmRatingNum"></span>
                    <span id="prmStatus"></span>
                    <span id="prmFeatured"></span>
                    <span class="text-muted small ms-auto"><i class="fe fe-calendar me-1"></i><span id="prmDate"></span></span>
                </div>

                <label class="form-label fw-semibold mb-2">نص التقييم</label>
                <div id="prmText" class="prm-text border rounded p-3 mb-3"></div>

                <div id="prmSuggestionWrap" class="d-none">
                    <label class="form-label fw-semibold mb-2">الاقتراحات</label>
                    <div id="prmSuggestion" class="prm-text border rounded p-3"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 d-flex flex-wrap justify-content-between gap-2">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light" id="prmPrev" title="السابق"><i class="fe fe-chevron-right"></i> السابق</button>
                    <button type="button" class="btn btn-light" id="prmNext" title="التالي">التالي <i class="fe fe-chevron-left"></i></button>
                    <a href="#" class="btn btn-outline-secondary" id="prmShowLink" title="الصفحة الكاملة"><i class="fe fe-external-link"></i></a>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <form method="POST" id="prmApproveForm" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success"><i class="fe fe-check me-1"></i>موافقة</button>
                    </form>
                    <form method="POST" id="prmRejectForm" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning"><i class="fe fe-x me-1"></i>رفض</button>
                    </form>
                    <form method="POST" id="prmFeaturedForm" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-info" id="prmFeaturedBtn"><i class="fe fe-heart me-1"></i><span>تمييز</span></button>
                    </form>
                    <form method="POST" id="prmDeleteForm" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا التقييم؟');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger-light"><i class="fe fe-trash-2 me-1"></i>حذف</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #platformReviewModal .prm-text {
        white-space: pre-wrap;
        word-break: break-word;
        font-size: 1.05rem;
        line-height: 1.9;
        max-height: 50vh;
        overflow-y: auto;
    }
    #platformReviewModal #prmAvatar img { width: 100%; height: 100%; object-fit: cover; }
    #platformReviewModal #prmAvatar { display: flex; align-items: center; justify-content: center; }
</style>
