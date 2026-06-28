@php
    $panelId = $panelId ?? 'sim-ai-refine';
    $refineUrl = $refineUrl ?? route('admin.lesson-simulators.refine-bundle');
    $useLaravelAiEngine = $useLaravelAiEngine ?? true;
    $simulatorsEngineChoiceAvailable = $simulatorsEngineChoiceAvailable ?? false;
@endphp

<div class="card shadow-sm border border-success mb-3" id="{{ $panelId }}-panel">
    <div class="card-header bg-success-transparent d-flex justify-content-between align-items-center">
        <strong class="text-success">
            <i class="fe fe-edit-2 me-2"></i>تعديل المحاكاة بالذكاء الاصطناعي
        </strong>
        <span class="badge bg-success">جاهز</span>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            يُطبَّق على HTML/CSS/JS الظاهر في المحررات أدناه. اكتب ما تريد تغييره بدقة — مثل الألوان، إضافة أزرار، تحسين النص العربي، أو تعديل التخطيط.
        </p>

        <div id="{{ $panelId }}-error" class="alert alert-danger d-none"></div>

        <div class="mb-3">
            <label class="form-label">تعليمات التعديل <span class="text-danger">*</span></label>
            <textarea class="form-control" id="{{ $panelId }}-instructions" rows="4"
                      placeholder="مثال: أضف خاصية gap للتحكم، غيّر الألوان إلى أزرق داكن، أضف زر إعادة تعيين، حسّن النص العربي في العناوين"></textarea>
        </div>

        @if(!empty($simulatorsEngineChoiceAvailable))
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <label class="form-label d-block">محرك AI</label>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="{{ $panelId }}_engine" id="{{ $panelId }}-engine-lara" value="laravel_ai" @checked($useLaravelAiEngine)>
                            <label class="form-check-label" for="{{ $panelId }}-engine-lara">Laravel AI SDK</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="{{ $panelId }}_engine" id="{{ $panelId }}-engine-legacy" value="legacy" @checked(! $useLaravelAiEngine)>
                            <label class="form-check-label" for="{{ $panelId }}-engine-legacy">موديلات قديمة</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 {{ !empty($useLaravelAiEngine) || !empty($simulatorsEngineChoiceAvailable) ? '' : 'd-none' }}" id="{{ $panelId }}-lara-model-wrap">
                    <label class="form-label">موديل Laravel AI</label>
                    <select class="form-select" id="{{ $panelId }}-lara-model">
                        <option value="">افتراضي</option>
                        @foreach($laravelAiModels ?? [] as $m)
                            <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->model }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6" id="{{ $panelId }}-legacy-model-wrap">
                    <label class="form-label">موديل قديم</label>
                    <select class="form-select" id="{{ $panelId }}-legacy-model">
                        <option value="">افتراضي</option>
                        @foreach($legacyModels ?? [] as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        <button type="button" class="btn btn-success" id="{{ $panelId }}-btn">
            <i class="fe fe-magic me-1"></i>تطبيق التعديلات
        </button>
        <p class="text-muted small mb-0 mt-2">قد يستغرق 1–3 دقائق. بعد التطبيق راجع المعاينة ثم احفظ النموذج.</p>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const panelId = @json($panelId);
    const refineUrl = @json($refineUrl);
    const csrf = @json(csrf_token());
    const defaultEngine = @json($useLaravelAiEngine ? 'laravel_ai' : 'legacy');

    const errEl = document.getElementById(panelId + '-error');
    const btn = document.getElementById(panelId + '-btn');

    function selectedEngine() {
        const r = document.querySelector('input[name="' + panelId + '_engine"]:checked');
        return r ? r.value : defaultEngine;
    }

    function toggleModelSelects() {
        const engine = selectedEngine();
        const laraWrap = document.getElementById(panelId + '-lara-model-wrap');
        const legacyWrap = document.getElementById(panelId + '-legacy-model-wrap');
        if (laraWrap) laraWrap.classList.toggle('d-none', engine !== 'laravel_ai');
        if (legacyWrap) legacyWrap.classList.toggle('d-none', engine === 'laravel_ai');
    }

    document.querySelectorAll('input[name="' + panelId + '_engine"]').forEach(function (el) {
        el.addEventListener('change', toggleModelSelects);
    });
    toggleModelSelects();

    function enginePayload() {
        return {
            simulators_engine: selectedEngine(),
            laravel_ai_model_id: document.getElementById(panelId + '-lara-model')?.value || null,
            ai_model_id: document.getElementById(panelId + '-legacy-model')?.value || null,
        };
    }

    function currentBundleFromEditors() {
        return {
            bundle_html: document.getElementById('bundle_html')?.value || '',
            bundle_css: document.getElementById('bundle_css')?.value || '',
            bundle_js: document.getElementById('bundle_js')?.value || '',
            title: document.querySelector('#simulator-bundle-form [name="title"]')?.value || '',
        };
    }

    function showError(msg) {
        errEl.textContent = msg;
        errEl.classList.remove('d-none');
    }

    function clearError() {
        errEl.classList.add('d-none');
        errEl.textContent = '';
    }

    function setLoading(loading) {
        if (!btn) return;
        btn.disabled = loading;
        if (loading) {
            btn.dataset.original = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>جاري التعديل...';
        } else if (btn.dataset.original) {
            btn.innerHTML = btn.dataset.original;
        }
    }

    if (!btn) return;

    btn.addEventListener('click', function () {
        clearError();
        const instructions = document.getElementById(panelId + '-instructions')?.value.trim() || '';
        if (!instructions) {
            showError('اكتب تعليمات التعديل أولاً.');
            return;
        }
        const bundle = currentBundleFromEditors();
        if (!bundle.bundle_html.trim()) {
            showError('لا يوجد HTML في المحرر — أضف محتوى أو ولّد محاكاة أولاً.');
            return;
        }
        setLoading(true);

        fetch(refineUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(Object.assign({
                instructions: instructions,
            }, bundle, enginePayload())),
        })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                if (!res.ok || !res.data.success) {
                    throw new Error(res.data.message || 'فشل التعديل');
                }
                document.dispatchEvent(new CustomEvent('simulator-ai-generated', { detail: res.data.data }));
            })
            .catch(function (e) {
                showError(e.message || 'حدث خطأ أثناء التعديل.');
            })
            .finally(function () {
                setLoading(false);
            });
    });
})();
</script>
@endpush
