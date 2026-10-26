@extends('admin.layouts.master')

@section('page-title', 'اقتراح عناوين توثيق بالذكاء الاصطناعي')

@section('styles')
@include('admin.docs.categories.partials.styles')
@include('admin.docs.pages.partials.ai-page-styles')
@include('admin.docs.pages.partials.ai-batch-styles')
<style>
    .doc-title-suggest-list { display: flex; flex-direction: column; gap: .5rem; }
    .doc-title-suggest-item { display: flex; align-items: center; gap: .5rem; }
    .doc-title-suggest-item__num { min-width: 2rem; text-align: center; opacity: .6; font-size: .85rem; }
    .doc-title-suggest-item .form-control { flex: 1; }
    .doc-title-suggest-item.is-off .form-control { opacity: .5; }
</style>
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
                    <li class="breadcrumb-item active">اقتراح عناوين</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in doc-ai-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-list me-1"></i>
                        مساعد التوثيق
                    </span>
                    <h2 class="group-show-hero__title mb-2">اقتراح عناوين توثيق</h2>
                    <p class="group-show-hero__desc mb-2">
                        اكتب العنوان الرئيسي، وسيقترح الذكاء الاصطناعي قائمة عناوين فرعية مرتبة. اختر منها وعدّلها، ثم أرسلها إلى صفحة الدفعة لتوليد توثيقاتها.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('admin.docs.ai-pages.batch.create') }}" class="btn btn-light border">
                            <i class="fe fe-layers me-1"></i>توليد دفعة مواضيع
                        </a>
                        <a href="{{ route('admin.docs.pages.index') }}" class="btn btn-light border">
                            <i class="fe fe-list me-1"></i>قائمة الصفحات
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card custom-card doc-ai-panel doc-cat-filter-card doc-ai-animate mb-4">
                    <div class="card-header doc-ai-panel__header border-0">
                        <h6 class="doc-ai-panel__title">
                            <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--ai"><i class="fe fe-zap"></i></span>
                            العنوان الرئيسي
                        </h6>
                    </div>
                    <div class="card-body pt-2">
                        <div class="mb-3">
                            <label class="form-label" for="suggest_main_topic">العنوان الرئيسي <span class="text-danger">*</span></label>
                            <input type="text" id="suggest_main_topic" class="form-control" maxlength="500" placeholder="مثال: Laravel Eloquent ORM">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label" for="suggest_count">عدد العناوين</label>
                                <input type="number" id="suggest_count" class="form-control" value="20" min="5" max="100">
                                <div class="form-check mt-1 mb-0">
                                    <input class="form-check-input" type="checkbox" id="suggest_auto_count" checked>
                                    <label class="form-check-label" for="suggest_auto_count">تلقائي حسب الموضوع</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="suggest_level">المستوى</label>
                                <select id="suggest_level" class="form-select">
                                    <option value="all" selected>كل المستويات</option>
                                    <option value="beginner">مبتدئ</option>
                                    <option value="intermediate">متوسط</option>
                                    <option value="advanced">متقدم</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="suggest_language">اللغة</label>
                                <select id="suggest_language" class="form-select">
                                    <option value="ar" selected>العربية</option>
                                    <option value="en">English</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="suggest_category_id">القسم (اختياري)</label>
                            <select id="suggest_category_id" class="form-select">
                                <option value="">— بدون —</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ (string) $categoryId === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <p class="doc-ai-hint mb-0">يُستخدم كسياق للاقتراح ويُختار تلقائياً في صفحة الدفعة.</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="suggest_notes">ملاحظات إضافية (اختياري)</label>
                            <textarea id="suggest_notes" class="form-control" rows="3" maxlength="2000" placeholder="مثال: ركّز على العلاقات والاستعلامات، وتجاهل التثبيت"></textarea>
                        </div>

                        @if(!empty($docsEngineChoiceAvailable))
                            <div class="mb-3">
                                <label class="form-label d-block">محرك الذكاء الاصطناعي</label>
                                <div class="doc-ai-engine-pills">
                                    <div class="doc-ai-engine-pill">
                                        <input class="form-check-input" type="radio" name="suggest_docs_engine" id="suggest_docs_engine_laravel_ai" value="laravel_ai" {{ !empty($useLaravelAiEngine) ? 'checked' : '' }}>
                                        <label for="suggest_docs_engine_laravel_ai">
                                            <i class="fe fe-cpu"></i>
                                            Laravel AI SDK
                                        </label>
                                    </div>
                                    <div class="doc-ai-engine-pill">
                                        <input class="form-check-input" type="radio" name="suggest_docs_engine" id="suggest_docs_engine_legacy" value="legacy" {{ empty($useLaravelAiEngine) ? 'checked' : '' }}>
                                        <label for="suggest_docs_engine_legacy">
                                            <i class="fe fe-database"></i>
                                            موديلات قديمة
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($models->isEmpty() && $laravelAiModels->isEmpty())
                            <div class="alert alert-warning border-0 mb-3">
                                <i class="fe fe-alert-triangle me-1"></i>
                                لا يوجد موديل نشط. أضف موديلاً من «إدارة موديلات AI» أو «موديلات Laravel AI SDK».
                            </div>
                        @else
                            @if(!empty($docsEngineChoiceAvailable) || ($laravelAiModels->isNotEmpty() && $models->isEmpty()))
                                <div id="suggest_docs_engine_laravel_wrap" class="docs-engine-model-wrap mb-3" style="{{ !empty($docsEngineChoiceAvailable) && empty($useLaravelAiEngine) ? 'display:none' : '' }}">
                                    <label class="form-label" for="suggest_laravel_ai_model_id">موديل Laravel AI SDK</label>
                                    <select id="suggest_laravel_ai_model_id" class="form-select" @if($laravelAiModels->isEmpty()) disabled @endif>
                                        <option value="">افتراضي</option>
                                        @foreach($laravelAiModels as $lmodel)
                                            <option value="{{ $lmodel->id }}">{{ $lmodel->name }} — {{ $lmodel->provider }}/{{ $lmodel->model }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            @if(!empty($docsEngineChoiceAvailable) || ($models->isNotEmpty() && $laravelAiModels->isEmpty()))
                                <div id="suggest_docs_engine_legacy_wrap" class="docs-engine-model-wrap mb-3" style="{{ !empty($docsEngineChoiceAvailable) && !empty($useLaravelAiEngine) ? 'display:none' : '' }}">
                                    <label class="form-label" for="suggest_ai_model_id">موديل AI (بنك الموديلات)</label>
                                    <select id="suggest_ai_model_id" class="form-select" @if($models->isEmpty()) disabled @endif>
                                        <option value="">الافتراضي</option>
                                        @foreach($models as $model)
                                            <option value="{{ $model->id }}">{{ $model->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        @endif

                        <button type="button" class="btn btn-primary w-100" id="suggestBtn">
                            <span class="loading-spinner spinner-border spinner-border-sm" role="status" style="display:none;"></span>
                            <i class="fe fe-zap me-1"></i>
                            <span class="btn-text">اقتراح العناوين</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card custom-card doc-ai-panel doc-cat-filter-card doc-ai-animate mb-4">
                    <div class="card-header doc-ai-panel__header border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h6 class="doc-ai-panel__title mb-0">
                            <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--content"><i class="fe fe-list"></i></span>
                            العناوين المقترحة
                            <span class="badge bg-primary-transparent ms-1"><span id="suggestSelectedCount">0</span> / <span id="suggestTotalCount">0</span></span>
                        </h6>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-light border" id="suggestToggleAll">
                                <i class="fe fe-check-square me-1"></i>تحديد / إلغاء الكل
                            </button>
                            <button type="button" class="btn btn-sm btn-light border" id="suggestAddManual">
                                <i class="fe fe-plus me-1"></i>عنوان يدوي
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="suggestMoreBtn" disabled>
                                <span class="loading-spinner spinner-border spinner-border-sm" role="status" style="display:none;"></span>
                                <i class="fe fe-refresh-cw me-1"></i>اقتراح المزيد
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        <p class="doc-ai-hint mb-3" id="suggestEmpty">لم تُقترح عناوين بعد — اكتب العنوان الرئيسي واضغط «اقتراح العناوين».</p>
                        <div class="doc-title-suggest-list" id="suggestList"></div>
                    </div>
                </div>

                <div class="card custom-card doc-ai-panel doc-cat-filter-card doc-ai-animate mb-4">
                    <div class="card-header doc-ai-panel__header border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h6 class="doc-ai-panel__title mb-0">
                            <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--meta"><i class="fe fe-edit-3"></i></span>
                            تعليمات تُضاف لكل موضوع
                        </h6>
                        <div class="d-flex align-items-center gap-2">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="suggest_suffix_enabled" checked>
                                <label class="form-check-label" for="suggest_suffix_enabled">مفعّل</label>
                            </div>
                            <button type="button" class="btn btn-sm btn-light border" id="suggestSuffixReset">
                                <i class="fe fe-rotate-ccw me-1"></i>النص الافتراضي
                            </button>
                        </div>
                    </div>
                    <div class="card-body pt-2">
                        <textarea id="suggest_suffix" class="form-control" rows="3" maxlength="1500"></textarea>
                        <p class="doc-ai-hint mb-0 mt-1">يُلحق هذا النص بنهاية كل عنوان محدد عند إرساله إلى صفحة الدفعة، ويُحفظ تلقائياً في هذا المتصفح لاستخدامه في المرات القادمة.</p>
                    </div>
                </div>

                <div class="card custom-card doc-ai-panel doc-ai-save-card doc-ai-animate">
                    <div class="card-body">
                        <button type="button" class="btn btn-success w-100 mb-2" id="suggestSendBtn" disabled>
                            <i class="fe fe-send me-1"></i>
                            إرسال المحدد إلى صفحة الدفعة
                        </button>
                        <p class="doc-ai-hint mb-0">
                            ستُفتح صفحة توليد الدفعة والعناوين المحددة معبأة فيها (حتى 100 عنوان) — راجع الإعدادات ثم أضفها إلى الطابور.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.documentElement.classList.add('loaded');
</script>
<script src="{{ asset('assets/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script>
(function () {
    const MAX_TITLES = 100;
    const PREFILL_KEY = 'docAiBatchPrefill';
    const SUFFIX_KEY = 'docAiTitleSuffix';
    const SUFFIX_ENABLED_KEY = 'docAiTitleSuffixEnabled';
    const DEFAULT_SUFFIX = 'اشرح الموضوع بشكل شامل من مستوى المبتدئ حتى مستوى الخبير، مع التركيز على الأمثلة العملية على شكل بلوكات كود منظمة ومشروحة، واستخدم أمثلة من الحياة الواقعية.';
    const docsEngineChoiceAvailable = @json(!empty($docsEngineChoiceAvailable));
    const defaultEngine = @json(!empty($useLaravelAiEngine) ? 'laravel_ai' : 'legacy');
    const suggestUrl = @json(route('admin.docs.ai-pages.suggest-titles.generate'));
    const batchUrl = @json(route('admin.docs.ai-pages.batch.create'));
    const csrfToken = @json(csrf_token());

    const listEl = document.getElementById('suggestList');

    function toast(opts) {
        if (typeof Swal === 'undefined') {
            if (opts.icon === 'error') alert(opts.title + (opts.text ? '\n' + opts.text : ''));
            return;
        }
        Swal.fire(Object.assign({
            toast: true,
            position: 'top-start',
            showConfirmButton: false,
            timer: 4500,
            timerProgressBar: true,
        }, opts));
    }

    function syncEngineVisibility() {
        if (!docsEngineChoiceAvailable) return;
        const laravelChecked = document.getElementById('suggest_docs_engine_laravel_ai')?.checked;
        const wL = document.getElementById('suggest_docs_engine_laravel_wrap');
        const wG = document.getElementById('suggest_docs_engine_legacy_wrap');
        if (wL) wL.style.display = laravelChecked ? '' : 'none';
        if (wG) wG.style.display = laravelChecked ? 'none' : '';
    }

    function setBusy(btn, busy) {
        const spinner = btn.querySelector('.loading-spinner');
        btn.disabled = busy;
        if (spinner) spinner.style.display = busy ? '' : 'none';
    }

    function items() {
        return Array.prototype.slice.call(listEl.querySelectorAll('.doc-title-suggest-item'));
    }

    function allTitles() {
        return items().map(function (row) { return row.querySelector('.form-control').value.trim(); })
            .filter(function (t) { return t !== ''; });
    }

    function selectedTitles() {
        const seen = {};
        return items()
            .filter(function (row) { return row.querySelector('.form-check-input').checked; })
            .map(function (row) { return row.querySelector('.form-control').value.replace(/\s+/g, ' ').trim(); })
            .filter(function (t) {
                if (!t || seen[t]) return false;
                seen[t] = true;
                return true;
            });
    }

    function refreshState() {
        const rows = items();
        rows.forEach(function (row, i) {
            row.querySelector('.doc-title-suggest-item__num').textContent = i + 1;
            row.classList.toggle('is-off', !row.querySelector('.form-check-input').checked);
        });
        document.getElementById('suggestTotalCount').textContent = rows.length;
        document.getElementById('suggestSelectedCount').textContent = selectedTitles().length;
        document.getElementById('suggestEmpty').style.display = rows.length ? 'none' : '';
        document.getElementById('suggestSendBtn').disabled = selectedTitles().length === 0;
        document.getElementById('suggestMoreBtn').disabled = rows.length === 0;
    }

    function addRow(title, focus) {
        const row = document.createElement('div');
        row.className = 'doc-title-suggest-item';

        const num = document.createElement('span');
        num.className = 'doc-title-suggest-item__num';

        const check = document.createElement('input');
        check.type = 'checkbox';
        check.className = 'form-check-input mt-0';
        check.checked = true;
        check.addEventListener('change', refreshState);

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control form-control-sm';
        input.maxLength = 3000;
        input.value = title;
        input.addEventListener('input', refreshState);

        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'btn btn-sm btn-light border';
        del.title = 'حذف';
        del.innerHTML = '<i class="fe fe-trash-2"></i>';
        del.addEventListener('click', function () {
            row.remove();
            refreshState();
        });

        row.append(num, check, input, del);
        listEl.appendChild(row);
        if (focus) input.focus();
    }

    function storageGet(key) {
        try { return localStorage.getItem(key); } catch (e) { return null; }
    }

    function storageSet(key, value) {
        try { localStorage.setItem(key, value); } catch (e) {}
    }

    function initSuffix() {
        const box = document.getElementById('suggest_suffix');
        const toggle = document.getElementById('suggest_suffix_enabled');
        const saved = storageGet(SUFFIX_KEY);
        box.value = saved !== null ? saved : DEFAULT_SUFFIX;
        toggle.checked = storageGet(SUFFIX_ENABLED_KEY) !== '0';
        box.disabled = !toggle.checked;

        box.addEventListener('input', function () { storageSet(SUFFIX_KEY, box.value); });
        toggle.addEventListener('change', function () {
            box.disabled = !toggle.checked;
            storageSet(SUFFIX_ENABLED_KEY, toggle.checked ? '1' : '0');
        });
        document.getElementById('suggestSuffixReset').addEventListener('click', function () {
            box.value = DEFAULT_SUFFIX;
            storageSet(SUFFIX_KEY, DEFAULT_SUFFIX);
        });
    }

    /** The batch page splits topics on blank lines, so the suffix is kept on one line. */
    function withSuffix(titles) {
        if (!document.getElementById('suggest_suffix_enabled').checked) return titles;
        const suffix = document.getElementById('suggest_suffix').value.replace(/\s+/g, ' ').trim();
        if (!suffix) return titles;
        return titles.map(function (t) { return t + '، ' + suffix; });
    }

    function engineValue() {
        return docsEngineChoiceAvailable
            ? (document.querySelector('input[name="suggest_docs_engine"]:checked')?.value || 'legacy')
            : defaultEngine;
    }

    function requestTitles(append, btn) {
        const mainTopic = document.getElementById('suggest_main_topic').value.trim();
        let count = parseInt(document.getElementById('suggest_count').value, 10) || 20;
        count = Math.min(MAX_TITLES, Math.max(5, count));

        if (!mainTopic) {
            toast({ icon: 'warning', title: 'يرجى كتابة العنوان الرئيسي أولاً' });
            return;
        }

        const engine = engineValue();
        const laravelEl = document.getElementById('suggest_laravel_ai_model_id');
        const legacyEl = document.getElementById('suggest_ai_model_id');

        const autoCount = document.getElementById('suggest_auto_count').checked;

        const payload = {
            main_topic: mainTopic,
            auto_count: autoCount,
            count: autoCount ? null : count,
            level: document.getElementById('suggest_level').value,
            language: document.getElementById('suggest_language').value,
            notes: document.getElementById('suggest_notes').value.trim() || null,
            documentation_category_id: document.getElementById('suggest_category_id').value || null,
            exclude: append ? allTitles() : [],
            docs_engine: engine,
            ai_model_id: engine === 'legacy' ? (legacyEl ? (legacyEl.value || null) : null) : null,
            laravel_ai_model_id: engine === 'laravel_ai' ? (laravelEl ? (laravelEl.value || null) : null) : null,
        };

        setBusy(btn, true);
        fetch(suggestUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        })
            .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
            .then(function (res) {
                setBusy(btn, false);
                if (res.body.success && Array.isArray(res.body.titles)) {
                    if (!append) listEl.innerHTML = '';
                    res.body.titles.forEach(function (t) { addRow(t, false); });
                    refreshState();
                    toast({ icon: 'success', title: 'تم اقتراح ' + res.body.titles.length + ' عنواناً' });
                    return;
                }
                toast({
                    icon: 'error',
                    title: 'تعذر اقتراح العناوين',
                    text: res.body.message || '',
                    timer: 7000,
                });
            })
            .catch(function () {
                setBusy(btn, false);
                toast({ icon: 'error', title: 'تعذر اقتراح العناوين', text: 'تحقق من الاتصال ثم أعد المحاولة.' });
            });
    }

    function syncAutoCount() {
        document.getElementById('suggest_count').disabled = document.getElementById('suggest_auto_count').checked;
    }

    document.addEventListener('DOMContentLoaded', function () {
        initSuffix();
        syncAutoCount();
        document.getElementById('suggest_auto_count').addEventListener('change', syncAutoCount);
        syncEngineVisibility();
        document.querySelectorAll('input[name="suggest_docs_engine"]').forEach(function (el) {
            el.addEventListener('change', syncEngineVisibility);
        });

        const suggestBtn = document.getElementById('suggestBtn');
        suggestBtn.addEventListener('click', function () {
            if (items().length && typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'question',
                    title: 'استبدال القائمة الحالية؟',
                    text: 'استخدم «اقتراح المزيد» لإضافة عناوين دون حذف الموجودة.',
                    showCancelButton: true,
                    confirmButtonText: 'استبدال',
                    cancelButtonText: 'إلغاء',
                }).then(function (r) { if (r.isConfirmed) requestTitles(false, suggestBtn); });
                return;
            }
            requestTitles(false, suggestBtn);
        });

        const moreBtn = document.getElementById('suggestMoreBtn');
        moreBtn.addEventListener('click', function () { requestTitles(true, moreBtn); });

        document.getElementById('suggestToggleAll').addEventListener('click', function () {
            const rows = items();
            const allOn = rows.every(function (row) { return row.querySelector('.form-check-input').checked; });
            rows.forEach(function (row) { row.querySelector('.form-check-input').checked = !allOn; });
            refreshState();
        });

        document.getElementById('suggestAddManual').addEventListener('click', function () {
            addRow('', true);
            refreshState();
        });

        document.getElementById('suggestSendBtn').addEventListener('click', function () {
            const topics = selectedTitles();
            if (!topics.length) {
                toast({ icon: 'warning', title: 'حدد عنواناً واحداً على الأقل' });
                return;
            }
            if (topics.length > MAX_TITLES) {
                toast({ icon: 'warning', title: 'الحد الأقصى 100 عنوان لكل دفعة', text: 'ألغِ تحديد ' + (topics.length - MAX_TITLES) + ' عنواناً.' });
                return;
            }
            const finalTopics = withSuffix(topics);
            if (finalTopics.some(function (t) { return t.length > 3000; })) {
                toast({ icon: 'warning', title: 'أحد المواضيع يتجاوز 3000 حرف بعد إضافة التعليمات', text: 'اختصر نص التعليمات أو العنوان.' });
                return;
            }
            try {
                sessionStorage.setItem(PREFILL_KEY, JSON.stringify({
                    topics: finalTopics,
                    documentation_category_id: document.getElementById('suggest_category_id').value || null,
                    language: document.getElementById('suggest_language').value,
                }));
            } catch (e) {
                toast({ icon: 'error', title: 'تعذر نقل العناوين', text: 'المتصفح يمنع التخزين المؤقت.' });
                return;
            }
            window.location.href = batchUrl;
        });

        refreshState();
    });
})();
</script>
@endsection
