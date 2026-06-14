<div class="modal fade student-achievement-modal" id="achievementDetailModal" tabindex="-1" aria-labelledby="achievementDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content student-achievement-modal__content">
            <div class="student-achievement-modal__hero">
                <button type="button" class="btn-close student-achievement-modal__close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                <div class="student-achievement-modal__icon" id="achievementModalIcon">🏆</div>
                <span class="student-achievement-modal__tier badge" id="achievementModalTier">—</span>
                <h5 class="student-achievement-modal__title" id="achievementModalTitle">—</h5>
                <p class="student-achievement-modal__desc" id="achievementModalDesc"></p>
            </div>
            <div class="modal-body">
                <div class="student-achievement-modal__stats">
                    <div class="student-achievement-modal__stat">
                        <span class="student-achievement-modal__stat-label">المتطلب</span>
                        <strong id="achievementModalRequirement">—</strong>
                    </div>
                    <div class="student-achievement-modal__stat">
                        <span class="student-achievement-modal__stat-label">المكافأة</span>
                        <strong id="achievementModalPoints">—</strong>
                    </div>
                    <div class="student-achievement-modal__stat">
                        <span class="student-achievement-modal__stat-label">التقدم</span>
                        <strong id="achievementModalProgress">—</strong>
                    </div>
                </div>

                <div id="achievementModalProgressWrap" class="student-achievement-modal__progress-wrap d-none">
                    <div class="student-achievement-modal__track">
                        <div class="student-achievement-modal__bar" id="achievementModalBar"></div>
                    </div>
                </div>

                <div id="achievementModalCompletedWrap" class="student-achievement-modal__completed d-none">
                    <span class="badge bg-success-transparent"><i class="fe fe-check-circle me-1"></i>مكتمل</span>
                    <small class="text-muted d-block mt-2" id="achievementModalCompletedAt"></small>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <a href="#" class="btn btn-primary rounded-pill" id="achievementModalShowLink">
                    <i class="fe fe-external-link me-1"></i>صفحة الإنجاز
                </a>
                <form action="#" method="POST" class="d-none" id="achievementModalClaimForm">
                    @csrf
                    <button type="submit" class="btn btn-warning rounded-pill">
                        <i class="fe fe-package me-1"></i>المطالبة بالمكافأة
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
