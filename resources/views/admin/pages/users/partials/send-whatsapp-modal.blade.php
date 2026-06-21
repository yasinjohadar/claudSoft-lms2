<div class="modal fade" id="sendWhatsAppModal" tabindex="-1" aria-labelledby="sendWhatsAppModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="sendWhatsAppModalTitle">
                    <i class="ri-whatsapp-line me-2 text-success"></i>إرسال واتساب للمستخدم
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="alert alert-success py-2 mb-3" role="status">
                    <div class="small mb-1">
                        <strong>المستخدم:</strong> <span id="sendWhatsAppUserName">—</span>
                    </div>
                    <div class="small mb-0">
                        <strong>الواتساب:</strong> <span id="sendWhatsAppUserPhone" dir="ltr">—</span>
                    </div>
                </div>

                <div id="sendWhatsAppAlert" class="alert d-none mb-3" role="alert"></div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="sendWhatsAppTemplateId">قالب الواتساب <span class="text-danger">*</span></label>
                    <select id="sendWhatsAppTemplateId" class="form-select" required>
                        <option value="">اختر قالباً...</option>
                        @foreach($whatsappTemplates ?? [] as $template)
                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                        @endforeach
                    </select>
                </div>

                @if(($evolutionInstances ?? collect())->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="sendWhatsAppInstanceName">Instance Evolution</label>
                        <select id="sendWhatsAppInstanceName" class="form-select">
                            <option value="">الافتراضي النشط</option>
                            @foreach($evolutionInstances as $instance)
                                <option value="{{ $instance->instance_name }}"
                                    @selected(($defaultEvolutionInstance?->instance_name ?? null) === $instance->instance_name)>
                                    {{ $instance->instance_name }}
                                    @if($instance->profile_name)
                                        — {{ $instance->profile_name }}
                                    @endif
                                    @if($instance->is_default) (افتراضي) @endif
                                    @if($instance->connection_status === 'open') (متصل) @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted fs-12">يُستخدم Instance النشط من الإعدادات إذا لم تختر غيره.</small>
                    </div>
                @endif

                <div id="sendWhatsAppPreviewWrap" class="admin-send-whatsapp-preview d-none">
                    <label class="form-label fw-semibold">معاينة</label>
                    <div class="admin-send-whatsapp-preview__body border rounded p-3 bg-light" id="sendWhatsAppPreviewBody"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-outline-success" id="sendWhatsAppPreviewBtn">
                    <span class="send-whatsapp-btn__label"><i class="fe fe-eye me-1"></i>معاينة</span>
                    <span class="send-whatsapp-btn__spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>جاري التحميل...</span>
                </button>
                <button type="button" class="btn btn-success" id="sendWhatsAppSubmitBtn">
                    <span class="send-whatsapp-btn__label"><i class="ri-send-plane-line me-1"></i>إرسال</span>
                    <span class="send-whatsapp-btn__spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>جاري الإرسال...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .admin-send-whatsapp-preview__body {
        white-space: pre-wrap;
        word-break: break-word;
        direction: rtl;
        font-size: 0.95rem;
        line-height: 1.6;
        min-height: 4rem;
    }
</style>
