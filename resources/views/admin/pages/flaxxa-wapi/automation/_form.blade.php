@php
    $isEdit = isset($rule) && $rule->exists;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">مفتاح الحدث <span class="text-danger">*</span></label>
        <select name="event_key" class="form-select @error('event_key') is-invalid @enderror" required>
            @foreach($eventKeys as $key => $label)
                <option value="{{ $key }}" {{ old('event_key', $rule->event_key ?? '') === $key ? 'selected' : '' }}>{{ $label }} — <code>{{ $key }}</code></option>
            @endforeach
        </select>
        @error('event_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">الأولوية</label>
        <input type="number" name="priority" class="form-control" min="0" value="{{ old('priority', $rule->priority ?? 0) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">ترتيب العرض</label>
        <input type="number" name="sort_order" class="form-control" min="0" value="{{ old('sort_order', $rule->sort_order ?? 0) }}">
    </div>

    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $rule->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">قاعدة مفعّلة</label>
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">قالب محفوظ (اختياري)</label>
        <select name="wapi_template_id" class="form-select">
            <option value="">— يدوي —</option>
            @foreach($templates as $tpl)
                <option value="{{ $tpl->id }}" {{ (int) old('wapi_template_id', $rule->wapi_template_id ?? 0) === $tpl->id ? 'selected' : '' }}>{{ $tpl->name }} ({{ $tpl->language }})</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">اسم القالب في Meta</label>
        <input type="text" name="template_name" class="form-control" value="{{ old('template_name', $rule->template_name ?? '') }}" placeholder="إن وُجد سجل أعلاه">
    </div>
    <div class="col-md-3">
        <label class="form-label">لغة القالب</label>
        <input type="text" name="language" class="form-control" value="{{ old('language', $rule->language ?? 'ar') }}" placeholder="ar">
    </div>

    <div class="col-md-6">
        <label class="form-label">تقييد كورس</label>
        <select name="course_id" class="form-select">
            <option value="">— أي كورس —</option>
            @foreach($courses as $c)
                <option value="{{ $c->id }}" {{ (int) old('course_id', $rule->course_id ?? 0) === $c->id ? 'selected' : '' }}>{{ $c->title }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">تقييد مجموعة</label>
        <select name="group_id" class="form-select">
            <option value="">— أي مجموعة —</option>
            @foreach($groups as $g)
                <option value="{{ $g->id }}" {{ (int) old('group_id', $rule->group_id ?? 0) === $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">تقييد وحدة (معرّف course_modules)</label>
        <input type="number" name="module_id" class="form-control" value="{{ old('module_id', $rule->module_id ?? '') }}" placeholder="اختياري">
    </div>
    <div class="col-md-6">
        <label class="form-label">تقييد درس</label>
        <input type="number" name="lesson_id" class="form-control" value="{{ old('lesson_id', $rule->lesson_id ?? '') }}" placeholder="اختياري">
    </div>

    <div class="col-12">
        <label class="form-label">متغيرات الرأس (سطر لكل متغير)</label>
        <textarea name="header_variables_text" class="form-control font-monospace" rows="3" placeholder="{student_name}">{{ old('header_variables_text', $isEdit ? implode("\n", $rule->header_variables ?? []) : '') }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label">متغيرات النص (سطر لكل متغير)</label>
        <textarea name="body_variables_text" class="form-control font-monospace" rows="5" placeholder="{course_name}">{{ old('body_variables_text', $isEdit ? implode("\n", $rule->body_variables ?? []) : '') }}</textarea>
    </div>

    <div class="col-md-4">
        <label class="form-label">تبريد (ثواني)</label>
        <input type="number" name="cooldown_seconds" class="form-control" min="0" value="{{ old('cooldown_seconds', $rule->cooldown_seconds ?? 0) }}">
        <small class="text-muted">منع تكرار نفس القاعدة لنفس المستخدم خلال هذه المدة</small>
    </div>
    <div class="col-md-8">
        <label class="form-label">مفتاح منع التكرار (اختياري)</label>
        <input type="text" name="dedupe_template" class="form-control" value="{{ old('dedupe_template', $rule->dedupe_template ?? '') }}" placeholder="مثال: {user_id}:{lesson_id}:{rule_id}">
    </div>

    <div class="col-12">
        <label class="form-label">وصف داخلي</label>
        <input type="text" name="description" class="form-control" value="{{ old('description', $rule->description ?? '') }}">
    </div>
</div>

<div class="alert alert-light border mt-3 small">
    استخدم نفس العناصر النائبة كما في «إرسال قالب»: <code>{student_name}</code>، <code>{course_name}</code> / <code>{course_title}</code>، <code>{group_name}</code>، <code>{lesson_title}</code>، <code>{learn_url}</code>، <code>{quiz_title}</code>، <code>{score}</code>، …
    <strong>مهم:</strong> اختر قالب Meta يتناسب مع الحدث (مثلاً إكمال درس وليس رسالة ترحيب). عدد أسطر المتغيرات يجب أن يطابق عدد حقول القالب في Meta.
    إذا تركت «متغيرات النص» فارغة، يُستخدم نص عربي افتراضي حسب نوع الحدث حتى لا يُرسل <code>components</code> فارغاً.
    لتفعيل الإرسال من مركز الإشعارات، فعّل قناة <strong>whatsapp_wapi</strong> في إعدادات مركز الإشعارات.
</div>
