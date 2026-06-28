@extends('admin.layouts.master')

@section('page-title', 'تعديل صفحة توثيق')

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
                    @if($documentation_page->category)
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.docs.categories.show', $documentation_page->category) }}">{{ $documentation_page->category->name }}</a>
                        </li>
                    @endif
                    <li class="breadcrumb-item active">تعديل</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in doc-ai-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-edit-2 me-1"></i>
                        تحرير المقال
                    </span>
                    <h2 class="group-show-hero__title mb-2">{{ $documentation_page->title }}</h2>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <code class="doc-cat-slug">{{ $documentation_page->slug }}</code>
                        @if($documentation_page->status === 'published')
                            <span class="doc-cat-status doc-cat-status--published">
                                <span class="doc-cat-status__dot"></span>منشور
                            </span>
                        @else
                            <span class="doc-cat-status doc-cat-status--draft">مسودة</span>
                        @endif
                        @if($documentation_page->category)
                            <a href="{{ route('admin.docs.categories.show', $documentation_page->category) }}" class="doc-cat-chip doc-cat-chip--section text-decoration-none">
                                <i class="fe fe-folder"></i>{{ $documentation_page->category->name }}
                            </a>
                        @endif
                    </div>
                    <p class="group-show-hero__desc mb-0">
                        آخر تحديث: {{ $documentation_page->updated_at?->diffForHumans() }}
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('admin.docs.pages.index') }}" class="btn btn-light border" title="رجوع للقائمة">
                            <i class="fe fe-list"></i>
                        </a>
                        @if($documentation_page->isPublished() && $documentation_page->category?->is_active)
                            <a href="{{ route('frontend.docs.show', ['categorySlug' => $documentation_page->category->slug, 'pagePath' => $documentation_page->slugPathUnderCategory()]) }}"
                               class="btn btn-light border"
                               target="_blank"
                               rel="noopener noreferrer"
                               title="عرض على الموقع">
                                <i class="fe fe-external-link"></i>
                            </a>
                        @endif
                        <a href="{{ route('admin.docs.pages.pdf', $documentation_page) }}"
                           class="btn btn-light border"
                           target="_blank"
                           rel="noopener"
                           title="تصدير PDF">
                            <i class="fe fe-download"></i>
                        </a>
                        <a href="{{ route('admin.docs.ai-pages.enhance', ['documentation_page_id' => $documentation_page->id]) }}"
                           class="btn btn-outline-primary"
                           title="إضافة أفكار بالذكاء">
                            <i class="fe fe-zap"></i>
                        </a>
                        <a href="{{ route('admin.docs.ai-pages.improve', ['documentation_page_id' => $documentation_page->id]) }}"
                           class="btn btn-outline-secondary"
                           title="تحسين بالذكاء الاصطناعي">
                            <i class="fe fe-tool"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.docs.pages.update', $documentation_page) }}" method="POST">
            @csrf
            @method('PUT')

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
                                <input type="text" name="title" id="doc_title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $documentation_page->title) }}" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="doc_slug">الرابط (slug)</label>
                                <div class="input-group">
                                    <input type="text" name="slug" id="doc_slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $documentation_page->slug) }}">
                                    <button type="button" class="btn btn-light border" id="doc_generate_slug" title="توليد من العنوان">
                                        <i class="fe fe-refresh-cw"></i>
                                    </button>
                                </div>
                                @error('slug')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="doc_excerpt">المقتطف</label>
                                <textarea name="excerpt" id="doc_excerpt" rows="2" class="form-control @error('excerpt') is-invalid @enderror">{{ old('excerpt', $documentation_page->excerpt) }}</textarea>
                                @error('excerpt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-0">
                                <label class="form-label" for="doc_content">المحتوى <span class="text-danger">*</span></label>
                                <textarea name="content" id="doc_content" class="form-control @error('content') is-invalid @enderror" rows="12">{{ old('content', $documentation_page->content) }}</textarea>
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
                                <input type="text" name="meta_title" id="meta_title" class="form-control" value="{{ old('meta_title', $documentation_page->meta_title) }}" maxlength="255">
                            </div>
                            <div class="mb-0">
                                <label class="form-label" for="meta_description">وصف Meta</label>
                                <textarea name="meta_description" id="meta_description" rows="2" class="form-control">{{ old('meta_description', $documentation_page->meta_description) }}</textarea>
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

                        <div class="card custom-card doc-ai-panel doc-ai-save-card doc-ai-animate mb-4">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary w-100 mb-3">
                                    <i class="fe fe-save me-1"></i>حفظ التعديلات
                                </button>
                                <a href="{{ route('admin.docs.pages.index') }}" class="btn btn-light border w-100">
                                    <i class="fe fe-arrow-right me-1"></i>رجوع للقائمة
                                </a>
                            </div>
                        </div>

                        <div class="card custom-card doc-ai-panel doc-ai-animate">
                            <div class="card-header doc-ai-panel__header border-0 pb-0">
                                <h6 class="doc-ai-panel__title mb-0">
                                    <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--ai"><i class="fe fe-more-horizontal"></i></span>
                                    إجراءات سريعة
                                </h6>
                            </div>
                            <div class="card-body pt-3">
                                <div class="doc-cat-actions justify-content-center">
                                    <form action="{{ route('admin.docs.pages.toggle-publish', $documentation_page) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-sm doc-cat-action-btn"
                                                title="{{ $documentation_page->status === 'published' ? 'إلغاء النشر' : 'نشر' }}">
                                            <i class="fe {{ $documentation_page->status === 'published' ? 'fe-eye-off' : 'fe-eye' }}"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.docs.ai-pages.enhance', ['documentation_page_id' => $documentation_page->id]) }}"
                                       class="btn btn-sm doc-cat-action-btn doc-cat-action-btn--primary"
                                       title="إضافة أفكار">
                                        <i class="fe fe-zap"></i>
                                    </a>
                                    <a href="{{ route('admin.docs.ai-pages.improve', ['documentation_page_id' => $documentation_page->id]) }}"
                                       class="btn btn-sm doc-cat-action-btn"
                                       title="تحسين بالذكاء">
                                        <i class="fe fe-tool"></i>
                                    </a>
                                    <a href="{{ route('admin.docs.pages.pdf', $documentation_page) }}"
                                       class="btn btn-sm doc-cat-action-btn"
                                       target="_blank"
                                       rel="noopener"
                                       title="PDF">
                                        <i class="fe fe-download"></i>
                                    </a>
                                    <form action="{{ route('admin.docs.pages.destroy', $documentation_page) }}" method="POST" class="d-inline" onsubmit="return confirm('حذف «{{ $documentation_page->title }}»؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm doc-cat-action-btn doc-cat-action-btn--danger" title="حذف">
                                            <i class="fe fe-trash-2"></i>
                                        </button>
                                    </form>
                                </div>
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
    if (btn && titleEl && slugEl) {
        btn.addEventListener('click', function () {
            slugEl.value = slugify(titleEl.value);
        });
    }
});
</script>
@endsection
