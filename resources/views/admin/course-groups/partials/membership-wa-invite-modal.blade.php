<div class="modal fade" id="membershipWaInviteModal" tabindex="-1" aria-labelledby="membershipWaInviteModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="membershipWaInviteModalTitle">
                    <i class="ri-whatsapp-line me-2 text-success"></i>دعوة للانضمام — واتساب
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form id="membershipWaInviteForm"
                  action="{{ route('courses.groups.membership-requests.send-whatsapp-invite', [$course->id, $group->id]) }}"
                  method="POST"
                  data-group-name="{{ $group->name }}"
                  data-group-link="{{ $waContext['whatsapp_group_link'] ?? '' }}">
                @csrf
                <input type="hidden" name="student_id" id="membershipWaInviteStudentId" value="">
                <div class="modal-body pt-3">
                    <div class="alert alert-success py-2 mb-3">
                        <div class="small mb-1"><strong>الطالب:</strong> <span id="membershipWaInviteStudentName">—</span></div>
                        <div class="small mb-0"><strong>الواتساب:</strong> <span id="membershipWaInviteStudentPhone" dir="ltr">—</span></div>
                    </div>
                    <div id="membershipWaInviteAlert" class="alert d-none mb-3" role="alert"></div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="membershipWaInviteMessage">نص الرسالة</label>
                        <textarea name="message" id="membershipWaInviteMessage" class="form-control" rows="6"
                                  data-default-message="{{ e($waContext['default_invite_message'] ?? '') }}">{{ $waContext['default_invite_message'] ?? '' }}</textarea>
                        <small class="text-muted d-block mt-2">
                            المتغيرات: <code>{student_name}</code>، <code>{group_name}</code>، <code>{group_link}</code>
                            @if(empty($waContext['whatsapp_group_link']))
                                — <span class="text-warning">أضف رابط المجموعة من <a href="{{ route('admin.group-registration-settings.index', $group->id) }}">إعدادات التسجيل</a>.</span>
                            @endif
                        </small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success" id="membershipWaInviteSubmitBtn">
                        <i class="ri-send-plane-line me-1"></i>إرسال الدعوة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
