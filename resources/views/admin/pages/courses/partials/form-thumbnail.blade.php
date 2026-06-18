@php
    $inputId = $inputId ?? 'imageInput';
    $previewWrapId = $previewWrapId ?? 'thumbnailPreview';
    $existingImageUrl = $existingImageUrl ?? null;
    $altText = $altText ?? 'صورة الكورس';
@endphp

<div class="admin-course-form-page__thumbnail" id="{{ $previewWrapId }}" role="button" tabindex="0"
     aria-label="رفع صورة الكورس">
    <input type="file" name="image" class="admin-course-form-page__thumbnail-input @error('image') is-invalid @enderror"
           id="{{ $inputId }}" accept="image/*">
    @if($existingImageUrl)
        <img src="{{ $existingImageUrl }}" alt="{{ $altText }}" class="admin-course-form-page__thumbnail-preview"
             id="currentThumbnail"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="admin-course-form-page__thumbnail-placeholder" style="display: none;">
            <i class="fe fe-upload-cloud"></i>
            <span>انقر أو اسحب صورة هنا</span>
            <small class="admin-course-form-page__thumbnail-hint">الأبعاد الموصى بها: 1280×720</small>
        </div>
    @else
        <div class="admin-course-form-page__thumbnail-placeholder" id="thumbnailPlaceholder">
            <i class="fe fe-upload-cloud"></i>
            <span>انقر أو اسحب صورة هنا</span>
            <small class="admin-course-form-page__thumbnail-hint">الأبعاد الموصى بها: 1280×720</small>
        </div>
        <img src="" alt="معاينة" class="admin-course-form-page__thumbnail-preview" id="thumbnailPreviewImg" style="display: none;">
    @endif
</div>
@error('image')
    <div class="invalid-feedback d-block">{{ $message }}</div>
@enderror
