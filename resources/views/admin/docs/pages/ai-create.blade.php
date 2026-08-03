@extends('admin.layouts.master')

@section('page-title', 'توليد صفحة توثيق بالذكاء الاصطناعي')

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
                    <li class="breadcrumb-item active">توليد بالذكاء الاصطناعي</li>
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
                    <h2 class="group-show-hero__title mb-2">توليد صفحة توثيق بالذكاء الاصطناعي</h2>
                    <p class="group-show-hero__desc mb-2">
                        اكتب الموضوع، اختر القسم والإعدادات، ثم ولّد المحتوى وراجعه قبل الحفظ — بنفس تنسيق صفحات التوثيق العامة.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        @if(!empty($docsEngineChoiceAvailable))
                            <span class="doc-ai-badge"><i class="fe fe-layers"></i>محركان متاحان</span>
                        @elseif(!empty($useLaravelAiEngine))
                            <span class="doc-ai-badge"><i class="fe fe-cpu"></i>Laravel AI SDK</span>
                        @else
                            <span class="doc-ai-badge"><i class="fe fe-database"></i>بنك الموديلات</span>
                        @endif
                        <span class="doc-ai-badge"><i class="fe fe-file-text"></i>HTML جاهز للتوثيق</span>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('admin.docs.pages.index') }}" class="btn btn-light border">
                            <i class="fe fe-list me-1"></i>قائمة الصفحات
                        </a>
                        <a href="{{ route('admin.docs.pages.create') }}" class="btn btn-outline-primary">
                            <i class="fe fe-edit me-1"></i>إضافة يدوية
                        </a>
                        <a href="{{ route('admin.docs.ai-pages.improve') }}" class="btn btn-outline-secondary">
                            <i class="fe fe-tool me-1"></i>فحص وتعديل
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.docs.ai-pages.store') }}" method="POST" id="aiDocPageForm">
            @csrf

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card custom-card doc-ai-panel doc-cat-filter-card doc-ai-animate mb-4">
                        <div class="card-header doc-ai-panel__header border-0">
                            <h6 class="doc-ai-panel__title">
                                <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--ai"><i class="fe fe-zap"></i></span>
                                إعدادات التوليد
                            </h6>
                        </div>
                        <div class="card-body pt-2">
                            <div class="mb-4">
                                <label class="form-label" for="topic">الموضوع أو ما تريد شرحه <span class="text-danger">*</span></label>
                                <input type="text" id="topic" class="form-control doc-ai-topic-input" placeholder="مثال: التوجيه في Laravel، أنواع البيانات في PHP، النماذج في HTML">
                                <p class="doc-ai-hint mb-0">يُستخدم للتوليد فقط — لا يُحفظ مع الصفحة.</p>
                            </div>

                            @if(!empty($docsEngineChoiceAvailable))
                                <div class="mb-4">
                                    <label class="form-label d-block">محرك التوثيق</label>
                                    <div class="doc-ai-engine-pills">
                                        <div class="doc-ai-engine-pill">
                                            <input class="form-check-input" type="radio" name="docs_engine" id="docs_engine_laravel_ai" value="laravel_ai" {{ !empty($useLaravelAiEngine) ? 'checked' : '' }}>
                                            <label for="docs_engine_laravel_ai">
                                                <i class="fe fe-cpu"></i>
                                                Laravel AI SDK
                                            </label>
                                        </div>
                                        <div class="doc-ai-engine-pill">
                                            <input class="form-check-input" type="radio" name="docs_engine" id="docs_engine_legacy" value="legacy" {{ empty($useLaravelAiEngine) ? 'checked' : '' }}>
                                            <label for="docs_engine_legacy">
                                                <i class="fe fe-database"></i>
                                                موديلات قديمة
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($models->isEmpty() && $laravelAiModels->isEmpty())
                                <div class="alert alert-warning border-0 mb-4">
                                    <i class="fe fe-alert-triangle me-1"></i>
                                    لا يوجد موديل نشط. أضف موديلاً من «إدارة موديلات AI» أو «موديلات Laravel AI SDK».
                                </div>
                            @else
                                <div class="row g-3 mb-4">
                                    @if(!empty($docsEngineChoiceAvailable) || ($laravelAiModels->isNotEmpty() && $models->isEmpty()))
                                        <div class="col-md-6">
                                            <div id="docs_engine_laravel_wrap" class="docs-engine-model-wrap" style="{{ !empty($docsEngineChoiceAvailable) && empty($useLaravelAiEngine) ? 'display:none' : '' }}">
                                                <label class="form-label" for="laravel_ai_model_id">موديل Laravel AI SDK</label>
                                                <select id="laravel_ai_model_id" class="form-select" @if($laravelAiModels->isEmpty()) disabled @endif>
                                                    <option value="">افتراضي (أولوية + docs.refine)</option>
                                                    @foreach($laravelAiModels as $lmodel)
                                                        <option value="{{ $lmodel->id }}">{{ $lmodel->name }} — {{ $lmodel->provider }}/{{ $lmodel->model }}</option>
                                                    @endforeach
                                                </select>
                                                <p class="doc-ai-hint mb-0">من لوحة «موديلات Laravel AI SDK»</p>
                                            </div>
                                        </div>
                                    @endif
                                    @if(!empty($docsEngineChoiceAvailable) || ($models->isNotEmpty() && $laravelAiModels->isEmpty()))
                                        <div class="col-md-6">
                                            <div id="docs_engine_legacy_wrap" class="docs-engine-model-wrap" style="{{ !empty($docsEngineChoiceAvailable) && !empty($useLaravelAiEngine) ? 'display:none' : '' }}">
                                                <label class="form-label" for="ai_model_id">موديل AI (بنك الموديلات)</label>
                                                <select id="ai_model_id" class="form-select" @if($models->isEmpty()) disabled @endif>
                                                    <option value="">الافتراضي</option>
                                                    @foreach($models as $model)
                                                        <option value="{{ $model->id }}">{{ $model->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-md-4">
                                        <label class="form-label" for="content_length">طول المحتوى</label>
                                        <select id="content_length" class="form-select">
                                            <option value="short">قصير</option>
                                            <option value="medium" selected>متوسط</option>
                                            <option value="long">طويل</option>
                                        </select>
                                        <small class="text-muted d-block mt-1">الطويل/المتوسط يُقسَّم تلقائياً إلى أقسام ويستخدم حدّ <code>max_tokens</code> من موديل Laravel AI SDK.</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="tone">الأسلوب</label>
                                        <select id="tone" class="form-select">
                                            <option value="professional">احترافي</option>
                                            <option value="friendly">ودود</option>
                                            <option value="technical" selected>تقني</option>
                                            <option value="casual">عادي</option>
                                            <option value="formal">رسمي</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="language">اللغة</label>
                                        <select id="language" class="form-select">
                                            <option value="ar" selected>العربية</option>
                                            <option value="en">English</option>
                                        </select>
                                    </div>
                                </div>
                            @endif

                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="generate_meta" checked>
                                <label class="form-check-label" for="generate_meta">توليد meta_title و meta_description تلقائياً</label>
                            </div>

                            <div class="doc-ai-generate-bar">
                                <p class="doc-ai-hint mb-0">
                                    <i class="fe fe-info me-1"></i>
                                    اختر القسم من الشريط الجانبي قبل التوليد. المحتوى الطويل يُولَّد على مراحل مع شريط تقدم.
                                </p>
                                <button type="button" class="doc-ai-generate-btn" id="generateBtn">
                                    <span class="loading-spinner spinner-border spinner-border-sm" role="status"></span>
                                    <i class="fe fe-zap"></i>
                                    <span class="btn-text">توليد المحتوى</span>
                                </button>
                            </div>
                            <div id="docAiProgressWrap" class="mt-3" style="display:none;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted" id="docAiProgressLabel">جاري التوليد…</small>
                                    <small class="fw-semibold" id="docAiProgressPct">0%</small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="docAiProgressBar" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card doc-ai-panel doc-cat-table-card doc-ai-animate mb-4">
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
                                    <input type="text" name="slug" id="doc_slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}">
                                    <button type="button" class="btn btn-light border" id="doc_generate_slug">
                                        <i class="fe fe-refresh-cw me-1"></i>توليد
                                    </button>
                                </div>
                                @error('slug')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="doc_excerpt">المقتطف</label>
                                <textarea name="excerpt" id="doc_excerpt" rows="2" class="form-control">{{ old('excerpt') }}</textarea>
                            </div>
                            <div class="mb-0">
                                <label class="form-label" for="doc_content">المحتوى <span class="text-danger">*</span></label>
                                <textarea name="content" id="doc_content" class="form-control @error('content') is-invalid @enderror" rows="12">{{ old('content') }}</textarea>
                                @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الترتيب</label>
                                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">الحالة <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>مسودة</option>
                                        <option value="published" {{ old('status', 'published') === 'published' ? 'selected' : '' }}>منشور</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">تاريخ النشر</label>
                                    <input type="datetime-local" name="published_at" class="form-control" value="{{ old('published_at', $defaultPublishedAt ?? now()->format('Y-m-d\TH:i')) }}">
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_indexable" value="1" id="is_indexable" {{ old('is_indexable', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_indexable">قابلة للفهرسة</label>
                                </div>
                            </div>
                        </div>

                        <div class="card custom-card doc-ai-panel doc-ai-save-card doc-ai-animate">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary w-100 mb-2">
                                    <i class="fe fe-save me-1"></i>حفظ الصفحة
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
@include('admin.docs.partials.tinymce-doc')
@include('admin.docs.pages.partials.ai-job-poller')
<script>
document.documentElement.classList.add('loaded');
</script>
<script>
(function () {
    const useLaravelAiEngineDefault = @json(!empty($useLaravelAiEngine));
    const docsEngineChoiceAvailable = @json(!empty($docsEngineChoiceAvailable));
    const parentPages = @json($parentPagesJson);
    const STORAGE_KEY = 'docs_ai_generate_job_uuid';
    let activePoller = null;

    function setProgress(job) {
        const wrap = document.getElementById('docAiProgressWrap');
        const bar = document.getElementById('docAiProgressBar');
        const label = document.getElementById('docAiProgressLabel');
        const pct = document.getElementById('docAiProgressPct');
        if (!wrap) return;
        wrap.style.display = '';
        const p = Math.max(0, Math.min(100, parseInt(job.progress || 0, 10)));
        if (bar) bar.style.width = p + '%';
        if (pct) pct.textContent = p + '%';
        if (label) {
            let text = job.stage_label || 'جاري التوليد…';
            if (job.queue_hint && job.status === 'queued') {
                text = 'في الطابور — تأكد أن عامل الطابور يعمل (php artisan queue:work)';
            }
            label.textContent = text;
        }
    }

    function hideProgress() {
        const wrap = document.getElementById('docAiProgressWrap');
        if (wrap) wrap.style.display = 'none';
    }

    function applyGenerateResult(d) {
        if (d.title) document.getElementById('doc_title').value = d.title;
        if (d.slug) {
            document.getElementById('doc_slug').value = d.slug;
            document.getElementById('doc_slug').dataset.touched = '1';
        }
        if (d.excerpt) document.getElementById('doc_excerpt').value = d.excerpt;
        if (d.meta_title) document.getElementById('meta_title').value = d.meta_title;
        if (d.meta_description) document.getElementById('meta_description').value = d.meta_description;
        if (d.content) {
            const ed = tinymce.get('doc_content');
            if (ed) ed.setContent(d.content);
            else document.getElementById('doc_content').value = d.content;
        }
    }

    function resetGenerateBtn() {
        const btn = document.getElementById('generateBtn');
        if (!btn) return;
        btn.disabled = false;
        const btnText = btn.querySelector('.btn-text');
        const spinner = btn.querySelector('.loading-spinner');
        if (btnText) btnText.textContent = 'توليد المحتوى';
        if (spinner) spinner.classList.remove('active');
    }

    function startPolling(uuid) {
        if (activePoller) activePoller.stop();
        const btn = document.getElementById('generateBtn');
        if (btn) btn.disabled = true;
        activePoller = window.DocAiJobPoller.poll({
            uuid: uuid,
            storageKey: STORAGE_KEY,
            onProgress: setProgress,
            onComplete: function (result) {
                applyGenerateResult(result || {});
                hideProgress();
                resetGenerateBtn();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'تم التوليد', text: 'راجع المحتوى ثم احفظ.', timer: 2800 });
                }
            },
            onError: function (msg) {
                hideProgress();
                resetGenerateBtn();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'خطأ', text: msg || 'فشل التوليد' });
                } else {
                    alert(msg || 'فشل التوليد');
                }
            }
        });
    }

    function syncDocsEngineModelVisibility() {
        if (!docsEngineChoiceAvailable) return;
        const laravelChecked = document.getElementById('docs_engine_laravel_ai')?.checked;
        const wL = document.getElementById('docs_engine_laravel_wrap');
        const wG = document.getElementById('docs_engine_legacy_wrap');
        if (wL) wL.style.display = laravelChecked ? '' : 'none';
        if (wG) wG.style.display = laravelChecked ? 'none' : '';
    }

    function ensureLaravelAiForLongContent() {
        const lengthEl = document.getElementById('content_length');
        const laravelEl = document.getElementById('laravel_ai_model_id');
        if (!lengthEl || !laravelEl) return;
        const length = lengthEl.value;
        if (length !== 'medium' && length !== 'long') return;
        const laravelRadio = document.getElementById('docs_engine_laravel_ai');
        if (laravelRadio && !laravelRadio.checked) {
            laravelRadio.checked = true;
            syncDocsEngineModelVisibility();
        }
    }

    function refreshParentOptions() {
        const catId = document.getElementById('doc_category_id').value;
        const sel = document.getElementById('doc_parent_id');
        const current = sel.value;
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
        ensureLaravelAiForLongContent();
        document.querySelectorAll('input[name="docs_engine"]').forEach(function (el) {
            el.addEventListener('change', syncDocsEngineModelVisibility);
        });
        document.getElementById('content_length')?.addEventListener('change', ensureLaravelAiForLongContent);

        // Resume unfinished job after leave/return
        window.DocAiJobPoller.resumeIfAny(STORAGE_KEY, {
            onProgress: function (job) {
                setProgress(job);
                const btn = document.getElementById('generateBtn');
                if (btn) {
                    btn.disabled = true;
                    const btnText = btn.querySelector('.btn-text');
                    const spinner = btn.querySelector('.loading-spinner');
                    if (btnText) btnText.textContent = 'جاري التوليد...';
                    if (spinner) spinner.classList.add('active');
                }
            },
            onComplete: function (result) {
                applyGenerateResult(result || {});
                hideProgress();
                resetGenerateBtn();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'تم التوليد', text: 'اكتملت المهمة أثناء غيابك — راجع المحتوى.', timer: 3200 });
                }
            },
            onError: function (msg) {
                hideProgress();
                resetGenerateBtn();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'خطأ', text: msg || 'فشل التوليد' });
                }
            }
        });

        // slug handled in separate DOMContentLoaded below

        document.getElementById('generateBtn').addEventListener('click', function () {
            const topic = document.getElementById('topic').value.trim();
            const cat = document.getElementById('doc_category_id').value;
            if (!topic) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'تنبيه', text: 'يرجى إدخال الموضوع' });
                } else {
                    alert('يرجى إدخال الموضوع');
                }
                return;
            }
            if (!cat) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'تنبيه', text: 'يرجى اختيار قسم التوثيق أولاً' });
                } else {
                    alert('يرجى اختيار قسم التوثيق');
                }
                return;
            }

            const btn = this;
            const btnText = btn.querySelector('.btn-text');
            const spinner = btn.querySelector('.loading-spinner');
            btn.disabled = true;
            btnText.textContent = 'جاري التوليد...';
            spinner.classList.add('active');
            setProgress({ progress: 1, stage_label: 'بدء المهمة…', status: 'queued' });

            let engine = useLaravelAiEngineDefault ? 'laravel_ai' : 'legacy';
            if (docsEngineChoiceAvailable) {
                const r = document.querySelector('input[name="docs_engine"]:checked');
                if (r) engine = r.value;
            }
            const contentLength = document.getElementById('content_length').value;
            const laravelEl = document.getElementById('laravel_ai_model_id');
            const legacyEl = document.getElementById('ai_model_id');
            // Medium/long must use Laravel AI pipeline (and its model max_tokens).
            if ((contentLength === 'medium' || contentLength === 'long') && laravelEl) {
                engine = 'laravel_ai';
                ensureLaravelAiForLongContent();
            }
            const payload = {
                topic: topic,
                docs_engine: engine,
                ai_model_id: engine === 'legacy' ? (legacyEl ? (legacyEl.value || null) : null) : null,
                laravel_ai_model_id: engine === 'laravel_ai' ? (laravelEl ? (laravelEl.value || null) : null) : null,
                content_length: contentLength,
                tone: document.getElementById('tone').value,
                language: document.getElementById('language').value,
                documentation_category_id: cat,
                parent_id: document.getElementById('doc_parent_id').value || null,
                generate_meta: document.getElementById('generate_meta').checked,
                _token: '{{ csrf_token() }}'
            };

            fetch(@json(route('admin.docs.ai-pages.generate')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
            .then(function (res) {
                if (res.body.success && res.body.job && res.body.job.uuid) {
                    startPolling(res.body.job.uuid);
                    return;
                }
                // Legacy sync shape (should not happen)
                if (res.body.success && res.body.data) {
                    applyGenerateResult(res.body.data);
                    hideProgress();
                    resetGenerateBtn();
                    return;
                }
                hideProgress();
                resetGenerateBtn();
                const msg = res.body.message || 'فشل التوليد';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'خطأ', text: msg });
                } else {
                    alert(msg);
                }
            })
            .catch(function () {
                hideProgress();
                resetGenerateBtn();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'خطأ', text: 'تعذر بدء المهمة — تحقق من الاتصال ثم أعد المحاولة' });
                } else {
                    alert('تعذر بدء المهمة');
                }
            });
        });
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
        var slugBtn = document.getElementById('doc_generate_slug');
        if (titleEl && slugEl) {
            titleEl.addEventListener('input', function () {
                if (!slugEl.dataset.touched) slugEl.value = slugify(this.value);
            });
            slugEl.addEventListener('input', function () { this.dataset.touched = '1'; });
        }
        if (slugBtn && titleEl && slugEl) {
            slugBtn.addEventListener('click', function () {
                slugEl.value = slugify(titleEl.value);
                delete slugEl.dataset.touched;
            });
        }
    });
})();
</script>
@endsection
