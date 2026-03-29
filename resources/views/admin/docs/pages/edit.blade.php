@extends('admin.layouts.master')

@section('page-title', 'تعديل صفحة توثيق')

@section('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet" />
@endsection

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">تعديل صفحة توثيق: {{ $documentation_page->title }}</h4>
                <p class="mb-0 text-muted"><code>{{ $documentation_page->slug }}</code></p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.docs.ai-pages.improve', ['documentation_page_id' => $documentation_page->id]) }}" class="btn btn-outline-primary">
                    <i class="fas fa-magic me-1"></i>تحسين بالذكاء الاصطناعي
                </a>
                <a href="{{ route('admin.docs.pages.index') }}" class="btn btn-secondary">رجوع للقائمة</a>
            </div>
        </div>

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">@foreach($errors->all() as $e2)<li>{{ $e2 }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <form action="{{ route('admin.docs.pages.update', $documentation_page) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-lg-8">
                    <div class="card custom-card mb-4">
                        <div class="card-header"><div class="card-title">المحتوى</div></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">العنوان <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="doc_title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $documentation_page->title) }}" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">الرابط (slug)</label>
                                <div class="input-group">
                                    <input type="text" name="slug" id="doc_slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $documentation_page->slug) }}">
                                    <button type="button" class="btn btn-outline-secondary" id="doc_generate_slug">توليد</button>
                                </div>
                                @error('slug')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">المقتطف</label>
                                <textarea name="excerpt" rows="2" class="form-control @error('excerpt') is-invalid @enderror">{{ old('excerpt', $documentation_page->excerpt) }}</textarea>
                                @error('excerpt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-0">
                                <label class="form-label">المحتوى <span class="text-danger">*</span></label>
                                <textarea name="content" id="doc_content" class="form-control @error('content') is-invalid @enderror" rows="12">{{ old('content', $documentation_page->content) }}</textarea>
                                @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card mb-4">
                        <div class="card-header"><div class="card-title">SEO</div></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">عنوان Meta</label>
                                <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $documentation_page->meta_title) }}" maxlength="255">
                            </div>
                            <div class="mb-0">
                                <label class="form-label">وصف Meta</label>
                                <textarea name="meta_description" rows="2" class="form-control">{{ old('meta_description', $documentation_page->meta_description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card custom-card mb-4">
                        <div class="card-header"><div class="card-title">التصنيف والهيكل</div></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">القسم <span class="text-danger">*</span></label>
                                <select name="documentation_category_id" class="form-select @error('documentation_category_id') is-invalid @enderror" required>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ (string) old('documentation_category_id', $documentation_page->documentation_category_id) === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('documentation_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">صفحة أب (اختياري)</label>
                                <select name="parent_id" class="form-select">
                                    <option value="">— بدون —</option>
                                    @foreach($parentOptions as $id => $label)
                                    <option value="{{ $id }}" {{ (string) old('parent_id', $documentation_page->parent_id) === (string) $id ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">الترتيب</label>
                                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $documentation_page->sort_order) }}" min="0">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">الحالة <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="draft" {{ old('status', $documentation_page->status) === 'draft' ? 'selected' : '' }}>مسودة</option>
                                    <option value="published" {{ old('status', $documentation_page->status) === 'published' ? 'selected' : '' }}>منشور</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">تاريخ النشر</label>
                                <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', $documentation_page->published_at?->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_indexable" value="1" id="is_indexable" {{ old('is_indexable', $documentation_page->is_indexable) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_indexable">قابلة للفهرسة (SEO)</label>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card mb-4">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary w-100 mb-2"><i class="bi bi-save me-2"></i>حفظ التعديلات</button>
                            <a href="{{ route('admin.docs.pages.index') }}" class="btn btn-secondary w-100">رجوع</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="row">
            <div class="col-lg-4 ms-lg-auto">
                <div class="card custom-card">
                    <div class="card-header"><div class="card-title">إجراءات سريعة</div></div>
                    <div class="card-body">
                        <form action="{{ route('admin.docs.pages.toggle-publish', $documentation_page) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary w-100">نشر / إلغاء نشر</button>
                        </form>
                        <form action="{{ route('admin.docs.pages.destroy', $documentation_page) }}" method="POST" onsubmit="return confirm('حذف هذه الصفحة؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">حذف الصفحة</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@include('admin.docs.partials.tinymce-doc')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var titleEl = document.getElementById('doc_title');
    var slugEl  = document.getElementById('doc_slug');
    var btn     = document.getElementById('doc_generate_slug');
    function slugify(t) {
        if (!t) return '';
        return t.toString().trim()
            .replace(/\s+/g, '-')
            .replace(/[^\u0600-\u06FFa-zA-Z0-9-]/g, '')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '') || ('page-' + Date.now());
    }
    if (btn && titleEl && slugEl) {
        btn.addEventListener('click', function () {
            slugEl.value = slugify(titleEl.value);
        });
    }
});
</script>
@endsection
