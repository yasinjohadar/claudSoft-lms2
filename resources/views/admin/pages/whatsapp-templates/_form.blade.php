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
        <label for="body" class="form-label">نص الرسالة <span class="text-danger">*</span></label>
        <textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body" rows="6" placeholder="@verbatimمثال: مرحباً {{student_name}}، تم استلام طلبك في {{group_name}}.@endverbatim" required>{{ old('body', $template?->body) }}</textarea>
        <small class="text-muted">استخدم المتغيرات بصيغة @verbatim<code>{{اسم_المتغير}}</code>@endverbatim مثل: student_name, course_name, group_name, student_email</small>
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
            <code>student_name</code>, <code>student_email</code>, <code>course_name</code>, <code>group_name</code>
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
<script>
document.getElementById('type').addEventListener('change', function() {
    var wrap = document.getElementById('meta-template-name-wrap');
    wrap.style.display = this.value === 'template' ? 'block' : 'none';
});
</script>
@endpush
