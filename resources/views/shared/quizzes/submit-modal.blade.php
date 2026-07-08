<div class="modal fade quiz-submit-modal" id="submitModal" tabindex="-1" aria-labelledby="submitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header quiz-submit-modal__header border-0">
                <div class="quiz-submit-modal__icon-wrap" aria-hidden="true">
                    <i class="fe fe-send"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <h5 class="modal-title quiz-submit-modal__title" id="submitModalLabel">تأكيد إرسال الاختبار</h5>
                    <p class="quiz-submit-modal__subtitle mb-0">راجع ملخص إجاباتك قبل التسليم النهائي</p>
                </div>
                <button type="button" class="btn-close quiz-submit-modal__close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>

            <div class="modal-body quiz-submit-modal__body">
                <p class="quiz-submit-modal__question mb-4">هل أنت متأكد من إرسال الاختبار الآن؟</p>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="quiz-submit-stat quiz-submit-stat--success">
                            <div class="quiz-submit-stat__value" id="submit-answered-count">0</div>
                            <div class="quiz-submit-stat__label">أسئلة مُجابة</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="quiz-submit-stat quiz-submit-stat--muted" id="submit-unanswered-stat-box">
                            <div class="quiz-submit-stat__value" id="submit-unanswered-count">0</div>
                            <div class="quiz-submit-stat__label">غير مُجابة</div>
                        </div>
                    </div>
                </div>

                <div class="quiz-submit-warning" role="alert">
                    <div class="quiz-submit-warning__icon" aria-hidden="true">
                        <i class="fe fe-alert-triangle"></i>
                    </div>
                    <div>
                        <strong class="d-block mb-1">تنبيه مهم</strong>
                        <span>بعد الإرسال لن تتمكن من تعديل إجاباتك أو العودة للاختبار. تأكد من مراجعة جميع الأسئلة.</span>
                    </div>
                </div>

                <div class="alert alert-danger d-none mt-3 mb-0 quiz-submit-incomplete-warning" id="submit-incomplete-warning" role="alert">
                    <i class="fe fe-alert-circle me-1"></i>
                    يجب الإجابة على <strong>جميع</strong> الأسئلة قبل التسليم. راجع الأسئلة غير المُجابة من القائمة الجانبية.
                </div>
            </div>

            <div class="modal-footer quiz-submit-modal__footer border-0">
                <button type="button" class="btn btn-light btn-lg quiz-submit-modal__btn-cancel" data-bs-dismiss="modal">
                    <i class="fe fe-x me-1"></i>متابعة الحل
                </button>
                <button type="button" class="btn btn-success btn-lg quiz-submit-modal__btn-submit" id="confirm-submit-quiz" onclick="submitQuiz()" disabled>
                    <i class="fe fe-check-circle me-1"></i>إرسال الاختبار الآن
                </button>
            </div>
        </div>
    </div>
</div>

<script>
if (typeof window.applyQuizSubmitModalState !== 'function') {
    window.applyQuizSubmitModalState = function(answeredCount, totalQuestions) {
        const unansweredCount = Math.max(0, totalQuestions - answeredCount);
        const canSubmit = totalQuestions > 0 && unansweredCount === 0;

        const answeredEl = document.getElementById('submit-answered-count');
        const unansweredEl = document.getElementById('submit-unanswered-count');
        if (answeredEl) {
            answeredEl.textContent = String(answeredCount);
        }
        if (unansweredEl) {
            unansweredEl.textContent = String(unansweredCount);
        }

        const unansweredBox = document.getElementById('submit-unanswered-stat-box');
        if (unansweredBox) {
            unansweredBox.classList.toggle('quiz-submit-stat--danger', unansweredCount > 0);
            unansweredBox.classList.toggle('quiz-submit-stat--muted', unansweredCount === 0);
        }

        const incompleteWarning = document.getElementById('submit-incomplete-warning');
        if (incompleteWarning) {
            incompleteWarning.classList.toggle('d-none', canSubmit);
        }

        const confirmBtn = document.getElementById('confirm-submit-quiz');
        if (confirmBtn) {
            confirmBtn.disabled = !canSubmit;
        }

        return canSubmit;
    };
}
</script>
