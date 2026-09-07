@extends('admin.layouts.master')

@section('page-title', 'توليد دفعة مواضيع توثيق بالذكاء الاصطناعي')

@section('styles')
@include('admin.docs.categories.partials.styles')
@include('admin.docs.pages.partials.ai-page-styles')
<style>
    .doc-ai-batch-table { width: 100%; font-size: .875rem; }
    .doc-ai-batch-table td, .doc-ai-batch-table th { padding: .5rem .6rem; vertical-align: middle; }
    .doc-ai-batch-badge { display:inline-block; padding:.2rem .55rem; border-radius:999px; font-size:.75rem; font-weight:600; }
    .doc-ai-batch-badge--pending { background:#eef0f2; color:#6b7280; }
    .doc-ai-batch-badge--running { background:#fff3cd; color:#8a6100; }
    .doc-ai-batch-badge--completed { background:#e6f7ed; color:#0f7b42; }
    .doc-ai-batch-badge--failed { background:#fdecea; color:#b3261e; }
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
                    <li class="breadcrumb-item active">توليد دفعة مواضيع</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in doc-ai-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-layers me-1"></i>
                        مساعد التوثيق
                    </span>
                    <h2 class="group-show-hero__title mb-2">توليد دفعة مواضيع توثيق</h2>
                    <p class="group-show-hero__desc mb-2">
                        اكتب عدة مواضيع (كل موضوع في سطر)، اضبط الإعدادات المشتركة، ثم أضفها إلى الطابور — ستُعالَج المواضيع تباعاً وتُحفظ صفحة كل موضوع فوراً بعد اكتماله، دون الحاجة لأي تدخل يدوي.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('admin.docs.pages.index') }}" class="btn btn-light border">
                            <i class="fe fe-list me-1"></i>قائمة الصفحات
                        </a>
                        <a href="{{ route('admin.docs.ai-pages.create') }}" class="btn btn-outline-secondary">
                            <i class="fe fe-zap me-1"></i>توليد موضوع واحد
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card custom-card doc-ai-panel doc-cat-filter-card doc-ai-animate mb-4">
                    <div class="card-header doc-ai-panel__header border-0">
                        <h6 class="doc-ai-panel__title">
                            <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--content"><i class="fe fe-file-text"></i></span>
                            المواضيع
                        </h6>
                    </div>
                    <div class="card-body pt-2">
                        <label class="form-label" for="batchTopics">المواضيع (افصل بين كل موضوع وآخر بفاصلة ,) <span class="text-danger">*</span></label>
                        <textarea id="batchTopics" class="form-control" rows="10" placeholder="التوجيه في Laravel، اشرح بالتفصيل مع أمثلة كاملة وشرحها على شكل بلوك كود,&#10;أنواع البيانات في PHP,&#10;النماذج في HTML"></textarea>
                        <p class="doc-ai-hint mb-0 mt-1"><span id="batchTopicsCount">0</span> موضوع مكتشف — الحد الأقصى 30 موضوعاً لكل دفعة. يمكن أن يمتد شرح الموضوع الواحد على عدة أسطر؛ الفاصلة (,) هي التي تفصل بين موضوع وآخر.</p>
                    </div>
                </div>

                <div class="card custom-card doc-ai-panel doc-cat-filter-card doc-ai-animate mb-4">
                    <div class="card-header doc-ai-panel__header border-0">
                        <h6 class="doc-ai-panel__title">
                            <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--ai"><i class="fe fe-zap"></i></span>
                            إعدادات التوليد
                        </h6>
                    </div>
                    <div class="card-body pt-2">
                        @if(!empty($docsEngineChoiceAvailable))
                            <div class="mb-4">
                                <label class="form-label d-block">محرك التوثيق</label>
                                <div class="doc-ai-engine-pills">
                                    <div class="doc-ai-engine-pill">
                                        <input class="form-check-input" type="radio" name="batch_docs_engine" id="batch_docs_engine_laravel_ai" value="laravel_ai" {{ !empty($useLaravelAiEngine) ? 'checked' : '' }}>
                                        <label for="batch_docs_engine_laravel_ai">
                                            <i class="fe fe-cpu"></i>
                                            Laravel AI SDK
                                        </label>
                                    </div>
                                    <div class="doc-ai-engine-pill">
                                        <input class="form-check-input" type="radio" name="batch_docs_engine" id="batch_docs_engine_legacy" value="legacy" {{ empty($useLaravelAiEngine) ? 'checked' : '' }}>
                                        <label for="batch_docs_engine_legacy">
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
                                        <div id="batch_docs_engine_laravel_wrap" class="docs-engine-model-wrap" style="{{ !empty($docsEngineChoiceAvailable) && empty($useLaravelAiEngine) ? 'display:none' : '' }}">
                                            <label class="form-label" for="batch_laravel_ai_model_id">موديل Laravel AI SDK</label>
                                            <select id="batch_laravel_ai_model_id" class="form-select" @if($laravelAiModels->isEmpty()) disabled @endif>
                                                <option value="">افتراضي (أولوية + docs.refine)</option>
                                                @foreach($laravelAiModels as $lmodel)
                                                    <option value="{{ $lmodel->id }}">{{ $lmodel->name }} — {{ $lmodel->provider }}/{{ $lmodel->model }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                @if(!empty($docsEngineChoiceAvailable) || ($models->isNotEmpty() && $laravelAiModels->isEmpty()))
                                    <div class="col-md-6">
                                        <div id="batch_docs_engine_legacy_wrap" class="docs-engine-model-wrap" style="{{ !empty($docsEngineChoiceAvailable) && !empty($useLaravelAiEngine) ? 'display:none' : '' }}">
                                            <label class="form-label" for="batch_ai_model_id">موديل AI (بنك الموديلات)</label>
                                            <select id="batch_ai_model_id" class="form-select" @if($models->isEmpty()) disabled @endif>
                                                <option value="">الافتراضي</option>
                                                @foreach($models as $model)
                                                    <option value="{{ $model->id }}">{{ $model->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-4">
                                    <label class="form-label" for="batch_content_length">طول المحتوى</label>
                                    <select id="batch_content_length" class="form-select">
                                        <option value="short">قصير</option>
                                        <option value="medium" selected>متوسط</option>
                                        <option value="long">طويل</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="batch_tone">الأسلوب</label>
                                    <select id="batch_tone" class="form-select">
                                        <option value="professional">احترافي</option>
                                        <option value="friendly">ودود</option>
                                        <option value="technical" selected>تقني</option>
                                        <option value="casual">عادي</option>
                                        <option value="formal">رسمي</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="batch_language">اللغة</label>
                                    <select id="batch_language" class="form-select">
                                        <option value="ar" selected>العربية</option>
                                        <option value="en">English</option>
                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="batch_generate_meta" checked>
                            <label class="form-check-label" for="batch_generate_meta">توليد meta_title و meta_description تلقائياً لكل صفحة</label>
                        </div>
                    </div>
                </div>

                <div class="card custom-card doc-ai-panel doc-cat-table-card doc-ai-animate mb-4">
                    <div class="card-header doc-ai-panel__header border-0">
                        <h6 class="doc-ai-panel__title">
                            <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--content"><i class="fe fe-activity"></i></span>
                            تقدّم الطابور
                        </h6>
                    </div>
                    <div class="card-body pt-2">
                        <div id="batchIdleMsg" class="doc-ai-hint mb-0">
                            <i class="fe fe-info me-1"></i>
                            لم تبدأ أي دفعة بعد. اكتب المواضيع واضغط «إضافة إلى الطابور».
                        </div>
                        <div id="batchProgressWrap" style="display:none;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted" id="batchProgressLabel">في الطابور…</small>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="batchCancelBtn">
                                    <i class="fe fe-x me-1"></i>إلغاء الدفعة
                                </button>
                            </div>
                            <div class="progress mb-3" style="height: 8px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="batchProgressBar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <div class="table-responsive">
                                <table class="doc-ai-batch-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>الموضوع</th>
                                            <th>الحالة</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="batchItemsBody"></tbody>
                                </table>
                            </div>
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
                                التصنيف والحفظ
                            </h6>
                        </div>
                        <div class="card-body pt-2">
                            <div class="mb-3">
                                <label class="form-label" for="batch_doc_category_id">القسم <span class="text-danger">*</span></label>
                                <select id="batch_doc_category_id" class="form-select" required>
                                    <option value="">— اختر القسم —</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ (string) $categoryId === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="batch_doc_parent_id">صفحة أب (اختياري)</label>
                                <select id="batch_doc_parent_id" class="form-select">
                                    <option value="">— بدون —</option>
                                </select>
                                <p class="doc-ai-hint mb-0">تُطبَّق على كل صفحات هذه الدفعة.</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">الترتيب</label>
                                <input type="number" id="batch_sort_order" class="form-control" value="0" min="0">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">الحالة <span class="text-danger">*</span></label>
                                <select id="batch_status" class="form-select" required>
                                    <option value="draft">مسودة</option>
                                    <option value="published" selected>منشور</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">تاريخ النشر</label>
                                <input type="datetime-local" id="batch_published_at" class="form-control" value="{{ $defaultPublishedAt ?? now()->format('Y-m-d\TH:i') }}">
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="batch_is_indexable" checked>
                                <label class="form-check-label" for="batch_is_indexable">قابلة للفهرسة</label>
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card doc-ai-panel doc-ai-save-card doc-ai-animate">
                        <div class="card-body">
                            <button type="button" class="btn btn-primary w-100 mb-2" id="batchSubmitBtn">
                                <span class="loading-spinner spinner-border spinner-border-sm" role="status" style="display:none;"></span>
                                <i class="fe fe-plus-circle me-1"></i>
                                <span class="btn-text">إضافة إلى الطابور</span>
                            </button>
                            <p class="doc-ai-hint mb-0">
                                كل موضوع يُولَّد ثم تُحفظ صفحته فوراً — لا حاجة لمراجعة يدوية. يمكنك مغادرة الصفحة والعودة لاحقاً.
                            </p>
                        </div>
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
<script>
(function () {
    const parentPages = @json($parentPagesJson);
    const docsEngineChoiceAvailable = @json(!empty($docsEngineChoiceAvailable));
    const statusUrlBase = @json(route('admin.docs.ai-pages.batch.show', ['uuid' => '__UUID__']));
    const cancelUrlBase = @json(route('admin.docs.ai-pages.batch.cancel', ['uuid' => '__UUID__']));
    const storeUrl = @json(route('admin.docs.ai-pages.batch.store'));
    const csrfToken = @json(csrf_token());

    let pollTimer = null;
    let currentUuid = null;

    function refreshParentOptions() {
        const catId = document.getElementById('batch_doc_category_id').value;
        const sel = document.getElementById('batch_doc_parent_id');
        const current = sel.value;
        sel.innerHTML = '<option value="">— بدون —</option>';
        parentPages.filter(function (p) { return String(p.category_id) === String(catId); }).forEach(function (p) {
            const o = document.createElement('option');
            o.value = p.id;
            o.textContent = p.label;
            sel.appendChild(o);
        });
        if (current) sel.value = current;
    }

    function syncEngineVisibility() {
        if (!docsEngineChoiceAvailable) return;
        const laravelChecked = document.getElementById('batch_docs_engine_laravel_ai')?.checked;
        const wL = document.getElementById('batch_docs_engine_laravel_wrap');
        const wG = document.getElementById('batch_docs_engine_legacy_wrap');
        if (wL) wL.style.display = laravelChecked ? '' : 'none';
        if (wG) wG.style.display = laravelChecked ? 'none' : '';
    }

    function parseTopics() {
        const raw = document.getElementById('batchTopics').value || '';
        const seen = {};
        return raw.split(',')
            // A single topic's own explanation may span several lines; only the
            // comma separates one topic from the next, so newlines inside a
            // topic are collapsed into spaces instead of breaking it apart.
            .map(function (t) { return t.replace(/\s+/g, ' ').trim(); })
            .filter(function (t) {
                if (!t) return false;
                if (seen[t]) return false;
                seen[t] = true;
                return true;
            });
    }

    function updateTopicsCount() {
        document.getElementById('batchTopicsCount').textContent = parseTopics().length;
    }

    function badgeClass(status) {
        return 'doc-ai-batch-badge doc-ai-batch-badge--' + (status || 'pending');
    }

    function badgeLabel(status) {
        const map = { pending: 'قيد الانتظار', running: 'جاري التوليد…', completed: 'مكتملة', failed: 'فشلت' };
        return map[status] || status;
    }

    function renderItems(batch) {
        const body = document.getElementById('batchItemsBody');
        body.innerHTML = '';
        (batch.items || []).forEach(function (item) {
            const tr = document.createElement('tr');

            const num = document.createElement('td');
            num.textContent = item.position + 1;
            tr.appendChild(num);

            const topic = document.createElement('td');
            topic.textContent = item.topic;
            tr.appendChild(topic);

            const status = document.createElement('td');
            const badge = document.createElement('span');
            badge.className = badgeClass(item.status);
            badge.textContent = badgeLabel(item.status);
            status.appendChild(badge);
            if (item.status === 'failed' && item.error_message) {
                const small = document.createElement('div');
                small.className = 'text-danger small mt-1';
                small.textContent = item.error_message;
                status.appendChild(small);
            }
            tr.appendChild(status);

            const actions = document.createElement('td');
            if (item.edit_url) {
                const a = document.createElement('a');
                a.href = item.edit_url;
                a.target = '_blank';
                a.className = 'btn btn-sm btn-outline-secondary';
                a.textContent = 'فتح الصفحة';
                actions.appendChild(a);
            }
            tr.appendChild(actions);

            body.appendChild(tr);
        });
    }

    function renderBatch(batch) {
        document.getElementById('batchIdleMsg').style.display = 'none';
        document.getElementById('batchProgressWrap').style.display = '';

        const done = (batch.completed || 0) + (batch.failed || 0);
        const pct = batch.total ? Math.round((done / batch.total) * 100) : 0;
        document.getElementById('batchProgressBar').style.width = pct + '%';

        const labelMap = {
            queued: 'في الطابور…',
            running: 'جاري المعالجة… (' + done + ' من ' + batch.total + ')',
            completed: 'اكتملت الدفعة بنجاح (' + batch.total + ' من ' + batch.total + ')',
            completed_with_errors: 'اكتملت الدفعة مع بعض الأخطاء (' + batch.failed + ' فاشلة من ' + batch.total + ')',
            cancelled: 'أُلغيت الدفعة',
        };
        document.getElementById('batchProgressLabel').textContent = labelMap[batch.status] || batch.status;
        document.getElementById('batchCancelBtn').style.display = batch.finished ? 'none' : '';

        renderItems(batch);

        if (batch.finished && pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    function startPolling(uuid) {
        currentUuid = uuid;
        if (pollTimer) clearInterval(pollTimer);
        const url = statusUrlBase.replace('__UUID__', uuid);
        function tick() {
            fetch(url, { headers: { Accept: 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.success && res.batch) renderBatch(res.batch);
                });
        }
        tick();
        pollTimer = setInterval(tick, 3000);
    }

    function setSubmitting(isSubmitting) {
        const btn = document.getElementById('batchSubmitBtn');
        const spinner = btn.querySelector('.loading-spinner');
        btn.disabled = isSubmitting;
        if (spinner) spinner.style.display = isSubmitting ? '' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function () {
        refreshParentOptions();
        document.getElementById('batch_doc_category_id').addEventListener('change', refreshParentOptions);
        syncEngineVisibility();
        document.querySelectorAll('input[name="batch_docs_engine"]').forEach(function (el) {
            el.addEventListener('change', syncEngineVisibility);
        });
        document.getElementById('batchTopics').addEventListener('input', updateTopicsCount);
        updateTopicsCount();

        document.getElementById('batchSubmitBtn').addEventListener('click', function () {
            const topics = parseTopics();
            const categoryId = document.getElementById('batch_doc_category_id').value;

            if (!topics.length) {
                alert('يرجى إدخال موضوع واحد على الأقل');
                return;
            }
            if (topics.length > 30) {
                alert('الحد الأقصى 30 موضوعاً لكل دفعة');
                return;
            }
            const tooLong = topics.find(function (t) { return t.length > 3000; });
            if (tooLong) {
                alert('أحد المواضيع يتجاوز 3000 حرف — يرجى اختصار النص أو تقسيمه.');
                return;
            }
            if (!categoryId) {
                alert('يرجى اختيار قسم التوثيق أولاً');
                return;
            }

            let engine = docsEngineChoiceAvailable
                ? (document.querySelector('input[name="batch_docs_engine"]:checked')?.value || 'legacy')
                : @json(!empty($useLaravelAiEngine) ? 'laravel_ai' : 'legacy');

            const laravelEl = document.getElementById('batch_laravel_ai_model_id');
            const legacyEl = document.getElementById('batch_ai_model_id');

            const payload = {
                topics: topics,
                docs_engine: engine,
                ai_model_id: engine === 'legacy' ? (legacyEl ? (legacyEl.value || null) : null) : null,
                laravel_ai_model_id: engine === 'laravel_ai' ? (laravelEl ? (laravelEl.value || null) : null) : null,
                content_length: document.getElementById('batch_content_length').value,
                tone: document.getElementById('batch_tone').value,
                language: document.getElementById('batch_language').value,
                documentation_category_id: categoryId,
                parent_id: document.getElementById('batch_doc_parent_id').value || null,
                generate_meta: document.getElementById('batch_generate_meta').checked,
                status: document.getElementById('batch_status').value,
                published_at: document.getElementById('batch_published_at').value || null,
                is_indexable: document.getElementById('batch_is_indexable').checked,
                sort_order: document.getElementById('batch_sort_order').value || 0,
            };

            setSubmitting(true);
            fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(payload),
            })
                .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, body: j }; }); })
                .then(function (res) {
                    setSubmitting(false);
                    if (res.body.success && res.body.batch) {
                        renderBatch(res.body.batch);
                        startPolling(res.body.batch.uuid);
                        return;
                    }
                    alert(res.body.message || 'تعذر بدء الدفعة');
                })
                .catch(function () {
                    setSubmitting(false);
                    alert('تعذر بدء الدفعة — تحقق من الاتصال ثم أعد المحاولة');
                });
        });

        document.getElementById('batchCancelBtn').addEventListener('click', function () {
            if (!currentUuid) return;
            if (!confirm('إلغاء الدفعة؟ المواضيع التي لم تبدأ بعد لن تُعالَج.')) return;
            fetch(cancelUrlBase.replace('__UUID__', currentUuid), {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
            })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.success && res.batch) renderBatch(res.batch);
                });
        });
    });
})();
</script>
@endsection
