@extends('admin.layouts.master')

@section('page-title')
    إرسال قالب Flaxxa
@stop

@php
    $templatesMeta = collect($templates ?? [])->mapWithKeys(function ($t) {
        $st = $t->structure ?? [];
        return [$t->id => [
            'id' => $t->id,
            'name' => $t->name,
            'language' => $t->language,
            'provider_template_id' => $t->provider_template_id,
            'status' => $st['status'] ?? null,
            'category' => $st['category'] ?? null,
            'header_placeholders' => (int) ($st['header_placeholders'] ?? 0),
            'body_placeholders' => (int) ($st['body_placeholders'] ?? 0),
            'has_media_header' => (bool) ($st['has_media_header'] ?? false),
            'header_format' => $st['header_format'] ?? null,
            'header_text' => (string) ($st['header_text'] ?? ''),
            'preview_template' => (string) ($st['preview_template'] ?? ''),
            'footer_text' => (string) ($st['footer_text'] ?? ''),
            'source' => $st['source'] ?? 'manual',
        ]];
    })->toArray();
@endphp

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        @include('admin.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">إرسال قالب معتمد</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.flaxxa-wapi.messages.index') }}">Flaxxa</a></li>
                        <li class="breadcrumb-item active">إرسال قالب</li>
                    </ol>
                </nav>
            </div>
        </div>

        @include('admin.pages.flaxxa-wapi._nav')

        <div class="alert alert-info d-flex align-items-start">
            <i class="ri-information-line ms-2 mt-1"></i>
            <div>
                القوالب المعتمَدة من Meta تصل <strong>فوراً</strong> وتتجاوز نافذة 24 ساعة. إن لم تظهر قوالبك هنا
                <a href="{{ route('admin.flaxxa-wapi.templates.index') }}">اضغط هنا</a> ثم "مزامنة من Meta عبر Flaxxa".
            </div>
        </div>

        <div class="card custom-card">
            <div class="card-body">
                <form action="{{ route('admin.flaxxa-wapi.send.template.store') }}" method="POST" enctype="multipart/form-data" class="row g-3" id="flaxxa-template-form">
                    @csrf
                    <div class="col-12">
                        <label class="form-label">نوع الإرسال <span class="text-danger">*</span></label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="send_type" id="send_type_individual" value="individual" {{ old('send_type', 'individual') === 'individual' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="send_type_individual">إرسال فردي</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="send_type" id="send_type_broadcast" value="broadcast" {{ old('send_type') === 'broadcast' ? 'checked' : '' }}>
                                <label class="form-check-label" for="send_type_broadcast">إرسال جماعي (كورس / مجموعة)</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12" id="individual-flaxxa-fields" style="{{ old('send_type') === 'broadcast' ? 'display:none' : '' }}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="student_search_flaxxa">البحث عن طالب <span class="text-muted">(اختياري)</span></label>
                                <select class="form-select" id="student_search_flaxxa" name="student_id" style="width:100%">
                                    <option value="">— بدون —</option>
                                    @if(old('student_id') && ($__ou = \App\Models\User::find(old('student_id'))))
                                        <option value="{{ $__ou->id }}" selected>{{ $__ou->name }} — {{ $__ou->phone }}</option>
                                    @endif
                                </select>
                                <small class="text-muted">عند الاختيار يُستخدم رقم الطالب وتُستبدل <code>{student_name}</code> وغيرها تلقائياً.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">رقم الهاتف <span id="phone-required-star" class="text-danger">*</span></label>
                                <input type="text" name="phone" id="flaxxa_phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="مثال 9055xxxxxxxx أو +9055...">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <p class="small text-muted mt-1" id="individual-placeholders-hint" style="display:none;">
                            <strong>اختصارات متاحة عند اختيار طالب أو في الإرسال الجماعي:</strong>
                            <code>{student_name}</code>، <code>{student_email}</code>، <code>{course_name}</code>، <code>{group_name}</code>
                        </p>
                    </div>

                    <div class="col-12" id="broadcast-flaxxa-fields" style="{{ old('send_type') === 'broadcast' ? '' : 'display:none' }}">
                        <div class="alert alert-light border">
                            <small class="text-muted">عند اختيار <strong>مجموعة</strong> يُرسل لأعضائها فقط. عند اختيار <strong>كورس</strong> فقط يُرسل لطلاب مسجلين فيه.</small>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">الكورس <span id="course-required-star-b" class="text-danger">*</span></label>
                                <select class="form-select @error('course_id') is-invalid @enderror" id="flaxxa_course_id" name="course_id">
                                    <option value="">— اختر الكورس أو مجموعة فقط —</option>
                                    @foreach($courses ?? [] as $course)
                                        <option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>{{ $course->title }}</option>
                                    @endforeach
                                </select>
                                @error('course_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">المجموعة <span class="text-muted">(اختياري)</span></label>
                                <select class="form-select" id="flaxxa_group_id" name="group_id">
                                    <option value="">— بدون —</option>
                                    @foreach($groups ?? [] as $group)
                                        <option value="{{ $group->id }}" @selected(old('group_id') == $group->id)>{{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <span class="badge bg-secondary fs-6">عدد الطلاب ذوي أرقام صالحة: <strong id="flaxxa-students-count">—</strong></span>
                                <button type="button" class="btn btn-sm btn-outline-secondary ms-2" id="flaxxa-refresh-count">تحديث العدد</button>
                            </div>
                        </div>
                        <div class="mt-2 small border rounded p-2 bg-light">
                            <strong>متغيرات متاحة للإرسال الجماعي:</strong>
                            <code>{student_name}</code> اسم الطالب — <code>{student_email}</code> البريد — <code>{course_name}</code> اسم الكورس — <code>{group_name}</code> اسم المجموعة
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">اختر قالباً مزامَناً</label>
                        <select name="wapi_template_id" class="form-select" id="wapi_template_id">
                            <option value="">— يدوي (أدخل الاسم واللغة) —</option>
                            @foreach($templates as $t)
                                @php $st = $t->structure ?? []; @endphp
                                <option value="{{ $t->id }}"
                                    data-source="{{ $st['source'] ?? 'manual' }}"
                                    data-status="{{ $st['status'] ?? '' }}"
                                    @selected(old('wapi_template_id') == $t->id)>
                                    {{ $t->name }} ({{ $t->language }})
                                    @if(($st['status'] ?? '') === 'APPROVED') ✓ @endif
                                    @if(!empty($st['has_media_header'])) [{{ $st['header_format'] ?? 'MEDIA' }}] @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">اختيار قالب يُعبئ الاسم/اللغة ويُنشئ حقول المتغيرات تلقائياً.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">اسم القالب في المزود <span class="text-danger">*</span></label>
                        <input type="text" id="template_name" name="template_name" class="form-control @error('template_name') is-invalid @enderror" value="{{ old('template_name') }}" placeholder="مثل rsal_trhyby_balekadymy">
                        @error('template_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">لغة القالب <span class="text-danger">*</span></label>
                        <input type="text" id="language" name="language" class="form-control @error('language') is-invalid @enderror" value="{{ old('language', 'en_US') }}" placeholder="ar / en_US">
                        @error('language')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12" id="template-preview" style="display:none;">
                        <div class="border rounded p-3 bg-light">
                            <div class="fw-bold mb-1">معاينة محتوى القالب</div>
                            <div class="small" id="tpl-header"></div>
                            <div class="small" id="tpl-body"></div>
                            <div class="small text-muted" id="tpl-footer"></div>
                        </div>
                    </div>

                    <div class="col-md-6" id="attachment-wrap">
                        <label class="form-label">مرفق رأس <span id="attachment-required" class="text-danger d-none">*</span></label>
                        <input type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror">
                        <small class="text-muted" id="attachment-hint">اختياري — يُطلب فقط إن كان رأس القالب Media.</small>
                        @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12" id="dynamic-vars">
                        <div class="row g-3" id="header-vars-wrap" style="display:none;">
                            <div class="col-12"><hr><strong>متغيرات الرأس</strong></div>
                            <div class="col-12" id="header-vars-fields"></div>
                        </div>
                        <div class="row g-3" id="body-vars-wrap" style="display:none;">
                            <div class="col-12"><hr><strong>متغيرات النص</strong></div>
                            <div class="col-12" id="body-vars-fields"></div>
                        </div>
                    </div>

                    <div class="col-12" id="fallback-vars">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">متغيرات الرأس (سطر لكل متغير)</label>
                                <textarea name="header_variables_text" rows="3" class="form-control @error('header_variables_text') is-invalid @enderror" placeholder="سطر لكل متغير بالترتيب">{{ old('header_variables_text') }}</textarea>
                                @error('header_variables_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">متغيرات النص (سطر لكل متغير)</label>
                                <textarea name="body_variables_text" rows="3" class="form-control @error('body_variables_text') is-invalid @enderror" placeholder="سطر لكل متغير في نص الرسالة">{{ old('body_variables_text') }}</textarea>
                                @error('body_variables_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><i class="ri-send-plane-2-line me-1"></i> جدولة الإرسال</button>
                        <a href="{{ route('admin.flaxxa-wapi.templates.index') }}" class="btn btn-outline-secondary">إدارة القوالب</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function () {
    const meta = @json($templatesMeta);
    const select = document.getElementById('wapi_template_id');
    const nameInput = document.getElementById('template_name');
    const langInput = document.getElementById('language');
    const preview = document.getElementById('template-preview');
    const tplHeader = document.getElementById('tpl-header');
    const tplBody = document.getElementById('tpl-body');
    const tplFooter = document.getElementById('tpl-footer');
    const attachmentHint = document.getElementById('attachment-hint');
    const attachmentRequired = document.getElementById('attachment-required');
    const headerWrap = document.getElementById('header-vars-wrap');
    const headerFields = document.getElementById('header-vars-fields');
    const bodyWrap = document.getElementById('body-vars-wrap');
    const bodyFields = document.getElementById('body-vars-fields');
    const fallback = document.getElementById('fallback-vars');
    const sendInd = document.getElementById('send_type_individual');
    const sendBro = document.getElementById('send_type_broadcast');
    const indFields = document.getElementById('individual-flaxxa-fields');
    const broFields = document.getElementById('broadcast-flaxxa-fields');
    const phoneInput = document.getElementById('flaxxa_phone');
    const phoneStar = document.getElementById('phone-required-star');
    const courseSel = document.getElementById('flaxxa_course_id');
    const groupSel = document.getElementById('flaxxa_group_id');
    const countEl = document.getElementById('flaxxa-students-count');
    const indHint = document.getElementById('individual-placeholders-hint');
    const courseStarB = document.getElementById('course-required-star-b');
    const form = document.getElementById('flaxxa-template-form');

    function buildInputs(container, prefix, count) {
        container.innerHTML = '';
        for (let i = 1; i <= count; i++) {
            const col = document.createElement('div');
            col.className = 'col-md-6 mb-2';
            const fieldName = prefix === 'رأس' ? 'header_vars' : 'body_vars';
            const label = document.createElement('label');
            label.className = 'form-label small';
            label.textContent = prefix + ' متغير \u007B\u007B' + i + '\u007D\u007D';
            const input = document.createElement('input');
            input.type = 'text';
            input.name = fieldName + '[]';
            input.className = 'form-control';
            input.required = true;
            col.appendChild(label);
            col.appendChild(input);
            container.appendChild(col);
        }
    }

    function render(id) {
        if (!id || !meta[id]) {
            preview.style.display = 'none';
            headerWrap.style.display = 'none';
            bodyWrap.style.display = 'none';
            fallback.style.display = '';
            attachmentRequired.classList.add('d-none');
            attachmentHint.textContent = 'اختياري — يُطلب فقط إن كان رأس القالب Media.';
            return;
        }
        const t = meta[id];
        nameInput.value = t.name || '';
        langInput.value = t.language || '';

        preview.style.display = '';
        tplHeader.textContent = t.header_text ? 'رأس: ' + t.header_text : '';
        tplBody.textContent = t.preview_template ? 'النص: ' + t.preview_template : '';
        tplFooter.textContent = t.footer_text ? 'تذييل: ' + t.footer_text : '';

        if (t.has_media_header) {
            attachmentRequired.classList.remove('d-none');
            attachmentHint.textContent = 'مطلوب — رأس هذا القالب من نوع ' + (t.header_format || 'MEDIA');
        } else {
            attachmentRequired.classList.add('d-none');
            attachmentHint.textContent = 'لا يتطلب هذا القالب مرفقاً.';
        }

        if (t.header_placeholders > 0) {
            headerWrap.style.display = '';
            buildInputs(headerFields, 'رأس', t.header_placeholders);
        } else {
            headerWrap.style.display = 'none';
            headerFields.innerHTML = '';
        }
        if (t.body_placeholders > 0) {
            bodyWrap.style.display = '';
            buildInputs(bodyFields, 'نص', t.body_placeholders);
        } else {
            bodyWrap.style.display = 'none';
            bodyFields.innerHTML = '';
        }

        fallback.style.display = (t.header_placeholders === 0 && t.body_placeholders === 0) ? '' : 'none';
    }

    if (select) {
        select.addEventListener('change', function () { render(this.value); });
        if (select.value) render(select.value);
    }

    function updateBroadcastCourseRequired() {
        if (!sendBro || !sendBro.checked) return;
        if (groupSel.value) {
            courseSel.removeAttribute('required');
            if (courseStarB) courseStarB.style.display = 'none';
        } else {
            courseSel.setAttribute('required', 'required');
            if (courseStarB) courseStarB.style.display = 'inline';
        }
    }

    function toggleSendType() {
        if (sendBro && sendBro.checked) {
            indFields.style.display = 'none';
            broFields.style.display = 'block';
            if (phoneInput) { phoneInput.removeAttribute('required'); phoneInput.disabled = true; }
            if (phoneStar) phoneStar.style.display = 'none';
            if (courseSel) courseSel.disabled = false;
            if (groupSel) groupSel.disabled = false;
            jQuery('#student_search_flaxxa').prop('disabled', true);
            updateBroadcastCourseRequired();
            updateStudentsCount();
        } else {
            indFields.style.display = 'block';
            broFields.style.display = 'none';
            if (phoneInput) phoneInput.disabled = false;
            if (courseSel) { courseSel.removeAttribute('required'); courseSel.disabled = true; }
            if (groupSel) groupSel.disabled = true;
            jQuery('#student_search_flaxxa').prop('disabled', false);
            const hasStudent = jQuery && jQuery('#student_search_flaxxa').val();
            if (phoneInput && (!hasStudent || hasStudent === '')) {
                phoneInput.setAttribute('required', 'required');
                if (phoneStar) phoneStar.style.display = 'inline';
            }
        }
    }

    const studentsCountUrl = @json(route('admin.whatsapp-messages.broadcast.students-count'));
    const searchStudentsUrl = @json(route('admin.whatsapp-messages.search-students'));

    async function updateStudentsCount() {
        if (!countEl || !courseSel) return;
        const p = new URLSearchParams();
        if (courseSel.value) p.set('course_id', courseSel.value);
        if (groupSel && groupSel.value) p.set('group_id', groupSel.value);
        countEl.textContent = '…';
        try {
            const res = await fetch(studentsCountUrl + '?' + p.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            const data = await res.json();
            countEl.textContent = typeof data.count !== 'undefined' ? data.count : '؟';
        } catch (e) {
            countEl.textContent = '؟';
        }
    }

    if (sendInd) sendInd.addEventListener('change', toggleSendType);
    if (sendBro) sendBro.addEventListener('change', toggleSendType);
    if (courseSel) courseSel.addEventListener('change', function () { updateBroadcastCourseRequired(); updateStudentsCount(); });
    if (groupSel) groupSel.addEventListener('change', function () { updateBroadcastCourseRequired(); updateStudentsCount(); });
    document.getElementById('flaxxa-refresh-count')?.addEventListener('click', updateStudentsCount);

    if (form) {
        form.addEventListener('submit', function () {
            if (sendBro && sendBro.checked) {
                if (phoneInput) phoneInput.disabled = true;
                jQuery('#student_search_flaxxa').prop('disabled', true);
            } else {
                if (courseSel) courseSel.disabled = true;
                if (groupSel) groupSel.disabled = true;
            }
        });
    }

    jQuery(function () {
        jQuery('#student_search_flaxxa').select2({
            placeholder: 'ابحث عن طالب…',
            allowClear: true,
            dir: 'rtl',
            ajax: {
                url: searchStudentsUrl,
                dataType: 'json',
                delay: 300,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                data: function (params) { return { search: params.term }; },
                processResults: function (data) {
                    if (!Array.isArray(data)) return { results: [] };
                    return {
                        results: data.map(function (s) {
                            return { id: s.id, text: s.name + ' (' + (s.email || '') + ') — ' + (s.phone || '') };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 2
        }).on('select2:select', function (e) {
            var text = e.params.data.text || '';
            var parts = text.split('—');
            if (parts.length > 1) {
                var phone = parts[parts.length - 1].trim();
                phoneInput.value = phone.replace(/^\+/, '');
                if (indHint) indHint.style.display = 'block';
                phoneInput.removeAttribute('required');
                if (phoneStar) phoneStar.style.display = 'none';
            }
        }).on('select2:clear', function () {
            phoneInput.value = '';
            if (indHint) indHint.style.display = 'none';
            if (sendInd && sendInd.checked) {
                phoneInput.setAttribute('required', 'required');
                if (phoneStar) phoneStar.style.display = 'inline';
            }
        });

        toggleSendType();
        if (jQuery('#student_search_flaxxa').val()) {
            if (indHint) indHint.style.display = 'block';
            phoneInput.removeAttribute('required');
            if (phoneStar) phoneStar.style.display = 'none';
        }
    });
})();
</script>
@endpush
