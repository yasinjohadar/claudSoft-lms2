@extends('admin.layouts.master')

@section('page-title', 'فحص وتعديل التوثيق بالتعليمات (ذكاء اصطناعي)')

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
                    <li class="breadcrumb-item"><a href="{{ route('admin.docs.pages.index') }}">التوثيق</a></li>
                    <li class="breadcrumb-item active">فحص وتعديل بالذكاء</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in doc-ai-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-tool me-1"></i>
                        تحسين المحتوى
                    </span>
                    <h2 class="group-show-hero__title mb-2">فحص المستند وتعديله بالذكاء الاصطناعي</h2>
                    <p class="group-show-hero__desc mb-2">
                        يُرسل <strong>المصدر HTML بالكامل</strong> إلى النموذج دفعة واحدة. اكتب تعليمات محددة (حذف، إعادة هيكلة، توحيد مصطلحات…) ثم راجع النتيجة قبل الحفظ.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        @if(!empty($docsEngineChoiceAvailable))
                            <span class="doc-ai-badge"><i class="fe fe-layers"></i>محركان متاحان</span>
                        @elseif(!empty($useLaravelAiEngine))
                            <span class="doc-ai-badge"><i class="fe fe-cpu"></i>Laravel AI SDK</span>
                        @endif
                        <span class="doc-ai-badge"><i class="fe fe-file-text"></i>حد {{ number_format(\App\Services\Ai\AIDocumentationPageService::MAX_REFINE_SOURCE_CHARS) }} حرف</span>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('admin.docs.pages.index') }}" class="btn btn-light border">
                            <i class="fe fe-list me-1"></i>قائمة الصفحات
                        </a>
                        <a href="{{ route('admin.docs.ai-pages.create') }}" class="btn btn-outline-primary">
                            <i class="fe fe-zap me-1"></i>توليد صفحة جديدة
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if($prefillPage)
            <div class="doc-ai-prefill-banner doc-ai-animate">
                <p class="doc-ai-prefill-banner__text mb-0">
                    <i class="fe fe-download-cloud me-1 text-primary"></i>
                    تم تحميل محتوى الصفحة: <strong>{{ $prefillPage->title }}</strong>
                </p>
                <a href="{{ route('admin.docs.pages.edit', $prefillPage) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fe fe-edit me-1"></i>فتح التحرير الكامل
                </a>
            </div>
        @endif

        <form id="improveSaveForm" method="POST" action="{{ $prefillPage ? route('admin.docs.pages.update', $prefillPage) : route('admin.docs.ai-pages.store') }}">
            @csrf
            @if($prefillPage)
                @method('PUT')
            @endif

            <div class="row g-4">
                <div class="col-lg-4 order-lg-2">
                    <div class="doc-ai-sidebar-sticky">
                        <div class="card custom-card doc-ai-panel doc-cat-filter-card doc-ai-animate mb-4">
                            <div class="card-header doc-ai-panel__header border-0">
                                <h6 class="doc-ai-panel__title">
                                    <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--ai"><i class="fe fe-sliders"></i></span>
                                    إعدادات التحسين
                                    <span class="doc-ai-step-badge">1</span>
                                </h6>
                            </div>
                            <div class="card-body pt-2">
                                @if(!empty($docsEngineChoiceAvailable))
                                    <div class="mb-3">
                                        <label class="form-label d-block">محرك التوثيق</label>
                                        <div class="doc-ai-engine-pills">
                                            <div class="doc-ai-engine-pill">
                                                <input type="radio" name="docs_engine" id="docs_engine_laravel_ai" value="laravel_ai" {{ !empty($useLaravelAiEngine) ? 'checked' : '' }}>
                                                <label for="docs_engine_laravel_ai"><i class="fe fe-cpu"></i>Laravel AI SDK</label>
                                            </div>
                                            <div class="doc-ai-engine-pill">
                                                <input type="radio" name="docs_engine" id="docs_engine_legacy" value="legacy" {{ empty($useLaravelAiEngine) ? 'checked' : '' }}>
                                                <label for="docs_engine_legacy"><i class="fe fe-database"></i>موديلات قديمة</label>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($models->isEmpty() && $laravelAiModels->isEmpty())
                                    <div class="alert alert-warning border-0 mb-3">لا يوجد موديل نشط في كلا النظامين.</div>
                                @else
                                    @if(!empty($docsEngineChoiceAvailable) || ($laravelAiModels->isNotEmpty() && $models->isEmpty()))
                                        <div id="docs_engine_laravel_wrap" class="docs-engine-model-wrap mb-3" style="{{ !empty($docsEngineChoiceAvailable) && empty($useLaravelAiEngine) ? 'display:none' : '' }}">
                                            <label class="form-label" for="laravel_ai_model_id">موديل Laravel AI SDK</label>
                                            <select id="laravel_ai_model_id" class="form-select" @if($laravelAiModels->isEmpty()) disabled @endif>
                                                <option value="">افتراضي (docs.refine)</option>
                                                @foreach($laravelAiModels as $lmodel)
                                                    <option value="{{ $lmodel->id }}">{{ $lmodel->name }} — {{ $lmodel->provider }}/{{ $lmodel->model }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    @if(!empty($docsEngineChoiceAvailable) || ($models->isNotEmpty() && $laravelAiModels->isEmpty()))
                                        <div id="docs_engine_legacy_wrap" class="docs-engine-model-wrap mb-3" style="{{ !empty($docsEngineChoiceAvailable) && !empty($useLaravelAiEngine) ? 'display:none' : '' }}">
                                            <label class="form-label" for="ai_model_id">موديل AI (بنك الموديلات)</label>
                                            <select id="ai_model_id" class="form-select" @if($models->isEmpty()) disabled @endif>
                                                <option value="">الافتراضي</option>
                                                @foreach($models as $model)
                                                    <option value="{{ $model->id }}">{{ $model->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                @endif

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label" for="tone">الأسلوب</label>
                                        <select id="tone" class="form-select">
                                            <option value="professional" selected>احترافي</option>
                                            <option value="friendly">ودود</option>
                                            <option value="technical">تقني</option>
                                            <option value="casual">عادي</option>
                                            <option value="formal">رسمي</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="language">اللغة</label>
                                        <select id="language" class="form-select">
                                            <option value="ar" selected>العربية</option>
                                            <option value="en">English</option>
                                        </select>
                                    </div>
                                </div>

                                <div id="doc_structure_length_wrap" class="mb-3" style="display:none;">
                                    <label class="form-label" for="content_length">الطول المستهدف</label>
                                    <select id="content_length" class="form-select">
                                        <option value="short">قصير</option>
                                        <option value="medium" selected>متوسط</option>
                                        <option value="long">طويل</option>
                                    </select>
                                </div>

                                <div class="form-check mb-3" id="doc_update_excerpt_wrap">
                                    <input class="form-check-input" type="checkbox" id="update_excerpt">
                                    <label class="form-check-label" for="update_excerpt">طلب مقتطف (excerpt) مقترح</label>
                                </div>

                                <button type="button" class="doc-ai-refine-btn" id="refineBtn">
                                    <span class="loading-spinner spinner-border spinner-border-sm" role="status"></span>
                                    <i class="fe fe-zap"></i>
                                    <span class="btn-text">تحسين المحتوى</span>
                                </button>
                                <div id="docAiProgressWrap" class="mt-3" style="display:none;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted" id="docAiProgressLabel">جاري التحسين…</small>
                                        <small class="fw-semibold" id="docAiProgressPct">0%</small>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="docAiProgressBar" role="progressbar" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card custom-card doc-ai-panel doc-cat-table-card doc-ai-animate">
                            <div class="card-header doc-ai-panel__header border-0">
                                <h6 class="doc-ai-panel__title">
                                    <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--meta"><i class="fe fe-message-square"></i></span>
                                    التعليمات المحددة
                                    <span class="doc-ai-step-badge">2</span>
                                </h6>
                            </div>
                            <div class="card-body pt-2">
                                <p class="doc-ai-hint mb-2">اترك الحقل فارغاً للتحسين العام. أو صف التغييرات على <strong>كامل</strong> الصفحة.</p>
                                <textarea id="user_notes" class="form-control doc-ai-notes-area" placeholder="أمثلة: احذف القسم الفلاني؛ وحّد مصطلح «المتغير»؛ أضف جدول مقارنة بعد المقدمة؛ اختصر الملحقات…"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 order-lg-1">
                    <div class="card custom-card doc-ai-panel doc-cat-table-card doc-ai-animate mb-4">
                        <div class="card-header doc-ai-panel__header border-0">
                            <h6 class="doc-ai-panel__title mb-1">
                                <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--content"><i class="fe fe-code"></i></span>
                                <span id="doc_source_title">المصدر (HTML)</span>
                            </h6>
                            <p class="doc-ai-hint mb-2" id="doc_source_hint">المحتوى الكامل للصفحة — يُعاد صياغته دفعة واحدة.</p>
                            <div class="doc-ai-engine-pills">
                                <div class="doc-ai-engine-pill">
                                    <input type="radio" name="doc_input_mode" id="doc_mode_html" value="html" checked>
                                    <label for="doc_mode_html"><i class="fe fe-code"></i>HTML جاهز</label>
                                </div>
                                <div class="doc-ai-engine-pill">
                                    <input type="radio" name="doc_input_mode" id="doc_mode_raw" value="raw">
                                    <label for="doc_mode_raw"><i class="fe fe-file-text"></i>محتوى خام (نص، Markdown، JSON)</label>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-2">
                            <textarea id="doc_source" class="form-control" rows="14">{{ old('source', $prefillPage?->content ?? '') }}</textarea>
                            <div id="doc_raw_wrap" style="display:none;">
                                <div class="d-flex justify-content-end mb-2">
                                    <button type="button" class="btn btn-sm btn-light border" id="doc_raw_import_btn">
                                        <i class="fe fe-upload me-1"></i>استيراد من ملف (.md, .json, .txt)
                                    </button>
                                    <input type="file" id="doc_raw_file_input" accept=".md,.markdown,.json,.txt,text/plain,application/json" style="display:none;">
                                </div>
                                <textarea id="doc_raw_content" class="form-control" rows="14" placeholder="الصق هنا نصاً حراً غير منظم، أو Markdown، أو JSON — سيقوم الذكاء الاصطناعي بتنظيمه وتنسيقه كصفحة توثيق."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card doc-ai-panel doc-cat-table-card doc-ai-animate mb-4">
                        <div class="card-header doc-ai-panel__header border-0 d-flex flex-wrap align-items-start justify-content-between gap-2">
                            <div>
                                <h6 class="doc-ai-panel__title mb-1">
                                    <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--seo"><i class="fe fe-check-circle"></i></span>
                                    النتيجة بعد التحسين
                                    <span class="doc-ai-step-badge">3</span>
                                </h6>
                                <p class="doc-ai-hint mb-0">يُحفظ هذا المحتوى عند الضغط على «حفظ».</p>
                            </div>
                            <div class="doc-ai-editor-actions">
                                <button type="button" class="btn btn-sm btn-light border" id="btnReplaceSource" title="نسخ النتيجة إلى المصدر">
                                    <i class="fe fe-repeat me-1"></i>استبدال المصدر
                                </button>
                                <button type="button" class="btn btn-sm btn-light border" id="btnCopyResult">
                                    <i class="fe fe-copy me-1"></i>نسخ HTML
                                </button>
                            </div>
                        </div>
                        <div class="card-body pt-2">
                            <textarea name="content" id="doc_result" class="form-control @error('content') is-invalid @enderror" rows="14" placeholder="يظهر هنا المحتوى المحسّن بعد «تحسين المحتوى»">{{ old('content') }}</textarea>
                            @error('content')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="mt-3" id="excerptWrap" style="display: none;">
                                <label class="form-label" for="doc_excerpt_result">مقتطف مقترح (excerpt)</label>
                                <textarea id="doc_excerpt_result" class="form-control" rows="2" readonly></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-1">
                <div class="col-lg-8">
                    <div class="card custom-card doc-ai-panel doc-cat-table-card doc-ai-animate mb-4">
                        <div class="card-header doc-ai-panel__header border-0">
                            <h6 class="doc-ai-panel__title">
                                <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--content"><i class="fe fe-edit"></i></span>
                                بيانات الصفحة للحفظ
                            </h6>
                        </div>
                        <div class="card-body pt-2">
                            <div class="mb-3">
                                <label class="form-label" for="doc_title">العنوان <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="doc_title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $prefillPage->title ?? '') }}" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="doc_slug">الرابط (slug)</label>
                                <div class="input-group">
                                    <input type="text" name="slug" id="doc_slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $prefillPage?->slug ?? '') }}" placeholder="يُولَّد من العنوان إن وُجد فارغاً">
                                    <button type="button" class="btn btn-light border" id="doc_generate_slug">
                                        <i class="fe fe-refresh-cw me-1"></i>توليد
                                    </button>
                                </div>
                                @error('slug')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-0">
                                <label class="form-label" for="save_excerpt">المقتطف</label>
                                <textarea name="excerpt" id="save_excerpt" rows="2" class="form-control @error('excerpt') is-invalid @enderror">{{ old('excerpt', $prefillPage?->excerpt ?? '') }}</textarea>
                                @error('excerpt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card doc-ai-panel doc-cat-table-card doc-ai-animate mb-4">
                        <div class="card-header doc-ai-panel__header border-0">
                            <h6 class="doc-ai-panel__title">
                                <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--seo"><i class="fe fe-search"></i></span>
                                SEO
                            </h6>
                        </div>
                        <div class="card-body pt-2">
                            <div class="mb-3">
                                <label class="form-label">عنوان Meta</label>
                                <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $prefillPage?->meta_title ?? '') }}" maxlength="255">
                            </div>
                            <div class="mb-0">
                                <label class="form-label">وصف Meta</label>
                                <textarea name="meta_description" rows="2" class="form-control">{{ old('meta_description', $prefillPage?->meta_description ?? '') }}</textarea>
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
                                    <label class="form-label" for="doc_category_id">القسم <span class="text-danger">*</span></label>
                                    <select name="documentation_category_id" id="doc_category_id" class="form-select @error('documentation_category_id') is-invalid @enderror" required>
                                        <option value="">— اختر القسم —</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ (string) old('documentation_category_id', $categoryId) === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('documentation_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="doc_parent_id">صفحة أب (اختياري)</label>
                                    <select name="parent_id" id="doc_parent_id" class="form-select">
                                        <option value="">— بدون —</option>
                                    </select>
                                    <p class="doc-ai-hint mb-0">يجب أن تنتمي لنفس القسم عند الحفظ.</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الترتيب</label>
                                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $prefillPage?->sort_order ?? 0) }}" min="0">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الحالة <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="draft" {{ old('status', $prefillPage?->status ?? 'draft') === 'draft' ? 'selected' : '' }}>مسودة</option>
                                        <option value="published" {{ old('status', $prefillPage?->status ?? 'draft') === 'published' ? 'selected' : '' }}>منشور</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">تاريخ النشر</label>
                                    <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', $prefillPage?->published_at?->format('Y-m-d\TH:i') ?? '') }}">
                                    <p class="doc-ai-hint mb-0">يُعبَّأ تلقائياً عند «منشور» إن تُرك فارغاً.</p>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_indexable" value="1" id="is_indexable" {{ old('is_indexable', $prefillPage?->is_indexable ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_indexable">قابلة للفهرسة (SEO)</label>
                                </div>
                            </div>
                        </div>

                        <div class="card custom-card doc-ai-panel doc-ai-save-card doc-ai-animate">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary w-100 mb-2">
                                    <i class="fe fe-save me-1"></i>{{ $prefillPage ? 'تحديث الصفحة' : 'حفظ صفحة جديدة' }}
                                </button>
                                <a href="{{ route('admin.docs.pages.index') }}" class="btn btn-light border w-100">إلغاء</a>
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
@include('admin.docs.pages.partials.ai-job-poller')
@php($tinymceSelector = '#doc_source, #doc_result')
@include('admin.docs.partials.tinymce-doc')
<script>
document.documentElement.classList.add('loaded');
</script>
<script>
(function () {
    const useLaravelAiEngineDefault = @json(!empty($useLaravelAiEngine));
    const docsEngineChoiceAvailable = @json(!empty($docsEngineChoiceAvailable));
    const refineUrl = @json(route('admin.docs.ai-pages.refine'));
    const structureUrl = @json(route('admin.docs.ai-pages.structure'));
    const csrf = @json(csrf_token());
    const parentPages = @json($parentPagesJson);
    const initialParentId = @json(old('parent_id', $prefillPage?->parent_id));

    function syncDocsEngineModelVisibility() {
        if (!docsEngineChoiceAvailable) return;
        const laravelChecked = document.getElementById('docs_engine_laravel_ai')?.checked;
        const wL = document.getElementById('docs_engine_laravel_wrap');
        const wG = document.getElementById('docs_engine_legacy_wrap');
        if (wL) wL.style.display = laravelChecked ? '' : 'none';
        if (wG) wG.style.display = laravelChecked ? 'none' : '';
    }

    function getEditorHtml(id) {
        const ed = tinymce.get(id);
        return ed ? ed.getContent() : (document.getElementById(id) ? document.getElementById(id).value : '');
    }

    function setEditorHtml(id, html) {
        const ed = tinymce.get(id);
        if (ed) ed.setContent(html || '');
        else {
            const el = document.getElementById(id);
            if (el) el.value = html || '';
        }
    }

    function refreshParentOptions() {
        const catId = document.getElementById('doc_category_id').value;
        const sel = document.getElementById('doc_parent_id');
        const current = sel.value || (initialParentId != null ? String(initialParentId) : '');
        sel.innerHTML = '<option value="">— بدون —</option>';
        parentPages.filter(function (p) {
            return String(p.category_id) === String(catId);
        }).forEach(function (p) {
            const o = document.createElement('option');
            o.value = p.id;
            o.textContent = p.label;
            sel.appendChild(o);
        });
        if (current) {
            sel.value = current;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        refreshParentOptions();
        document.getElementById('doc_category_id').addEventListener('change', refreshParentOptions);
        syncDocsEngineModelVisibility();
        document.querySelectorAll('input[name="docs_engine"]').forEach(function (el) {
            el.addEventListener('change', syncDocsEngineModelVisibility);
        });
    });

    function currentInputMode() {
        const r = document.querySelector('input[name="doc_input_mode"]:checked');
        return r ? r.value : 'html';
    }

    function setInputMode(mode) {
        const isRaw = mode === 'raw';
        const sourceEd = (typeof tinymce !== 'undefined') ? tinymce.get('doc_source') : null;
        if (sourceEd) {
            sourceEd.getContainer().style.display = isRaw ? 'none' : '';
        } else {
            document.getElementById('doc_source').style.display = isRaw ? 'none' : '';
        }
        document.getElementById('doc_raw_wrap').style.display = isRaw ? '' : 'none';
        document.getElementById('doc_structure_length_wrap').style.display = isRaw ? '' : 'none';
        document.getElementById('doc_update_excerpt_wrap').style.display = isRaw ? 'none' : '';
        document.getElementById('doc_source_title').textContent = isRaw ? 'المحتوى الخام' : 'المصدر (HTML)';
        document.getElementById('doc_source_hint').textContent = isRaw
            ? 'نص حر غير منظم، أو Markdown، أو JSON — يُنظَّم وينسَّق كصفحة توثيق جديدة.'
            : 'المحتوى الكامل للصفحة — يُعاد صياغته دفعة واحدة.';
        const btnText = document.querySelector('#refineBtn .btn-text');
        if (btnText) btnText.textContent = isRaw ? 'تنظيم المحتوى وتنسيقه' : 'تحسين المحتوى';
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[name="doc_input_mode"]').forEach(function (el) {
            el.addEventListener('change', function () { setInputMode(this.value); });
        });
        setInputMode(currentInputMode());

        const importBtn = document.getElementById('doc_raw_import_btn');
        const fileInput = document.getElementById('doc_raw_file_input');
        if (importBtn && fileInput) {
            importBtn.addEventListener('click', function () { fileInput.click(); });
            fileInput.addEventListener('change', function () {
                const file = this.files && this.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('doc_raw_content').value = String(e.target.result || '');
                };
                reader.onerror = function () {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'خطأ', text: 'تعذرت قراءة الملف' });
                    } else {
                        alert('تعذرت قراءة الملف');
                    }
                };
                reader.readAsText(file, 'UTF-8');
                fileInput.value = '';
            });
        }
    });

    function slugify(t) {
        if (!t) return '';
        return t.toString().trim()
            .replace(/\s+/g, '-')
            .replace(/[^\u0600-\u06FFa-zA-Z0-9-]/g, '')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '') || ('page-' + Date.now());
    }

    document.addEventListener('DOMContentLoaded', function () {
        var titleEl = document.getElementById('doc_title');
        var slugEl = document.getElementById('doc_slug');
        var btn = document.getElementById('doc_generate_slug');
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

    document.getElementById('improveSaveForm').addEventListener('submit', function (e) {
        if (typeof tinymce !== 'undefined') {
            tinymce.triggerSave();
        }
        const ta = document.getElementById('doc_result');
        const raw = ta ? ta.value.trim() : '';
        if (!raw) {
            e.preventDefault();
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'أضف محتوى في منطقة «النتيجة بعد التحسين» قبل الحفظ (مثلاً بعد التحسين بالذكاء الاصطناعي).' });
            } else {
                alert('أضف محتوى في منطقة النتيجة قبل الحفظ');
            }
        }
    });

    document.getElementById('refineBtn').addEventListener('click', function () {
        const mode = currentInputMode();
        const isRaw = mode === 'raw';

        const sourceHtml = isRaw ? '' : getEditorHtml('doc_source');
        const rawContent = isRaw ? document.getElementById('doc_raw_content').value.trim() : '';
        if (!isRaw && (!sourceHtml || !sourceHtml.trim())) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'أدخل محتوى في منطقة المصدر أولاً' });
            } else {
                alert('أدخل محتوى في منطقة المصدر');
            }
            return;
        }
        if (isRaw && !rawContent) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'أدخل أو استورد محتوى خاماً أولاً' });
            } else {
                alert('أدخل أو استورد محتوى خاماً أولاً');
            }
            return;
        }

        const btn = this;
        const btnText = btn.querySelector('.btn-text');
        const spinner = btn.querySelector('.loading-spinner');
        const busyLabel = isRaw ? 'جاري التنظيم...' : 'جاري التحسين...';
        const idleLabel = isRaw ? 'تنظيم المحتوى وتنسيقه' : 'تحسين المحتوى';
        btn.disabled = true;
        btnText.textContent = busyLabel;
        spinner.classList.add('active');

        document.getElementById('excerptWrap').style.display = 'none';
        document.getElementById('doc_excerpt_result').value = '';

        let engine = useLaravelAiEngineDefault ? 'laravel_ai' : 'legacy';
        if (docsEngineChoiceAvailable) {
            const r = document.querySelector('input[name="docs_engine"]:checked');
            if (r) engine = r.value;
        }
        const laravelEl = document.getElementById('laravel_ai_model_id');
        const legacyEl = document.getElementById('ai_model_id');
        const url = isRaw ? structureUrl : refineUrl;
        const payload = isRaw ? {
            raw_content: rawContent,
            content_length: document.getElementById('content_length').value,
            user_notes: document.getElementById('user_notes').value.trim() || null,
            docs_engine: docsEngineChoiceAvailable ? engine : undefined,
            ai_model_id: engine === 'legacy' ? (legacyEl ? (legacyEl.value || null) : null) : null,
            laravel_ai_model_id: engine === 'laravel_ai' ? (laravelEl ? (laravelEl.value || null) : null) : null,
            tone: document.getElementById('tone').value,
            language: document.getElementById('language').value,
            documentation_category_id: document.getElementById('doc_category_id').value || null,
            parent_id: document.getElementById('doc_parent_id').value || null,
            _token: csrf
        } : {
            source_html: sourceHtml,
            user_notes: document.getElementById('user_notes').value.trim() || null,
            docs_engine: docsEngineChoiceAvailable ? engine : undefined,
            ai_model_id: engine === 'legacy' ? (legacyEl ? (legacyEl.value || null) : null) : null,
            laravel_ai_model_id: engine === 'laravel_ai' ? (laravelEl ? (laravelEl.value || null) : null) : null,
            tone: document.getElementById('tone').value,
            language: document.getElementById('language').value,
            update_excerpt: document.getElementById('update_excerpt').checked,
            _token: csrf
        };

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify(payload)
        })
        .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, status: r.status, body: j }; }); })
        .then(function (res) {
            function applyRefineData(data) {
                setEditorHtml('doc_result', data.content);
                if (data.excerpt) {
                    document.getElementById('doc_excerpt_result').value = data.excerpt;
                    document.getElementById('excerptWrap').style.display = 'block';
                    var se = document.getElementById('save_excerpt');
                    if (se && !se.value.trim()) {
                        se.value = data.excerpt;
                    }
                }
                // Only structure/generate results carry these — refine/enhance don't.
                // Fill them in when the admin hasn't typed anything yet.
                var titleEl = document.getElementById('doc_title');
                if (data.title && titleEl && !titleEl.value.trim()) {
                    titleEl.value = data.title;
                    titleEl.dispatchEvent(new Event('input'));
                }
                var slugEl = document.getElementById('doc_slug');
                if (data.slug && slugEl && !slugEl.value.trim()) {
                    slugEl.value = data.slug;
                }
                var metaTitleEl = document.querySelector('input[name="meta_title"]');
                if (data.meta_title && metaTitleEl && !metaTitleEl.value.trim()) {
                    metaTitleEl.value = data.meta_title;
                }
                var metaDescEl = document.querySelector('textarea[name="meta_description"]');
                if (data.meta_description && metaDescEl && !metaDescEl.value.trim()) {
                    metaDescEl.value = data.meta_description;
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'تم', text: 'راجع النتيجة ثم احفظ لتحديث التوثيق.', timer: 2500 });
                }
            }

            function resetRefineBtn() {
                btn.disabled = false;
                btnText.textContent = idleLabel;
                spinner.classList.remove('active');
                var wrap = document.getElementById('docAiProgressWrap');
                if (wrap) wrap.style.display = 'none';
            }

            function setProgress(job) {
                var wrap = document.getElementById('docAiProgressWrap');
                var bar = document.getElementById('docAiProgressBar');
                var label = document.getElementById('docAiProgressLabel');
                var pct = document.getElementById('docAiProgressPct');
                if (!wrap) return;
                wrap.style.display = '';
                var p = Math.max(0, Math.min(100, parseInt(job.progress || 0, 10)));
                if (bar) bar.style.width = p + '%';
                if (pct) pct.textContent = p + '%';
                if (label) label.textContent = job.stage_label || 'جاري التحسين…';
            }

            if (res.body.success && res.body.job && res.body.job.uuid) {
                window.DocAiJobPoller.poll({
                    uuid: res.body.job.uuid,
                    storageKey: isRaw ? 'docs_ai_structure_job_uuid' : 'docs_ai_refine_job_uuid',
                    onProgress: setProgress,
                    onComplete: function (result) {
                        applyRefineData(result || {});
                        resetRefineBtn();
                    },
                    onError: function (msg) {
                        resetRefineBtn();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'error', title: 'خطأ', text: msg || 'فشلت العملية' });
                        } else {
                            alert(msg || 'فشلت العملية');
                        }
                    }
                });
                return;
            }

            if (res.body.success && res.body.data && res.body.data.content !== undefined) {
                applyRefineData(res.body.data);
                resetRefineBtn();
            } else {
                resetRefineBtn();
                const msg = res.body.message || 'فشلت العملية';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'خطأ', text: msg });
                } else {
                    alert(msg);
                }
            }
        })
        .catch(function () {
            btn.disabled = false;
            btnText.textContent = idleLabel;
            spinner.classList.remove('active');
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'خطأ', text: 'تعذر بدء المهمة' });
            } else {
                alert('تعذر بدء المهمة');
            }
        });
    });

    document.getElementById('btnReplaceSource').addEventListener('click', function () {
        const html = getEditorHtml('doc_result');
        if (!html || !html.trim()) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'info', text: 'لا توجد نتيجة للاستبدال' });
            return;
        }
        setEditorHtml('doc_source', html);
    });

    document.getElementById('btnCopyResult').addEventListener('click', function () {
        const html = getEditorHtml('doc_result');
        if (!html) return;
        navigator.clipboard.writeText(html).then(function () {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'تم النسخ', timer: 1500, showConfirmButton: false });
        }).catch(function () {
            alert('تعذر النسخ');
        });
    });
})();
</script>
@endsection
