@php
    $contentMode = old('content_mode', $gift->content_mode ?? 'upload');
@endphp

<div class="col-12">
    <h6 class="text-primary mb-3">المعلومات الأساسية</h6>
</div>

<div class="row g-3">
    <div class="col-md-8">
        <label for="name" class="form-label">اسم الهدية <span class="text-danger">*</span></label>
        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $gift->name ?? '') }}" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4">
        <label for="image" class="form-label">صورة الغلاف</label>
        <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
        @error('image')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if(!empty($gift?->cover_url))
            <img src="{{ $gift->cover_url }}" alt="" class="img-thumbnail mt-2" style="max-height:80px">
        @endif
    </div>
    <div class="col-12">
        <label for="description" class="form-label">الوصف</label>
        <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $gift->description ?? '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="col-12 mt-4">
    <h6 class="text-primary mb-3">محتوى الهدية</h6>
</div>

<div class="mb-3">
    <div class="form-check form-check-inline">
        <input class="form-check-input content-mode-radio" type="radio" name="content_mode" id="content_upload" value="upload" {{ $contentMode === 'upload' ? 'checked' : '' }}>
        <label class="form-check-label" for="content_upload">رفع ملفات</label>
    </div>
    <div class="form-check form-check-inline">
        <input class="form-check-input content-mode-radio" type="radio" name="content_mode" id="content_external" value="external" {{ $contentMode === 'external' ? 'checked' : '' }}>
        <label class="form-check-label" for="content_external">روابط خارجية</label>
    </div>
</div>

<div class="content-panel {{ $contentMode === 'upload' ? '' : 'd-none' }}" data-content="upload">
    <div class="row g-3">
        <div class="col-md-6">
            <label for="preview_file" class="form-label">ملف المعاينة</label>
            <input type="file" name="preview_file" id="preview_file" class="form-control @error('preview_file') is-invalid @enderror">
            @error('preview_file')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            @if(!empty($gift?->preview_file_name))
                <small class="text-muted d-block mt-1">الحالي: {{ $gift->preview_file_name }}</small>
            @endif
        </div>
        <div class="col-md-6">
            <label for="download_file" class="form-label">ملف التحميل <span class="text-danger">*</span></label>
            <input type="file" name="download_file" id="download_file" class="form-control @error('download_file') is-invalid @enderror">
            @error('download_file')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            @if(!empty($gift?->download_file_name))
                <small class="text-muted d-block mt-1">الحالي: {{ $gift->download_file_name }}</small>
            @endif
        </div>
    </div>
</div>

<div class="content-panel {{ $contentMode === 'external' ? '' : 'd-none' }}" data-content="external">
    <div class="row g-3">
        <div class="col-md-6">
            <label for="preview_url" class="form-label">رابط المعاينة</label>
            <input type="url" name="preview_url" id="preview_url" class="form-control @error('preview_url') is-invalid @enderror"
                   value="{{ old('preview_url', $gift->preview_url ?? '') }}" placeholder="https://">
            @error('preview_url')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label for="download_url" class="form-label">رابط التحميل <span class="text-danger">*</span></label>
            <input type="url" name="download_url" id="download_url" class="form-control @error('download_url') is-invalid @enderror"
                   value="{{ old('download_url', $gift->download_url ?? '') }}" placeholder="https://">
            @error('download_url')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="col-12 mt-4">
    <h6 class="text-primary mb-3">الاستهداف</h6>
</div>

@include('admin.pages.student-gifts.partials.targeting-fields', ['gift' => $gift ?? null])
