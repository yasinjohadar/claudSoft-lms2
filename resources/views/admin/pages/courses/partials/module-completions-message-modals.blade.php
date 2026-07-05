@php
    $groupId = request('group_id');
@endphp

{{-- WhatsApp --}}
<div class="modal fade" id="moduleCompletionWaModal" tabindex="-1" aria-labelledby="moduleCompletionWaModalTitle" aria-hidden="true"
     data-preview-url="{{ route('courses.modules.completions.preview-whatsapp', [$course->id, $module->id]) }}"
     data-send-url="{{ route('courses.modules.completions.send-whatsapp', [$course->id, $module->id]) }}"
     data-group-id="{{ $groupId }}">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="moduleCompletionWaModalTitle">
                    <i class="ri-whatsapp-line me-2 text-success"></i>رسالة واتساب للطالب
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="alert alert-success py-2 mb-3">
                    <div class="small mb-1"><strong>الطالب:</strong> <span id="moduleCompletionWaStudentName">—</span></div>
                    <div class="small mb-0"><strong>الواتساب:</strong> <span id="moduleCompletionWaStudentPhone" dir="ltr">—</span></div>
                </div>

                @if($evolutionRotationEnabled ?? false)
                    <div class="alert alert-{{ ($rotationPoolCount ?? 0) >= 2 ? 'info' : 'warning' }} py-2 mb-3 small">
                        <i class="ri-shuffle-line me-1"></i>
                        @if(($rotationPoolCount ?? 0) >= 2)
                            سيتم التبديل تلقائياً بين <strong>{{ $rotationPoolCount }}</strong> جلسات واتساب عند كل إرسال.
                        @else
                            جلسة واتساب واحدة فقط متاحة للتبديل — سيُستخدم نفس الرقم حتى تُفعّل جلسات إضافية.
                        @endif
                    </div>
                @endif

                <div id="moduleCompletionWaAlert" class="alert d-none mb-3" role="alert"></div>

                <input type="hidden" id="moduleCompletionWaStudentId" value="">
                <input type="hidden" id="moduleCompletionWaCompletionId" value="">

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="moduleCompletionWaTemplateId">
                        قالب الواتساب <span class="text-danger">*</span>
                    </label>
                    <select id="moduleCompletionWaTemplateId" class="form-select" required>
                        <option value="">اختر قالباً...</option>
                        @foreach($whatsappTemplates ?? [] as $template)
                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-2">
                        المتغيرات: <code>{student_name}</code>، <code>{course_name}</code>، <code>{module_name}</code>،
                        <code>{module_type}</code>، <code>{completion_status}</code>، <code>{completed_at}</code>، <code>{email}</code>
                    </small>
                    @if(($whatsappTemplates ?? collect())->isEmpty())
                        <div class="alert alert-warning small mt-2 mb-0">
                            لا توجد قوالب نصية نشطة.
                            <a href="{{ route('admin.whatsapp-templates.create') }}">أنشئ قالباً</a>
                        </div>
                    @endif
                </div>

                <div id="moduleCompletionWaPreviewWrap" class="d-none">
                    <label class="form-label fw-semibold">معاينة</label>
                    <div id="moduleCompletionWaPreviewBody" class="border rounded p-3 bg-light module-completions-preview"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-outline-success" id="moduleCompletionWaPreviewBtn">
                    <span class="module-wa-btn__label"><i class="fe fe-eye me-1"></i>معاينة</span>
                    <span class="module-wa-btn__spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>جاري التحميل...</span>
                </button>
                <button type="button" class="btn btn-success" id="moduleCompletionWaSubmitBtn">
                    <span class="module-wa-btn__label"><i class="ri-send-plane-line me-1"></i>إرسال</span>
                    <span class="module-wa-btn__spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>جاري الإرسال...</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Email --}}
<div class="modal fade" id="moduleCompletionEmailModal" tabindex="-1" aria-labelledby="moduleCompletionEmailModalTitle" aria-hidden="true"
     data-preview-url="{{ route('courses.modules.completions.preview-email', [$course->id, $module->id]) }}"
     data-send-url="{{ route('courses.modules.completions.send-email', [$course->id, $module->id]) }}"
     data-group-id="{{ $groupId }}">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="moduleCompletionEmailModalTitle">
                    <i class="fe fe-mail me-2 text-primary"></i>رسالة بريد للطالب
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="alert alert-info py-2 mb-3">
                    <div class="small mb-1"><strong>الطالب:</strong> <span id="moduleCompletionEmailStudentName">—</span></div>
                    <div class="small mb-0"><strong>البريد:</strong> <span id="moduleCompletionEmailStudentEmail" dir="ltr">—</span></div>
                </div>
                <div id="moduleCompletionEmailAlert" class="alert d-none mb-3" role="alert"></div>

                <input type="hidden" id="moduleCompletionEmailStudentId" value="">
                <input type="hidden" id="moduleCompletionEmailCompletionId" value="">

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="moduleCompletionEmailTemplateId">
                        قالب البريد <span class="text-danger">*</span>
                    </label>
                    <select id="moduleCompletionEmailTemplateId" class="form-select" required>
                        <option value="">اختر قالباً...</option>
                        @foreach($emailTemplates ?? [] as $template)
                            <option value="{{ $template->id }}">
                                {{ $template->name_ar ?: $template->name }}
                                @if($template->subject)
                                    — {{ \Illuminate\Support\Str::limit($template->subject, 40) }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted d-block mt-2">
                        المتغيرات: <code>@{{student_name}}</code>، <code>@{{course_name}}</code>، <code>@{{module_name}}</code>،
                        <code>@{{module_type}}</code>، <code>@{{completion_status}}</code>، <code>@{{completed_at}}</code>، <code>@{{email}}</code>
                    </small>
                    @if(($emailTemplates ?? collect())->isEmpty())
                        <div class="alert alert-warning small mt-2 mb-0">
                            لا توجد قوالب بريد نشطة.
                            <a href="{{ route('admin.email-templates.create') }}">أنشئ قالباً</a>
                        </div>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" for="moduleCompletionEmailSettingId">حساب الإرسال (SMTP)</label>
                    <select id="moduleCompletionEmailSettingId" class="form-select">
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

                <div id="moduleCompletionEmailPreviewWrap" class="d-none">
                    <label class="form-label fw-semibold">معاينة</label>
                    <div class="border rounded p-3 bg-light mb-2">
                        <small class="text-muted d-block mb-1">الموضوع</small>
                        <strong id="moduleCompletionEmailPreviewSubject">—</strong>
                    </div>
                    <div id="moduleCompletionEmailPreviewBody" class="border rounded p-3 bg-light module-completions-preview"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-outline-primary" id="moduleCompletionEmailPreviewBtn">
                    <span class="module-email-btn__label"><i class="fe fe-eye me-1"></i>معاينة</span>
                    <span class="module-email-btn__spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>جاري التحميل...</span>
                </button>
                <button type="button" class="btn btn-primary" id="moduleCompletionEmailSubmitBtn">
                    <span class="module-email-btn__label"><i class="fe fe-send me-1"></i>إرسال</span>
                    <span class="module-email-btn__spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>جاري الإرسال...</span>
                </button>
            </div>
        </div>
    </div>
</div>
