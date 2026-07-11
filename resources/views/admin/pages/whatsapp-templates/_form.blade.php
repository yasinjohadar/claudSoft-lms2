@php
    $template = $template ?? null;
@endphp
<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">اسم القالب <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $template?->name) }}" placeholder="مثال: ترحيب بالتسجيل" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="slug" class="form-label">المعرّف (slug)</label>
        <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $template?->slug) }}" placeholder="مثال: welcome_group (للاستخدام برمجياً)">
        <small class="text-muted">اختياري. يُستخدم للوصول للقالب من الكود (مثل التسجيل الجماعي).</small>
        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label for="whatsapp_template_body" class="form-label">نص الرسالة <span class="text-danger">*</span></label>
        <textarea class="form-control @error('body') is-invalid @enderror" id="whatsapp_template_body" name="body" rows="8" placeholder="@verbatimمثال: مرحباً {{student_name}}، تم استلام طلبك في {{group_name}}.@endverbatim">{{ old('body', $template?->body) }}</textarea>
        <small class="text-muted">استخدم المتغيرات بصيغة @verbatim<code>{{اسم_المتغير}}</code> أو <code>{اسم_المتغير}</code>@endverbatim مثل: student_name, group_name, email, phone</small>
        @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="type" class="form-label">نوع القالب <span class="text-danger">*</span></label>
        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
            <option value="text" {{ old('type', $template?->type ?? 'text') == 'text' ? 'selected' : '' }}>نص عادي</option>
            <option value="template" {{ old('type', $template?->type) == 'template' ? 'selected' : '' }}>قالب Meta (معتمد)</option>
        </select>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label for="language" class="form-label">اللغة <span class="text-danger">*</span></label>
        <select class="form-select @error('language') is-invalid @enderror" id="language" name="language" required>
            <option value="ar" {{ old('language', $template?->language ?? 'ar') == 'ar' ? 'selected' : '' }}>العربية (ar)</option>
            <option value="en" {{ old('language', $template?->language) == 'en' ? 'selected' : '' }}>الإنجليزية (en)</option>
            <option value="fr" {{ old('language', $template?->language) == 'fr' ? 'selected' : '' }}>الفرنسية (fr)</option>
            <option value="es" {{ old('language', $template?->language) == 'es' ? 'selected' : '' }}>الإسبانية (es)</option>
        </select>
        @error('language')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4" id="meta-template-name-wrap" style="{{ old('type', $template?->type ?? 'text') == 'template' ? '' : 'display:none;' }}">
        <label for="meta_template_name" class="form-label">اسم القالب في Meta</label>
        <input type="text" class="form-control @error('meta_template_name') is-invalid @enderror" id="meta_template_name" name="meta_template_name" value="{{ old('meta_template_name', $template?->meta_template_name) }}" placeholder="اسم القالب المعتمد في Meta">
        <small class="text-muted">مطلوب فقط لنوع «قالب Meta»</small>
        @error('meta_template_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label">المتغيرات المتاحة (للمرجع)</label>
        <p class="text-muted small mb-0">
            <code>student_name</code>, <code>student_name_ar</code>, <code>student_name_en</code>, <code>student_email</code>, <code>email</code>, <code>phone</code>, <code>full_phone</code>, <code>student_phone</code>, <code>password</code>, <code>new_password</code>, <code>login_url</code>, <code>admin_instructions</code>, <code>course_name</code>, <code>group_name</code>
        </p>
    </div>
    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $template?->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">قالب نشط (يظهر في قوائم الإرسال)</label>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var typeSelect = document.getElementById('type');
    var metaWrap = document.getElementById('meta-template-name-wrap');

    if (typeSelect && metaWrap) {
        typeSelect.addEventListener('change', function () {
            metaWrap.style.display = this.value === 'template' ? 'block' : 'none';
        });
    }

    function initWhatsAppTemplateEditor() {
        if (typeof tinymce === 'undefined') {
            setTimeout(initWhatsAppTemplateEditor, 100);
            return;
        }

        if (tinymce.get('whatsapp_template_body')) {
            tinymce.get('whatsapp_template_body').remove();
        }

        tinymce.init({
            selector: '#whatsapp_template_body',
            height: 320,
            directionality: 'rtl',
            language: 'ar',
            language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n@latest/langs6/ar.js',
            promotion: false,
            branding: false,
            menubar: false,
            statusbar: true,
            plugins: 'lists link directionality wordcount',
            toolbar: 'undo redo | bold italic underline strikethrough | forecolor | alignright aligncenter alignleft alignjustify | bullist numlist | ltr rtl | removeformat',
            content_style: 'body { font-family: "Segoe UI", Tahoma, Arial, sans-serif; font-size: 14px; direction: rtl; line-height: 1.6; }',
            paste_as_text: false,
            entity_encoding: 'raw',
            setup: function (editor) {
                editor.on('init', function () {
                    editor.getContainer().style.visibility = 'visible';
                });
            }
        });
    }

    setTimeout(initWhatsAppTemplateEditor, 200);

    var templateForm = document.getElementById('whatsappTemplateForm');
    if (templateForm) {
        templateForm.addEventListener('submit', function (event) {
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }

            var bodyField = document.getElementById('whatsapp_template_body');
            var bodyValue = bodyField ? bodyField.value.trim() : '';
            var bodyPlain = bodyValue.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();

            if (!bodyPlain) {
                event.preventDefault();
                alert('يرجى إدخال نص الرسالة.');
                var editor = typeof tinymce !== 'undefined' ? tinymce.get('whatsapp_template_body') : null;
                if (editor) {
                    editor.focus();
                } else if (bodyField) {
                    bodyField.focus();
                }
            }
        });
    }
});
</script>
@endpush
