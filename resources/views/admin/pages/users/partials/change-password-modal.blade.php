<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-5">
                <form id="changePasswordForm" novalidate>
                    <input type="hidden" id="changePasswordUserId" value="">

                    <div class="text-center mb-4">
                        <span class="avatar avatar-xl bg-primary-transparent text-primary rounded-circle d-inline-flex align-items-center justify-content-center change-password-modal__icon">
                            <i class="fas fa-key fa-3x"></i>
                        </span>
                    </div>

                    <h5 class="modal-title text-center mb-4 fw-bold" id="changePasswordModalTitle">
                        <i class="fas fa-lock me-2 text-primary"></i>
                        تعديل كلمة المرور
                    </h5>

                    <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                        <i class="fas fa-user-circle me-2 fs-5"></i>
                        <div>
                            <strong>المستخدم:</strong>
                            <span id="changePasswordUserName">—</span>
                        </div>
                    </div>

                    <div id="changePasswordAlert" class="alert d-none mb-3" role="alert"></div>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="changePasswordGenerateBtn">
                            <i class="fas fa-dice me-1"></i>توليد كلمة مرور قوية
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="changePasswordCopyBtn">
                            <i class="fas fa-copy me-1"></i>نسخ
                        </button>
                        <span class="badge bg-secondary align-self-center" id="changePasswordStrengthBadge">—</span>
                    </div>

                    <div class="mb-4">
                        <label for="changePasswordInput" class="form-label fw-semibold">
                            <i class="fas fa-lock me-2 text-primary"></i>
                            كلمة المرور الجديدة
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-key text-muted"></i>
                            </span>
                            <input type="password"
                                   name="password"
                                   id="changePasswordInput"
                                   class="form-control"
                                   placeholder="أدخل كلمة المرور الجديدة"
                                   autocomplete="new-password"
                                   required>
                            <button type="button" class="btn btn-outline-secondary js-toggle-password" data-target="changePasswordInput">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback d-block" id="changePasswordInputError"></div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            12 حرفاً على الأقل، أحرف كبيرة وصغيرة، أرقام، ورموز
                        </small>
                    </div>

                    <div class="mb-4">
                        <label for="changePasswordConfirmInput" class="form-label fw-semibold">
                            <i class="fas fa-lock me-2 text-primary"></i>
                            تأكيد كلمة المرور
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-key text-muted"></i>
                            </span>
                            <input type="password"
                                   name="password_confirmation"
                                   id="changePasswordConfirmInput"
                                   class="form-control"
                                   placeholder="أعد إدخال كلمة المرور"
                                   autocomplete="new-password"
                                   required>
                            <button type="button" class="btn btn-outline-secondary js-toggle-password" data-target="changePasswordConfirmInput">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback d-block" id="changePasswordConfirmInputError"></div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="send_credentials" id="sendCredentialsCheckbox" value="1" checked>
                            <label class="form-check-label" for="sendCredentialsCheckbox">
                                إرسال بيانات الدخول للطالب عبر البريد والواتساب
                            </label>
                        </div>
                        <small class="text-muted">يتضمن الاسم والبريد وكلمة المرور الجديدة وفق القوالب المخصصة.</small>
                    </div>

                    <div class="d-flex justify-content-center gap-3 mt-4">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>إغلاق
                        </button>
                        <button type="submit" class="btn btn-primary px-4" id="changePasswordSubmitBtn">
                            <i class="fas fa-save me-2"></i>تعديل كلمة المرور
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .change-password-modal__icon {
        width: 80px;
        height: 80px;
    }

    .bg-primary-transparent {
        background: rgba(102, 126, 234, 0.1) !important;
    }
</style>
