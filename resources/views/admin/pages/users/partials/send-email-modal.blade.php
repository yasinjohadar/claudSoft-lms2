<div class="modal fade" id="sendEmailModal" tabindex="-1" aria-labelledby="sendEmailModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="sendEmailModalTitle">
                    <i class="fe fe-mail me-2 text-primary"></i>إرسال بريد للمستخدم
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="alert alert-info py-2 mb-3" role="status">
                    <div class="small">
                        <strong>المستخدم:</strong> <span id="sendEmailUserName">—</span>
                    </div>
                    <div class="small mb-0">
                        <strong>البريد:</strong> <span id="sendEmailUserEmail">—</span>
                    </div>
                </div>

                <div id="sendEmailAlert" class="alert d-none mb-3" role="alert"></div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="sendEmailTemplateId">قالب البريد <span class="text-danger">*</span></label>
                    <select id="sendEmailTemplateId" class="form-select" required>
                        <option value="">اختر قالباً...</option>
                        @foreach($emailTemplates ?? [] as $template)
                            <option value="{{ $template->id }}">
                                {{ $template->name_ar ?: $template->name }}
                                @if($template->subject)
                                    — {{ Str::limit($template->subject, 40) }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="sendEmailSettingId">حساب الإرسال (SMTP)</label>
                    <select id="sendEmailSettingId" class="form-select">
                        <option value="">الافتراضي النشط</option>
                        @foreach($emailSettings ?? [] as $setting)
                            <option value="{{ $setting->id }}"
                                @selected(($defaultEmailSetting?->id ?? null) === $setting->id)>
                                {{ $setting->mail_from_name ?? $setting->provider ?? 'SMTP' }} — {{ $setting->mail_from_address }}
                                @if($setting->is_active) (نشط) @endif
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted fs-12">يُستخدم الحساب النشط افتراضياً إذا لم تختر غيره.</small>
                </div>

                <div id="sendEmailPreviewWrap" class="admin-send-email-preview d-none">
                    <label class="form-label fw-semibold">معاينة</label>
                    <div class="admin-send-email-preview__subject mb-2">
                        <small class="text-muted d-block">الموضوع</small>
                        <strong id="sendEmailPreviewSubject">—</strong>
                    </div>
                    <div class="admin-send-email-preview__body border rounded" id="sendEmailPreviewBody"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-outline-primary" id="sendEmailPreviewBtn">
                    <span class="send-email-btn__label"><i class="fe fe-eye me-1"></i>معاينة</span>
                    <span class="send-email-btn__spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>جاري التحميل...</span>
                </button>
                <button type="button" class="btn btn-primary" id="sendEmailSubmitBtn">
                    <span class="send-email-btn__label"><i class="fe fe-send me-1"></i>إرسال</span>
                    <span class="send-email-btn__spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>جاري الإرسال...</span>
                </button>
            </div>
        </div>
    </div>
</div>
