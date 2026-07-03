<div class="modal fade" id="membershipTgInviteModal" tabindex="-1" aria-hidden="true"
     data-preview-url="{{ route('courses.groups.membership-requests.preview-telegram-invite', [$course->id, $group->id]) }}"
     data-send-url="{{ route('courses.groups.membership-requests.send-telegram-invite', [$course->id, $group->id]) }}"
     data-default-template-id="{{ $defaultTelegramTemplateId ?? '' }}">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold"><i class="ri-telegram-line me-2 text-info"></i>دعوة — Telegram</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small mb-3">
                    <strong>الطالب:</strong> <span id="membershipTgInviteStudentName">—</span>
                </div>
                <div id="membershipTgInviteAlert" class="alert d-none"></div>
                <input type="hidden" id="membershipTgInviteStudentId">
                <div class="mb-3">
                    <label class="form-label">قالب Telegram</label>
                    <select id="membershipTgInviteTemplateId" class="form-select" required>
                        <option value="">اختر قالباً...</option>
                        @foreach($telegramTemplates ?? [] as $template)
                            <option value="{{ $template->id }}" @selected(($defaultTelegramTemplateId ?? null) == $template->id)>{{ $template->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">يجب أن يكون الطالب قد ربط Telegram من ملفه.</small>
                </div>
                <div id="membershipTgInvitePreviewWrap" class="d-none">
                    <div id="membershipTgInvitePreviewBody" class="border rounded p-3 bg-light" style="white-space:pre-wrap"></div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-outline-info" id="membershipTgInvitePreviewBtn">معاينة</button>
                <button type="button" class="btn btn-info text-white" id="membershipTgInviteSubmitBtn">إرسال</button>
            </div>
        </div>
    </div>
</div>
