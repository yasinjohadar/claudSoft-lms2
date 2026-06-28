@extends('admin.layouts.master')

@section('page-title', 'إضافة صفحة توثيق')

@section('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet" />
@include('admin.docs.categories.partials.styles')
@include('admin.docs.pages.partials.ai-page-styles')
@endsection

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        @include('admin.components.alerts')

        <div class="my-4 page-header-breadcrumb doc-ai-animate dashboard-fade-in">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.docs.pages.index') }}">صفحات التوثيق</a></li>
                    <li class="breadcrumb-item active">إضافة صفحة</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in doc-ai-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-plus me-1"></i>
                        مقال جديد
                    </span>
                    <h2 class="group-show-hero__title mb-2">إضافة صفحة توثيق</h2>
                    <p class="group-show-hero__desc mb-0">
                        أنشئ مقال توثيق يدوياً — أو استخدم
                        <a href="{{ route('admin.docs.ai-pages.create') }}">التوليد بالذكاء الاصطناعي</a>
                        لتسريع العمل.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('admin.docs.pages.index') }}" class="btn btn-light border" title="رجوع للقائمة">
                            <i class="fe fe-list"></i>
                        </a>
                        <a href="{{ route('admin.docs.ai-pages.create') }}" class="btn btn-outline-primary" title="توليد بالذكاء الاصطناعي">
                            <i class="fe fe-zap"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.docs.pages.store') }}" method="POST">
            @csrf

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card custom-card doc-ai-panel doc-cat-filter-card doc-ai-animate mb-4">
                        <div class="card-header doc-ai-panel__header border-0">
                            <h6 class="doc-ai-panel__title">
                                <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--content"><i class="fe fe-file-text"></i></span>
                                المحتوى
                            </h6>
                        </div>
                        <div class="card-body pt-2">
                            <div class="mb-3">
                                <label class="form-label" for="doc_title">العنوان <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="doc_title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="doc_slug">الرابط (slug)</label>
                                <div class="input-group">
                                    <input type="text" name="slug" id="doc_slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="يُولَّد من العنوان إن وُجد فارغاً">
                                    <button type="button" class="btn btn-light border" id="doc_generate_slug" title="توليد من العنوان">
                                        <i class="fe fe-refresh-cw"></i>
                                    </button>
                                </div>
                                <p class="doc-ai-hint mb-0">فريد ضمن نفس القسم والمستوى (الأب)</p>
                                @error('slug')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="doc_excerpt">المقتطف</label>
                                <textarea name="excerpt" id="doc_excerpt" rows="2" class="form-control @error('excerpt') is-invalid @enderror">{{ old('excerpt') }}</textarea>
                                @error('excerpt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-0">
                                <label class="form-label" for="doc_content">المحتوى <span class="text-danger">*</span></label>
                                <textarea name="content" id="doc_content" class="form-control @error('content') is-invalid @enderror" rows="12">{{ old('content') }}</textarea>
                                @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card doc-ai-panel doc-cat-filter-card doc-ai-animate mb-4">
                        <div class="card-header doc-ai-panel__header border-0">
                            <h6 class="doc-ai-panel__title">
                                <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--seo"><i class="fe fe-search"></i></span>
                                SEO
                            </h6>
                        </div>
                        <div class="card-body pt-2">
                            <div class="mb-3">
                                <label class="form-label" for="meta_title">عنوان Meta</label>
                                <input type="text" name="meta_title" id="meta_title" class="form-control" value="{{ old('meta_title') }}" maxlength="255">
                            </div>
                            <div class="mb-0">
                                <label class="form-label" for="meta_description">وصف Meta</label>
                                <textarea name="meta_description" id="meta_description" rows="2" class="form-control">{{ old('meta_description') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="doc-ai-sidebar-sticky">
                        <div class="card custom-card doc-ai-panel doc-cat-filter-card doc-ai-animate mb-4">
                            <div class="card-header doc-ai-panel__header border-0">
                                <h6 class="doc-ai-panel__title">
                                    <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--meta"><i class="fe fe-layers"></i></span>
                                    التصنيف والهيكل
                                </h6>
                            </div>
                            <div class="card-body pt-2">
                                <div class="mb-3">
                                    <label class="form-label">القسم <span class="text-danger">*</span></label>
                                    <select name="documentation_category_id" class="form-select @error('documentation_category_id') is-invalid @enderror" required>
                                        <option value="">— اختر —</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ (string) old('documentation_category_id', $categoryId) === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('documentation_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">صفحة أب (اختياري)</label>
                                    <select name="parent_id" class="form-select">
                                        <option value="">— بدون —</option>
                                        @foreach($parentOptions as $id => $label)
                                            <option value="{{ $id }}" {{ (string) old('parent_id') === (string) $id ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <p class="doc-ai-hint mb-0">يجب أن تنتمي لنفس القسم عند الحفظ</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الترتيب</label>
                                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الحالة <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>مسودة</option>
                                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>منشور</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">تاريخ النشر</label>
                                    <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at') }}">
                                    <p class="doc-ai-hint mb-0">عند «منشور» يُعبَّأ تلقائياً إن تُرك فارغاً</p>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_indexable" value="1" id="is_indexable" {{ old('is_indexable', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_indexable">قابلة للفهرسة (SEO)</label>
                                </div>
                            </div>
                        </div>

                        <div class="card custom-card doc-ai-panel doc-ai-save-card doc-ai-animate">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary w-100 mb-3">
                                    <i class="fe fe-save me-1"></i>حفظ الصفحة
                                </button>
                                <a href="{{ route('admin.docs.pages.index') }}" class="btn btn-light border w-100">
                                    <i class="fe fe-x me-1"></i>إلغاء
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>document.documentElement.classList.add('loaded');</script>
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
    if (titleEl && slugEl) {
        titleEl.addEventListener('input', function () {
            if (!slugEl.dataset.touched) slugEl.value = slugify(this.value);
        });
        slugEl.addEventListener('input', function () { this.dataset.touched = '1'; });
    }
    if (btn && titleEl && slugEl) {
        btn.addEventListener('click', function () {
            slugEl.value = slugify(titleEl.value);
            delete slugEl.dataset.touched;
        });
    }
});
</script>
@endsection
