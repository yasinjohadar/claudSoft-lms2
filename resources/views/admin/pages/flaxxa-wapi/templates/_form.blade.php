<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">اسم القالب <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $template->name ?? '') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">اللغة (مثل en_US) <span class="text-danger">*</span></label>
        <input type="text" name="language" class="form-control @error('language') is-invalid @enderror" value="{{ old('language', $template->language ?? 'en_US') }}" required>
        @error('language')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label">معرّف القالب لدى المزود (للحملات / المطابقة)</label>
        <input type="text" name="provider_template_id" class="form-control @error('provider_template_id') is-invalid @enderror" value="{{ old('provider_template_id', $template->provider_template_id ?? '') }}" placeholder="اختياري">
        @error('provider_template_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">عدد متغيرات الرأس</label>
        <input type="number" name="header_placeholders" min="0" max="50" class="form-control @error('header_placeholders') is-invalid @enderror" value="{{ old('header_placeholders', $headerPlaceholders ?? 0) }}">
        @error('header_placeholders')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">عدد متغيرات النص</label>
        <input type="number" name="body_placeholders" min="0" max="50" class="form-control @error('body_placeholders') is-invalid @enderror" value="{{ old('body_placeholders', $bodyPlaceholders ?? 0) }}">
        @error('body_placeholders')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label">نص المعاينة المحلي (رموز العناصر: عنصر 1، عنصر 2 وفق الترتيب في نص واتساب المعتمد)</label>
        <textarea name="preview_template" rows="3" class="form-control @error('preview_template') is-invalid @enderror" placeholder="للعرض فقط أثناء المعاينة من الـ API">{{ old('preview_template', $previewTemplate ?? '') }}</textarea>
        @error('preview_template')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
