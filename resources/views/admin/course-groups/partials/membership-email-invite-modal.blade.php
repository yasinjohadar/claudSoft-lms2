<div class="modal fade" id="membershipEmailInviteModal" tabindex="-1" aria-labelledby="membershipEmailInviteModalTitle" aria-hidden="true"
     data-preview-url="{{ route('courses.groups.membership-requests.preview-email-invite', [$course->id, $group->id]) }}"
     data-send-url="{{ route('courses.groups.membership-requests.send-email-invite', [$course->id, $group->id]) }}"
     data-default-template-id="{{ $defaultEmailTemplateId ?? '' }}">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="membershipEmailInviteModalTitle">
                    <i class="fe fe-mail me-2 text-primary"></i>دعوة عبر البريد الإلكتروني
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="alert alert-info py-2 mb-3">
                    <div class="small mb-1"><strong>الطالب:</strong> <span id="membershipEmailInviteStudentName">—</span></div>
                    <div class="small mb-0"><strong>البريد:</strong> <span id="membershipEmailInviteStudentEmail" dir="ltr">—</span></div>
                </div>
                <div id="membershipEmailInviteAlert" class="alert d-none mb-3" role="alert"></div>

                <input type="hidden" id="membershipEmailInviteStudentId" value="">

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="membershipEmailInviteTemplateId">
                        قالب البريد <span class="text-danger">*</span>
                    </label>
                    <select id="membershipEmailInviteTemplateId" class="form-select" required>
                        <option value="">اختر قالباً...</option>
                        @foreach($emailTemplates ?? [] as $template)
                            <option value="{{ $template->id }}"
                                @selected(($defaultEmailTemplateId ?? null) == $template->id)>
                                {{ $template->name_ar ?: $template->name }}
                                @if($template->subject)
                                    — {{ Str::limit($template->subject, 40) }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-2">
                        المتغيرات: <code>@{{student_name}}</code>، <code>@{{group_name}}</code>، <code>@{{course_name}}</code>، <code>@{{email}}</code>، <code>@{{group_link}}</code>
                    </small>
                    @if(($emailTemplates ?? collect())->isEmpty())
                        <div class="alert alert-warning small mt-2 mb-0">
                            لا توجد قوالب بريد نشطة.
                            <a href="{{ route('admin.email-templates.create') }}">أنشئ قالباً</a>
                        </div>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="membershipEmailInviteSettingId">حساب الإرسال (SMTP)</label>
                    <select id="membershipEmailInviteSettingId" class="form-select">
                        <option value="">الافتراضي النشط</option>
                        @foreach($emailSettings ?? [] as $setting)
                            <option value="{{ $setting->id }}"
                                @selected(($defaultEmailSetting?->id ?? null) === $setting->id)>
                                {{ $setting->mail_from_name ?? $setting->provider ?? 'SMTP' }} — {{ $setting->mail_from_address }}
                                @if($setting->is_active) (نشط) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="membershipEmailInvitePreviewWrap" class="d-none">
                    <label class="form-label fw-semibold">معاينة</label>
                    <div class="border rounded p-3 bg-light mb-2">
                        <small class="text-muted d-block mb-1">الموضوع</small>
                        <strong id="membershipEmailInvitePreviewSubject">—</strong>
                    </div>
                    <div id="membershipEmailInvitePreviewBody" class="border rounded p-3 bg-light membership-email-invite-preview"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-outline-primary" id="membershipEmailInvitePreviewBtn">
                    <span class="membership-email-btn__label"><i class="fe fe-eye me-1"></i>معاينة</span>
                    <span class="membership-email-btn__spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>جاري التحميل...</span>
                </button>
                <button type="button" class="btn btn-primary" id="membershipEmailInviteSubmitBtn">
                    <span class="membership-email-btn__label"><i class="fe fe-send me-1"></i>إرسال الدعوة</span>
                    <span class="membership-email-btn__spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>جاري الإرسال...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .membership-email-invite-preview {
        word-break: break-word;
        direction: rtl;
        font-size: 0.95rem;
        line-height: 1.6;
        min-height: 4rem;
    }
</style>
