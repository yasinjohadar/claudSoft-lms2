<div class="modal fade" id="membershipWaInviteModal" tabindex="-1" aria-labelledby="membershipWaInviteModalTitle" aria-hidden="true"
     data-preview-url="{{ route('courses.groups.membership-requests.preview-whatsapp-invite', [$course->id, $group->id]) }}"
     data-send-url="{{ route('courses.groups.membership-requests.send-whatsapp-invite', [$course->id, $group->id]) }}"
     data-default-template-id="{{ $defaultWhatsappTemplateId ?? '' }}">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="membershipWaInviteModalTitle">
                    <i class="ri-whatsapp-line me-2 text-success"></i>دعوة للانضمام — واتساب
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="alert alert-success py-2 mb-3">
                    <div class="small mb-1"><strong>الطالب:</strong> <span id="membershipWaInviteStudentName">—</span></div>
                    <div class="small mb-0"><strong>الواتساب:</strong> <span id="membershipWaInviteStudentPhone" dir="ltr">—</span></div>
                </div>
                <div id="membershipWaInviteAlert" class="alert d-none mb-3" role="alert"></div>

                <input type="hidden" id="membershipWaInviteStudentId" value="">

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="membershipWaInviteTemplateId">
                        قالب الواتساب <span class="text-danger">*</span>
                    </label>
                    <select id="membershipWaInviteTemplateId" class="form-select" required>
                        <option value="">اختر قالباً...</option>
                        @foreach($whatsappTemplates ?? [] as $template)
                            <option value="{{ $template->id }}"
                                @selected(($defaultWhatsappTemplateId ?? null) == $template->id)>
                                {{ $template->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-2">
                        المتغيرات: <code>{student_name}</code>، <code>{group_name}</code>، <code>{course_name}</code>، <code>{group_link}</code>، <code>{email}</code>
                        @if(empty($waContext['whatsapp_group_link']))
                            — <span class="text-warning">أضف رابط المجموعة من <a href="{{ route('admin.group-registration-settings.index', $group->id) }}">إعدادات التسجيل</a>.</span>
                        @endif
                    </small>
                    @if(($whatsappTemplates ?? collect())->isEmpty())
                        <div class="alert alert-warning small mt-2 mb-0">
                            لا توجد قوالب نصية نشطة.
                            <a href="{{ route('admin.whatsapp-templates.create') }}">أنشئ قالباً</a>
                        </div>
                    @endif
                </div>

                <div id="membershipWaInvitePreviewWrap" class="d-none">
                    <label class="form-label fw-semibold">معاينة</label>
                    <div id="membershipWaInvitePreviewBody" class="border rounded p-3 bg-light membership-wa-invite-preview"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-outline-success" id="membershipWaInvitePreviewBtn">
                    <span class="membership-wa-btn__label"><i class="fe fe-eye me-1"></i>معاينة</span>
                    <span class="membership-wa-btn__spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>جاري التحميل...</span>
                </button>
                <button type="button" class="btn btn-success" id="membershipWaInviteSubmitBtn">
                    <span class="membership-wa-btn__label"><i class="ri-send-plane-line me-1"></i>إرسال الدعوة</span>
                    <span class="membership-wa-btn__spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>جاري الإرسال...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .membership-wa-invite-preview {
        white-space: pre-wrap;
        word-break: break-word;
        direction: rtl;
        font-size: 0.95rem;
        line-height: 1.6;
        min-height: 4rem;
    }
</style>
