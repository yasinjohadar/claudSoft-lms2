@php
    /** @var \App\Models\BunnyStreamLibrary|null $library */
    $isEdit = isset($library);
@endphp

<div class="row gy-3">
    <div class="col-md-6">
        <label class="form-label">Library ID <span class="text-danger">*</span></label>
        <input type="text" name="library_id" class="form-control @error('library_id') is-invalid @enderror"
               value="{{ old('library_id', $library->library_id ?? '') }}"
               placeholder="مثال: 490323" required {{ $isEdit ? 'readonly' : '' }}>
        <small class="text-muted">الرقم من رابط Bunny: /embed/<strong>490323</strong>/video-id</small>
        @error('library_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">اسم المكتبة <span class="text-danger">*</span></label>
        <input type="text" name="library_name" class="form-control @error('library_name') is-invalid @enderror"
               value="{{ old('library_name', $library->library_name ?? '') }}"
               placeholder="مثال: Front End Diplom" required>
        @error('library_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label">
            Token authentication key
            @if(!$isEdit)<span class="text-danger">*</span>@endif
        </label>
        <input type="password" name="token_security_key" class="form-control @error('token_security_key') is-invalid @enderror"
               value="{{ old('token_security_key') }}"
               placeholder="{{ $isEdit ? 'اتركه فارغاً للإبقاء على المفتاح الحالي' : 'الصق المفتاح من Bunny Security' }}"
               {{ $isEdit ? '' : 'required' }} autocomplete="new-password">
        @error('token_security_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label">Stream API Key <span class="text-muted">(اختياري)</span></label>
        <input type="password" name="api_key" class="form-control @error('api_key') is-invalid @enderror"
               value="{{ old('api_key') }}"
               placeholder="{{ $isEdit ? 'اتركه فارغاً للإبقاء على القيمة الحالية' : 'اختياري — لاستعلامات Bunny API لهذه المكتبة' }}"
               autocomplete="new-password">
        @error('api_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                   {{ old('is_active', $library->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">المكتبة نشطة</label>
        </div>
    </div>
</div>
