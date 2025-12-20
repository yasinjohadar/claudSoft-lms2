@extends('admin.layouts.master')

@section('page-title')
إضافة مقال جديد
@stop

@section('styles')
<!-- TinyMCE Editor -->
<script src="https://cdn.tiny.mce.com/1/tinymce.min.js" referrerpolicy="origin"></script>
@endsection

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">إضافة مقال جديد</h4>
                <p class="mb-0 text-muted">إنشاء مقال جديد في المدونة</p>
            </div>
            <div class="ms-auto">
                <a href="{{ route('admin.blog.posts.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-right me-2"></i>
                    رجوع للقائمة
                </a>
            </div>
        </div>

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>يوجد أخطاء في النموذج:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <form action="{{ route('admin.blog.posts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">

                    <!-- Basic Info -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">المعلومات الأساسية</div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">عنوان المقال <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                       value="{{ old('title') }}" required>
                                @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">المقتطف</label>
                                <textarea name="excerpt" rows="3" class="form-control @error('excerpt') is-invalid @enderror">{{ old('excerpt') }}</textarea>
                                <small class="text-muted">نبذة مختصرة عن المقال (اختياري)</small>
                                @error('excerpt')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">المحتوى <span class="text-danger">*</span></label>
                                <textarea name="content" id="content" class="form-control @error('content') is-invalid @enderror">{{ old('content') }}</textarea>
                                @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- SEO Settings -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">إعدادات SEO</div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">عنوان SEO (Meta Title)</label>
                                <input type="text" name="meta_title" class="form-control @error('meta_title') is-invalid @enderror"
                                       value="{{ old('meta_title') }}" maxlength="255">
                                <small class="text-muted">سيتم استخدام عنوان المقال إذا تُرك فارغاً</small>
                                @error('meta_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">وصف SEO (Meta Description)</label>
                                <textarea name="meta_description" rows="2" class="form-control @error('meta_description') is-invalid @enderror">{{ old('meta_description') }}</textarea>
                                @error('meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">الكلمات المفتاحية (Meta Keywords)</label>
                                <input type="text" name="meta_keywords" class="form-control @error('meta_keywords') is-invalid @enderror"
                                       value="{{ old('meta_keywords') }}">
                                <small class="text-muted">افصل الكلمات بفاصلة</small>
                                @error('meta_keywords')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">الكلمة المفتاحية الرئيسية (Focus Keyword)</label>
                                <input type="text" name="focus_keyword" class="form-control @error('focus_keyword') is-invalid @enderror"
                                       value="{{ old('focus_keyword') }}">
                                @error('focus_keyword')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">

                    <!-- Publish Settings -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">إعدادات النشر</div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">الحالة <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                    <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>منشور</option>
                                    <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>مجدول</option>
                                </select>
                                @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">تاريخ النشر</label>
                                <input type="datetime-local" name="published_at" class="form-control @error('published_at') is-invalid @enderror"
                                       value="{{ old('published_at') }}">
                                <small class="text-muted">اتركه فارغاً للنشر الفوري</small>
                                @error('published_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1"
                                       id="is_featured" {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">
                                    مقال مميز
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="allow_comments" value="1"
                                       id="allow_comments" {{ old('allow_comments', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="allow_comments">
                                    السماح بالتعليقات
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="is_indexable" value="1"
                                       id="is_indexable" {{ old('is_indexable', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_indexable">
                                    قابل للفهرسة (Index)
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_followable" value="1"
                                       id="is_followable" {{ old('is_followable', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_followable">
                                    قابل للمتابعة (Follow)
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">التصنيف</div>
                        </div>
                        <div class="card-body">
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">اختر التصنيف</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Tags -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">الوسوم</div>
                        </div>
                        <div class="card-body">
                            <div class="tags-container" style="max-height: 200px; overflow-y: auto;">
                                @foreach($tags as $tag)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="tags[]"
                                           value="{{ $tag->id }}" id="tag{{ $tag->id }}"
                                           {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tag{{ $tag->id }}">
                                        {{ $tag->name }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Featured Image -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">الصورة البارزة</div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <input type="file" name="featured_image" class="form-control @error('featured_image') is-invalid @enderror"
                                       accept="image/*" id="featuredImage">
                                @error('featured_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div id="imagePreview" class="mb-3" style="display: none;">
                                <img src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
                            </div>

                            <div class="mb-0">
                                <label class="form-label">نص بديل للصورة (Alt Text)</label>
                                <input type="text" name="featured_image_alt" class="form-control @error('featured_image_alt') is-invalid @enderror"
                                       value="{{ old('featured_image_alt') }}">
                                @error('featured_image_alt')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="card custom-card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary w-100 mb-2">
                                <i class="bi bi-save me-2"></i>
                                حفظ المقال
                            </button>
                            <a href="{{ route('admin.blog.posts.index') }}" class="btn btn-secondary w-100">
                                <i class="bi bi-x-circle me-2"></i>
                                إلغاء
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </form>

    </div>
</div>
@endsection

@section('scripts')
<script>
// TinyMCE Editor
tinymce.init({
    selector: '#content',
    height: 500,
    directionality: 'rtl',
    language: 'ar',
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount'
    ],
    toolbar: 'undo redo | blocks | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | link image | code | help',
    menubar: 'file edit view insert format tools table help',
    content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; direction: rtl; }',
    image_advtab: true,
    relative_urls: false,
    remove_script_host: false,
    file_picker_types: 'image',
    automatic_uploads: true,
    images_upload_url: '/upload',
});

// Image Preview
document.getElementById('featuredImage').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.querySelector('#imagePreview img').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endsection
