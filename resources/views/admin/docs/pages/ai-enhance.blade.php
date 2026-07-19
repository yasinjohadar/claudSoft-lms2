@extends('admin.layouts.master')

@section('page-title', 'إضافة أفكار للتوثيق بالذكاء')

@section('styles')
@include('admin.docs.categories.partials.styles')
@include('admin.docs.pages.partials.ai-page-styles')
@include('admin.docs.pages.partials.ai-enhance-styles')
<link rel="stylesheet" href="{{ asset('docs/css/style.css') }}" media="print" onload="this.media='all'" id="docs-preview-css">
<noscript><link rel="stylesheet" href="{{ asset('docs/css/style.css') }}"></noscript>
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
                    <li class="breadcrumb-item active">إضافة أفكار بالذكاء</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in doc-ai-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-zap me-1"></i>
                        مساعد التوثيق
                    </span>
                    <h2 class="group-show-hero__title mb-2">إضافة أفكار للتوثيق بالذكاء الاصطناعي</h2>
                    <p class="group-show-hero__desc mb-2">
                        اختر صفحة موجودة، صف ما تريد إضافته، ثم راجع المقارنة بين القديم والجديد قبل الموافقة والحفظ.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        @if(!empty($docsEngineChoiceAvailable))
                            <span class="doc-ai-badge"><i class="fe fe-layers"></i>محركان متاحان</span>
                        @elseif(!empty($useLaravelAiEngine))
                            <span class="doc-ai-badge"><i class="fe fe-cpu"></i>Laravel AI SDK</span>
                        @endif
                        <span class="doc-ai-badge"><i class="fe fe-shield"></i>يحافظ على المحتوى الحالي</span>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('admin.docs.pages.index') }}" class="btn btn-light border" title="قائمة الصفحات">
                            <i class="fe fe-list"></i>
                        </a>
                        <a href="{{ route('admin.docs.ai-pages.improve') }}" class="btn btn-outline-secondary" title="تحسين محتوى">
                            <i class="fe fe-tool"></i>
                        </a>
                        <a href="{{ route('admin.docs.pages.create') }}" class="btn btn-outline-primary" title="إضافة يدوية">
                            <i class="fe fe-plus"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if($prefillPage)
            <div class="doc-ai-prefill-banner doc-ai-animate">
                <p class="doc-ai-prefill-banner__text mb-0">
                    <i class="fe fe-file-text me-1 text-primary"></i>
                    الصفحة المختارة: <strong>{{ $prefillPage->title }}</strong>
                </p>
                <a href="{{ route('admin.docs.pages.edit', $prefillPage) }}" class="btn btn-sm btn-outline-primary" id="editPageLinkBanner">
                    <i class="fe fe-edit me-1"></i>فتح التحرير الكامل
                </a>
            </div>
        @endif

        <form id="enhanceSaveForm" method="POST" action="{{ $prefillPage ? route('admin.docs.pages.update', $prefillPage) : '#' }}">
            @csrf
            @if($prefillPage)
                @method('PUT')
            @endif

            <input type="hidden" name="documentation_category_id" id="save_category_id" value="{{ old('documentation_category_id', $prefillPage?->documentation_category_id) }}">
            <input type="hidden" name="parent_id" id="save_parent_id" value="{{ old('parent_id', $prefillPage?->parent_id) }}">
            <input type="hidden" name="sort_order" id="save_sort_order" value="{{ old('sort_order', $prefillPage?->sort_order ?? 0) }}">
            <input type="hidden" name="status" id="save_status" value="{{ old('status', $prefillPage?->status ?? 'draft') }}">
            <input type="hidden" name="published_at" id="save_published_at" value="{{ old('published_at', $prefillPage?->published_at?->format('Y-m-d\TH:i')) }}">
            <input type="hidden" name="meta_title" id="save_meta_title" value="{{ old('meta_title', $prefillPage?->meta_title) }}">
            <input type="hidden" name="meta_description" id="save_meta_description" value="{{ old('meta_description', $prefillPage?->meta_description) }}">
            <input type="hidden" name="is_indexable" id="save_is_indexable" value="{{ old('is_indexable', $prefillPage?->is_indexable ?? true) ? '1' : '0' }}">
            <input type="hidden" name="title" id="save_title" value="{{ old('title', $prefillPage?->title) }}">
            <input type="hidden" name="slug" id="save_slug" value="{{ old('slug', $prefillPage?->slug) }}">
            <input type="hidden" name="excerpt" id="save_excerpt" value="{{ old('excerpt', $prefillPage?->excerpt) }}">
            <textarea id="doc_original" name="_original_content" class="d-none" readonly>{{ old('_original_content', $prefillPage?->content ?? '') }}</textarea>

            <div class="row g-4">
                {{-- المحتوى الرئيسي — مثل بطاقة «المحتوى» في create --}}
                <div class="col-lg-8">
                    <div class="card custom-card doc-ai-panel doc-cat-filter-card doc-ai-animate mb-4">
                        <div class="card-header doc-ai-panel__header border-0">
                            <h6 class="doc-ai-panel__title">
                                <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--content"><i class="fe fe-file-text"></i></span>
                                الصفحة والأفكار
                            </h6>
                        </div>
                        <div class="card-body pt-2">
                            <div class="mb-3">
                                <label class="form-label" for="page_picker">صفحة التوثيق <span class="text-danger">*</span></label>
                                <select id="page_picker" class="form-select">
                                    <option value="">— اختر صفحة —</option>
                                    @foreach($pagesJson as $pageItem)
                                        <option value="{{ $pageItem['id'] }}" {{ (string) ($prefillPage?->id) === (string) $pageItem['id'] ? 'selected' : '' }}>
                                            {{ $pageItem['category_name'] }} — {{ $pageItem['title'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="doc-ai-hint mb-0">اختر المقال الذي تريد إضافة محتوى إليه</p>
                            </div>

                            <div class="doc-enhance-page-meta mb-3" id="pageMeta" style="{{ $prefillPage ? '' : 'display:none' }}">
                                @if($prefillPage)
                                    <span class="doc-cat-slug">{{ $prefillPage->slug }}</span>
                                    @if($prefillPage->status === 'published')
                                        <span class="doc-cat-status doc-cat-status--published"><span class="doc-cat-status__dot"></span>منشور</span>
                                    @else
                                        <span class="doc-cat-status doc-cat-status--draft">مسودة</span>
                                    @endif
                                    @if($prefillPage->category)
                                        <span class="doc-cat-chip doc-cat-chip--section"><i class="fe fe-folder"></i>{{ $prefillPage->category->name }}</span>
                                    @endif
                                @endif
                            </div>

                            <div class="mb-0">
                                <label class="form-label" for="user_notes">وصف الأفكار والإضافات <span class="text-danger">*</span></label>
                                <textarea id="user_notes" class="form-control doc-ai-notes-area" rows="10" placeholder="صف بالتفصيل ما تريد إضافته، مثال:&#10;• أضف قسم «أمثلة عملية» بعد المقدمة مع 3 أمثلة كود&#10;• أضف جدول مقارنة بين X و Y&#10;• أضف صندوق تنبيه للأخطاء الشائعة"></textarea>
                                <p class="doc-ai-hint mb-0 mt-2"><span class="text-danger">*</span> مطلوب — 10 أحرف على الأقل. يُحافظ على المحتوى الحالي ويُضاف إليه فقط.</p>
                            </div>
                        </div>
                    </div>

                    {{-- مراجعة النتيجة — مثل بطاقة SEO في create --}}
                    <div class="card custom-card doc-ai-panel doc-cat-table-card doc-ai-animate doc-enhance-review mb-4" id="reviewSection">
                        <div class="card-header doc-ai-panel__header border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <h6 class="doc-ai-panel__title mb-0">
                                <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--seo"><i class="fe fe-git-merge"></i></span>
                                مراجعة النتيجة
                            </h6>
                            <span class="badge bg-success-transparent text-success">جاهز للموافقة</span>
                        </div>
                        <div class="card-body pt-2">
                            <div class="doc-enhance-stats" id="enhanceStats"></div>

                            <ul class="nav nav-pills doc-enhance-tabs mb-3" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-preview" type="button">
                                        <i class="fe fe-eye me-1"></i>معاينة
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-diff" type="button">
                                        <i class="fe fe-git-merge me-1"></i>مقارنة
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" id="tab-edit-btn" data-bs-toggle="pill" data-bs-target="#tab-edit" type="button">
                                        <i class="fe fe-edit-2 me-1"></i>تحرير
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="tab-preview">
                                    <div class="doc-enhance-preview-grid">
                                        <div class="doc-enhance-preview-pane">
                                            <div class="doc-enhance-preview-pane__header doc-enhance-preview-pane__header--old">القديم</div>
                                            <div class="doc-enhance-preview-pane__body docs-content" id="previewOld"></div>
                                        </div>
                                        <div class="doc-enhance-preview-pane">
                                            <div class="doc-enhance-preview-pane__header doc-enhance-preview-pane__header--new">الجديد</div>
                                            <div class="doc-enhance-preview-pane__body docs-content" id="previewNew"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="tab-diff">
                                    <div class="doc-enhance-diff" id="diffOutput"></div>
                                </div>
                                <div class="tab-pane fade" id="tab-edit">
                                    <textarea name="content" id="doc_result" class="form-control" rows="14"></textarea>
                                </div>
                            </div>

                            <div class="doc-enhance-review-actions">
                                <button type="button" class="btn btn-light border btn-sm" id="btnRegenerate">
                                    <i class="fe fe-refresh-cw me-1"></i>إعادة التوليد
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" id="btnReject">
                                    <i class="fe fe-x me-1"></i>رفض
                                </button>
                                <button type="button" class="btn btn-success btn-sm ms-auto" id="btnApprove" disabled>
                                    <i class="fe fe-check me-1"></i>الموافقة والحفظ
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- الشريط الجانبي — مثل «التصنيف والهيكل» + «حفظ» في create --}}
                <div class="col-lg-4">
                    <div class="doc-ai-sidebar-sticky">
                        <div class="card custom-card doc-ai-panel doc-cat-filter-card doc-ai-animate mb-4">
                            <div class="card-header doc-ai-panel__header border-0">
                                <h6 class="doc-ai-panel__title">
                                    <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--ai"><i class="fe fe-sliders"></i></span>
                                    إعدادات الذكاء
                                </h6>
                            </div>
                            <div class="card-body pt-2">
                                @include('admin.docs.pages.partials.ai-enhance-settings')
                            </div>
                        </div>

                        <div class="card custom-card doc-ai-panel doc-ai-save-card doc-ai-animate">
                            <div class="card-body">
                                <p class="doc-ai-hint mb-3" id="enhanceHint">اختر صفحة واكتب الأفكار لتفعيل الزر.</p>
                                <button type="button" class="btn btn-primary w-100 mb-3" id="enhanceBtn" disabled>
                                    <span class="loading-spinner spinner-border spinner-border-sm me-1" role="status"></span>
                                    <i class="fe fe-zap me-1"></i>
                                    <span class="btn-text">تطبيق الأفكار</span>
                                </button>
                                @if($prefillPage)
                                    <a href="{{ route('admin.docs.pages.edit', $prefillPage) }}" class="btn btn-light border w-100 mb-3" id="editPageLink">
                                        <i class="fe fe-edit-2 me-1"></i>فتح التحرير الكامل
                                    </a>
                                @else
                                    <a href="#" class="btn btn-light border w-100 mb-3 disabled" id="editPageLink" aria-disabled="true">
                                        <i class="fe fe-edit-2 me-1"></i>فتح التحرير الكامل
                                    </a>
                                @endif
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

<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title"><i class="fe fe-check-circle text-success me-1"></i>تأكيد الحفظ</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">سيتم استبدال محتوى الصفحة بالنسخة الجديدة.</p>
                <p class="text-muted small mb-0" id="approvePageTitle"></p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-success" id="confirmApproveBtn">
                    <i class="fe fe-save me-1"></i>موافقة وحفظ
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>document.documentElement.classList.add('loaded');</script>
@php($tinymceSelector = '#doc_result')
@include('admin.docs.partials.tinymce-doc')
@include('admin.docs.pages.partials.ai-job-poller')
@include('admin.docs.pages.partials.ai-enhance-scripts')
@endsection
