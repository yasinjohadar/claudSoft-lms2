<div class="modal fade student-leaderboard-player-modal" id="leaderboardPlayerModal" tabindex="-1" aria-labelledby="leaderboardPlayerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="leaderboardPlayerModalLabel">بطاقة المتصدر</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="student-leaderboard-player-modal__hero text-center">
                    <div class="student-leaderboard-player-modal__rank js-lb-modal-rank"></div>
                    <div class="student-leaderboard-player-modal__avatar-wrap">
                        <img src="" alt="" class="student-leaderboard-player-modal__avatar js-lb-modal-photo" hidden>
                        <span class="student-leaderboard-player-modal__avatar-fallback js-lb-modal-initials"></span>
                    </div>
                    <h5 class="student-leaderboard-player-modal__name mb-1">
                        <span class="js-lb-modal-name"></span>
                        <span class="student-leaderboard-user-name__ar js-lb-modal-name-ar d-block" hidden></span>
                    </h5>
                    <div class="js-lb-modal-me-badge mb-2" hidden>
                        <span class="badge bg-primary">أنت</span>
                    </div>
                    <span class="js-lb-modal-division"></span>
                </div>

                <div class="student-leaderboard-player-modal__stats">
                    <div class="student-leaderboard-player-modal__stat">
                        <span class="student-leaderboard-player-modal__stat-label">الترتيب</span>
                        <strong class="js-lb-modal-rank-num"></strong>
                    </div>
                    <div class="student-leaderboard-player-modal__stat">
                        <span class="student-leaderboard-player-modal__stat-label js-lb-modal-metric-label">النتيجة</span>
                        <strong class="text-primary js-lb-modal-score"></strong>
                    </div>
                    <div class="student-leaderboard-player-modal__stat js-lb-modal-change-wrap" hidden>
                        <span class="student-leaderboard-player-modal__stat-label">التغيّر</span>
                        <strong class="js-lb-modal-change"></strong>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-3 js-lb-modal-actions"></div>
            </div>
        </div>
    </div>
</div>
