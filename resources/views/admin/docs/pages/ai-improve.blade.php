@extends('admin.layouts.master')

@section('page-title', 'فحص وتعديل التوثيق بالتعليمات (ذكاء اصطناعي)')

@section('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet" />
<style>
    .loading-spinner { display: none; }
    .loading-spinner.active { display: inline-block; }
</style>
@endsection

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">فحص المستند وتعديله بالذكاء الاصطناعي</h4>
                <p class="mb-0 text-muted">يُرسل النظام <strong>محتوى المصدر بالكامل</strong> إلى النموذج دفعة واحدة. اكتب <strong>تعليمات محددة</strong> لتطبيقها على كل الأقسام (حذف، إعادة هيكلة، توحيد المصطلحات، تقليل التكرار…) ثم اضغط «تحسين المحتوى» وراجع النتيجة قبل الحفظ.</p>
                @if(!empty($docsEngineChoiceAvailable))
                    <p class="mb-0 mt-1"><span class="badge bg-secondary">محركان</span> يمكنك اختيار <strong>Laravel AI SDK</strong> أو <strong>موديلات بنك الموديلات القديمة</strong> لكل عملية تحسين.</p>
                @elseif(!empty($useLaravelAiEngine))
                    <p class="mb-0 mt-1"><span class="badge bg-info text-dark">Laravel AI SDK</span> — الموديلات من لوحة «موديلات Laravel AI SDK»؛ يُفضّل اختيار موديل بقدرة docs.refine عند الافتراضي.</p>
                @endif
            </div>
            <div class="ms-auto d-flex flex-wrap gap-2">
                <a href="{{ route('admin.docs.pages.index') }}" class="btn btn-secondary">قائمة الصفحات</a>
                <a href="{{ route('admin.docs.ai-pages.create') }}" class="btn btn-outline-primary">توليد صفحة جديدة</a>
            </div>
        </div>

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($prefillPage)
        <div class="alert alert-info d-flex align-items-center justify-content-between flex-wrap gap-2">
            <span>تم تحميل محتوى الصفحة: <strong>{{ $prefillPage->title }}</strong></span>
            <a href="{{ route('admin.docs.pages.edit', $prefillPage) }}" class="btn btn-sm btn-outline-primary">فتح التحرير الكامل</a>
        </div>
        @endif

        <form id="improveSaveForm" method="POST" action="{{ $prefillPage ? route('admin.docs.pages.update', $prefillPage) : route('admin.docs.ai-pages.store') }}">
            @csrf
            @if($prefillPage)
            @method('PUT')
            @endif

            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card custom-card mb-4">
                        <div class="card-header bg-primary text-white">
                            <div class="card-title mb-0"><i class="fas fa-sliders-h me-2"></i>إعدادات التحسين</div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                @if(!empty($docsEngineChoiceAvailable))
                                    <label class="form-label">محرك التوثيق</label>
                                    <div class="mb-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="docs_engine" id="docs_engine_laravel_ai" value="laravel_ai" {{ !empty($useLaravelAiEngine) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="docs_engine_laravel_ai">Laravel AI SDK</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="docs_engine" id="docs_engine_legacy" value="legacy" {{ empty($useLaravelAiEngine) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="docs_engine_legacy">موديلات قديمة (بنك الموديلات)</label>
                                        </div>
                                    </div>
                                @endif
                                @if($models->isEmpty() && $laravelAiModels->isEmpty())
                                    <div class="alert alert-warning mb-0 small">لا يوجد موديل نشط في كلا النظامين.</div>
                                @else
                                    @if(!empty($docsEngineChoiceAvailable) || ($laravelAiModels->isNotEmpty() && $models->isEmpty()))
                                        <div id="docs_engine_laravel_wrap" class="docs-engine-model-wrap" style="{{ !empty($docsEngineChoiceAvailable) && empty($useLaravelAiEngine) ? 'display:none' : '' }}">
                                            <label class="form-label">موديل Laravel AI SDK</label>
                                            <select id="laravel_ai_model_id" class="form-select" @if($laravelAiModels->isEmpty()) disabled @endif>
                                                <option value="">افتراضي (أولوية + قدرة docs.refine)</option>
                                                @foreach($laravelAiModels as $lmodel)
                                                    <option value="{{ $lmodel->id }}">{{ $lmodel->name }} — {{ $lmodel->provider }}/{{ $lmodel->model }}</option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">إدارة الموديلات: موديلات Laravel AI SDK</small>
                                        </div>
                                    @endif
                                    @if(!empty($docsEngineChoiceAvailable) || ($models->isNotEmpty() && $laravelAiModels->isEmpty()))
                                        <div id="docs_engine_legacy_wrap" class="docs-engine-model-wrap" style="{{ !empty($docsEngineChoiceAvailable) && !empty($useLaravelAiEngine) ? 'display:none' : '' }}">
                                            <label class="form-label">موديل AI (بنك الموديلات)</label>
                                            <select id="ai_model_id" class="form-select" @if($models->isEmpty()) disabled @endif>
                                                <option value="">الافتراضي</option>
                                                @foreach($models as $model)
                                                    <option value="{{ $model->id }}">{{ $model->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label">الأسلوب</label>
                                <select id="tone" class="form-select">
                                    <option value="professional" selected>احترافي</option>
                                    <option value="friendly">ودود</option>
                                    <option value="technical">تقني</option>
                                    <option value="casual">عادي</option>
                                    <option value="formal">رسمي</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">اللغة</label>
                                <select id="language" class="form-select">
                                    <option value="ar" selected>العربية</option>
                                    <option value="en">English</option>
                                </select>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="update_excerpt">
                                <label class="form-check-label" for="update_excerpt">طلب مقتطف (excerpt) مقترح في الاستجابة</label>
                            </div>
                            <button type="button" class="btn btn-success w-100" id="refineBtn">
                                <span class="loading-spinner spinner-border spinner-border-sm me-1" role="status"></span>
                                <span class="btn-text">تحسين المحتوى</span>
                            </button>
                            <p class="text-muted small mt-2 mb-0">الحد الأقصى لطول المصدر: {{ number_format(\App\Services\Ai\AIDocumentationPageService::MAX_REFINE_SOURCE_CHARS) }} حرفاً.</p>
                        </div>
                    </div>

                    <div class="card custom-card">
                        <div class="card-header"><div class="card-title">التعليمات المحددة (فحص المستند كاملاً)</div></div>
                        <div class="card-body">
                            <label class="form-label">توجيهات للنموذج على كامل المحتوى</label>
                            <p class="text-muted small mb-2">اترك الحقل فارغاً إن أردت تحسيناً عاماً فقط. عند الكتابة، صف ما يجب تغييره أو حذفه أو إضافته في <strong>كل</strong> الصفحة.</p>
                            <textarea id="user_notes" class="form-control" rows="10" placeholder="أمثلة: احذف القسم الفلاني بالكامل؛ وحّد مصطلح «المتغير» في كل الفقرات؛ أضف جدول مقارنة بعد المقدمة؛ اختصر الملحقات؛ صحح العناوين لتتبع تسلسلاً منطقياً؛ أزل الجمل المكررة بين الأقسام…"></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card custom-card mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <div class="card-title mb-0">المصدر (HTML)</div>
                                <small class="text-muted">المحتوى الكامل للصفحة؛ يُعاد صياغته دفعة واحدة وفق التعليمات أعلاه.</small>
                            </div>
                        </div>
                        <div class="card-body">
                            <textarea id="doc_source" class="form-control" rows="14">{{ old('source', $prefillPage?->content ?? '') }}</textarea>
                        </div>
                    </div>

                    <div class="card custom-card mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="card-title mb-0">النتيجة بعد التحسين (يُحفظ هذا المحتوى عند الضغط على حفظ)</div>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnReplaceSource" title="نسخ النتيجة إلى المصدر">استبدال المصدر بالنتيجة</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCopyResult">نسخ HTML</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <textarea name="content" id="doc_result" class="form-control @error('content') is-invalid @enderror" rows="14" placeholder="يظهر هنا المحتوى المحسّن بعد الضغط على «تحسين المحتوى»">{{ old('content') }}</textarea>
                            @error('content')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="mt-3" id="excerptWrap" style="display: none;">
                                <label class="form-label">مقتطف مقترح (excerpt)</label>
                                <textarea id="doc_excerpt_result" class="form-control" rows="2" readonly></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card custom-card mb-4">
                        <div class="card-header"><div class="card-title">بيانات الصفحة للحفظ</div></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">العنوان <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="doc_title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $prefillPage->title ?? '') }}" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">الرابط (slug)</label>
                                <div class="input-group">
                                    <input type="text" name="slug" id="doc_slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $prefillPage?->slug ?? '') }}" placeholder="يُولَّد من العنوان إن وُجد فارغاً">
                                    <button type="button" class="btn btn-outline-secondary" id="doc_generate_slug">توليد</button>
                                </div>
                                @error('slug')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">المقتطف</label>
                                <textarea name="excerpt" id="save_excerpt" rows="2" class="form-control @error('excerpt') is-invalid @enderror">{{ old('excerpt', $prefillPage?->excerpt ?? '') }}</textarea>
                                @error('excerpt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card mb-4">
                        <div class="card-header"><div class="card-title">SEO</div></div>
                        <div class="card-body">
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

                <div class="col-lg-4 mb-4">
                    <div class="card custom-card mb-4">
                        <div class="card-header"><div class="card-title">التصنيف والهيكل</div></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">القسم <span class="text-danger">*</span></label>
                                <select name="documentation_category_id" id="doc_category_id" class="form-select @error('documentation_category_id') is-invalid @enderror" required>
                                    <option value="">— اختر —</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ (string) old('documentation_category_id', $categoryId) === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('documentation_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">صفحة أب (اختياري)</label>
                                <select name="parent_id" id="doc_parent_id" class="form-select">
                                    <option value="">— بدون —</option>
                                </select>
                                <small class="text-muted">يجب أن تنتمي لنفس القسم عند الحفظ</small>
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
                                <small class="text-muted">عند اختيار «منشور» يُعبَّأ تلقائياً إن تُرك فارغاً</small>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_indexable" value="1" id="is_indexable" {{ old('is_indexable', $prefillPage?->is_indexable ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_indexable">قابلة للفهرسة (SEO)</label>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary w-100 mb-2"><i class="bi bi-save me-2"></i>{{ $prefillPage ? 'تحديث الصفحة' : 'حفظ صفحة جديدة' }}</button>
                            <a href="{{ route('admin.docs.pages.index') }}" class="btn btn-secondary w-100">إلغاء</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@php($tinymceSelector = '#doc_source, #doc_result')
@include('admin.docs.partials.tinymce-doc')
<script>
(function () {
    const useLaravelAiEngineDefault = @json(!empty($useLaravelAiEngine));
    const docsEngineChoiceAvailable = @json(!empty($docsEngineChoiceAvailable));
    const refineUrl = @json(route('admin.docs.ai-pages.refine'));
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
        const sourceHtml = getEditorHtml('doc_source');
        if (!sourceHtml || !sourceHtml.trim()) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'تنبيه', text: 'أدخل محتوى في منطقة المصدر أولاً' });
            } else {
                alert('أدخل محتوى في منطقة المصدر');
            }
            return;
        }

        const btn = this;
        const btnText = btn.querySelector('.btn-text');
        const spinner = btn.querySelector('.loading-spinner');
        btn.disabled = true;
        btnText.textContent = 'جاري التحسين...';
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
        const payload = {
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

        fetch(refineUrl, {
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
            if (res.body.success && res.body.data && res.body.data.content !== undefined) {
                setEditorHtml('doc_result', res.body.data.content);
                if (res.body.data.excerpt) {
                    document.getElementById('doc_excerpt_result').value = res.body.data.excerpt;
                    document.getElementById('excerptWrap').style.display = 'block';
                    var se = document.getElementById('save_excerpt');
                    if (se && !se.value.trim()) {
                        se.value = res.body.data.excerpt;
                    }
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'تم', text: 'راجع النتيجة ثم احفظ لتحديث التوثيق.', timer: 2500 });
                }
            } else {
                const msg = res.body.message || 'فشل التحسين';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'خطأ', text: msg });
                } else {
                    alert(msg);
                }
            }
        })
        .catch(function () {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'خطأ', text: 'فشل الاتصال بالخادم' });
            } else {
                alert('فشل الاتصال');
            }
        })
        .finally(function () {
            btn.disabled = false;
            btnText.textContent = 'تحسين المحتوى';
            spinner.classList.remove('active');
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
