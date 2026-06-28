@php
    $panelId = $panelId ?? 'sim-ai';
    $syncUrl = $syncUrl ?? route('admin.lesson-simulators.generate-bundle');
    $asyncUrl = $asyncUrl ?? null;
    $regenerateUrl = $regenerateUrl ?? null;
    $showAsync = $showAsync ?? ! empty($asyncUrl);
    $showRegenerateAsync = $showRegenerateAsync ?? ! empty($regenerateUrl);
    $defaultTopic = $defaultTopic ?? old('topic_description', '');
    $defaultDetails = $defaultDetails ?? old('simulation_details', '');
    $showRefine = $showRefine ?? false;
    $refineUrl = $refineUrl ?? route('admin.lesson-simulators.refine-bundle');
    $collapsed = $collapsed ?? false;
@endphp

<div class="card shadow-sm border-0 mb-3 {{ $collapsed ? '' : 'border-info' }}" id="{{ $panelId }}-panel">
    <div class="card-header d-flex justify-content-between align-items-center bg-light">
        <strong><i class="fe fe-zap me-2 text-warning"></i>توليد{{ $showRefine ? ' وتعديل' : '' }} بالذكاء الاصطناعي</strong>
        @if($collapsed)
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $panelId }}-body">
                إظهار/إخفاء
            </button>
        @endif
    </div>
    <div class="{{ $collapsed ? 'collapse' : '' }}" id="{{ $panelId }}-body">
        <div class="card-body">
            @if(!empty($regenerateUrl))
                <div class="alert alert-warning small">
                    إعادة التوليد ستستبدل HTML/CSS/JS الحالي — احفظ نسخة إذا لزم الأمر.
                </div>
            @endif

            <div id="{{ $panelId }}-error" class="alert alert-danger d-none"></div>

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">الموضوع / ما تريد شرحه <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="{{ $panelId }}-topic" value="{{ $defaultTopic }}"
                           placeholder="مثال: الأمن السيبراني، مصفوفات PHP، Flexbox في CSS">
                </div>
                <div class="col-md-4">
                    <label class="form-label">موضوع جاهز (اختياري)</label>
                    <select class="form-select" id="{{ $panelId }}-topic-key">
                        <option value="">— مخصص —</option>
                        @foreach($topics as $group => $items)
                            <optgroup label="{{ $group }}">
                                @foreach($items as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">اللغة / المكدس</label>
                    <select class="form-select" id="{{ $panelId }}-language">
                        @foreach($primaryLanguages as $code => $label)
                            <option value="{{ $code }}" @selected($code === 'html')>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">المستوى</label>
                    <select class="form-select" id="{{ $panelId }}-level">
                        @foreach($levels as $code => $label)
                            <option value="{{ $code }}" @selected($code === 'beginner')>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">نوع المحاكاة</label>
                    <select class="form-select" id="{{ $panelId }}-archetype">
                        <option value="auto">تلقائي</option>
                        @foreach($archetypes as $code => $label)
                            <option value="{{ $code }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">تفاصيل إضافية (اختياري)</label>
                    <textarea class="form-control" id="{{ $panelId }}-details" rows="2" placeholder="مثال: 3 سيناريوهات، ألوان داكنة، زر ابدأ الاستكشاف">{{ $defaultDetails }}</textarea>
                </div>

                @if(!empty($simulatorsEngineChoiceAvailable))
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
                @endif

                <div class="col-md-6 {{ !empty($useLaravelAiEngine) || !empty($simulatorsEngineChoiceAvailable) ? '' : 'd-none' }}" id="{{ $panelId }}-lara-model-wrap">
                    <label class="form-label">موديل Laravel AI</label>
                    <select class="form-select" id="{{ $panelId }}-lara-model">
                        <option value="">افتراضي</option>
                        @foreach($laravelAiModels as $m)
                            <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->model }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 {{ empty($useLaravelAiEngine) && empty($simulatorsEngineChoiceAvailable) ? '' : '' }}" id="{{ $panelId }}-legacy-model-wrap">
                    <label class="form-label">موديل قديم</label>
                    <select class="form-select" id="{{ $panelId }}-legacy-model">
                        <option value="">افتراضي</option>
                        @foreach($legacyModels as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3">
                <button type="button" class="btn btn-primary" id="{{ $panelId }}-btn-sync">
                    <i class="fe fe-zap me-1"></i>توليد الآن
                </button>
                @if($showAsync)
                    <button type="button" class="btn btn-outline-primary" id="{{ $panelId }}-btn-async">
                        <i class="fe fe-clock me-1"></i>توليد في الخلفية
                    </button>
                @endif
                @if($showRegenerateAsync)
                    <button type="button" class="btn btn-outline-warning" id="{{ $panelId }}-btn-regen-async">
                        <i class="fe fe-refresh-cw me-1"></i>إعادة توليد (Queue)
                    </button>
                @endif
            </div>
            <p class="text-muted small mb-0 mt-2">التوليد الفوري قد يستغرق 1–3 دقائق. استخدم Queue للمواضيع الطويلة.</p>

            @if($showRefine)
                <hr class="my-4">
                <h6 class="mb-2"><i class="fe fe-edit-2 me-1"></i>تعديل المحاكاة الحالية</h6>
                <p class="text-muted small">يُطبَّق على HTML/CSS/JS الظاهر في المحررات أدناه — اكتب ما تريد تغييره بدقة.</p>
                <div class="mb-3">
                    <label class="form-label">تعليمات التعديل <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="{{ $panelId }}-refine-instructions" rows="3"
                              placeholder="مثال: أضف خاصية gap للتحكم، غيّر الألوان إلى أزرق داكن، أضف زر إعادة تعيين، حسّن النص العربي في العناوين"></textarea>
                </div>
                <button type="button" class="btn btn-success" id="{{ $panelId }}-btn-refine">
                    <i class="fe fe-magic me-1"></i>تطبيق التعديلات
                </button>
            @endif
        </div>
    </div>
</div>

<script>
(function () {
    const panelId = @json($panelId);
    const syncUrl = @json($syncUrl);
    const asyncUrl = @json($asyncUrl);
    const regenerateUrl = @json($regenerateUrl);
    const refineUrl = @json($refineUrl);
    const showRefine = @json($showRefine);
    const csrf = @json(csrf_token());

    const errEl = document.getElementById(panelId + '-error');
    const btnSync = document.getElementById(panelId + '-btn-sync');
    const btnAsync = document.getElementById(panelId + '-btn-async');
    const btnRegenAsync = document.getElementById(panelId + '-btn-regen-async');
    const btnRefine = document.getElementById(panelId + '-btn-refine');

    function selectedEngine() {
        const r = document.querySelector('input[name="' + panelId + '_engine"]:checked');
        return r ? r.value : @json($useLaravelAiEngine ? 'laravel_ai' : 'legacy');
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

    function payload() {
        return Object.assign({
            topic_description: document.getElementById(panelId + '-topic').value.trim(),
            topic_key: document.getElementById(panelId + '-topic-key').value,
            primary_language: document.getElementById(panelId + '-language').value,
            level: document.getElementById(panelId + '-level').value,
            archetype: document.getElementById(panelId + '-archetype').value,
            simulation_details: document.getElementById(panelId + '-details').value.trim(),
        }, enginePayload());
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

    function setLoading(btn, loading) {
        if (!btn) return;
        btn.disabled = loading;
        if (loading) {
            btn.dataset.original = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>جاري التوليد...';
        } else if (btn.dataset.original) {
            btn.innerHTML = btn.dataset.original;
        }
    }

    function dispatchGenerated(data) {
        document.dispatchEvent(new CustomEvent('simulator-ai-generated', { detail: data }));
    }

    if (btnSync) {
        btnSync.addEventListener('click', function () {
            clearError();
            const body = payload();
            if (!body.topic_description) {
                showError('أدخل الموضوع أولاً.');
                return;
            }
            setLoading(btnSync, true);

            const targetUrl = regenerateUrl || syncUrl;
            if (regenerateUrl) {
                body.mode = 'sync';
            }

            fetch(targetUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(body),
            })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
                .then(function (res) {
                    if (!res.ok || !res.data.success) {
                        throw new Error(res.data.message || 'فشل التوليد');
                    }
                    if (regenerateUrl && !res.data.data) {
                        window.location.reload();
                        return;
                    }
                    dispatchGenerated(res.data.data);
                })
                .catch(function (e) {
                    showError(e.message || 'حدث خطأ أثناء التوليد.');
                })
                .finally(function () {
                    setLoading(btnSync, false);
                });
        });
    }

    function submitAsyncForm(url, mode) {
        clearError();
        const body = payload();
        if (!body.topic_description) {
            showError('أدخل الموضوع أولاً.');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.style.display = 'none';

        const token = document.createElement('input');
        token.name = '_token';
        token.value = csrf;
        form.appendChild(token);

        if (mode) {
            const modeInput = document.createElement('input');
            modeInput.name = 'mode';
            modeInput.value = mode;
            form.appendChild(modeInput);
        }

        Object.keys(body).forEach(function (key) {
            if (body[key] !== null && body[key] !== '') {
                const input = document.createElement('input');
                input.name = key;
                input.value = body[key];
                form.appendChild(input);
            }
        });

        document.body.appendChild(form);
        form.submit();
    }

    if (btnAsync && asyncUrl) {
        btnAsync.addEventListener('click', function () {
            submitAsyncForm(asyncUrl);
        });
    }

    if (btnRegenAsync && regenerateUrl) {
        btnRegenAsync.addEventListener('click', function () {
            if (!confirm('إعادة التوليد في الخلفية؟')) return;
            submitAsyncForm(regenerateUrl, 'async');
        });
    }

    if (btnRefine && showRefine) {
        btnRefine.addEventListener('click', function () {
            clearError();
            const instructions = document.getElementById(panelId + '-refine-instructions')?.value.trim() || '';
            if (!instructions) {
                showError('اكتب تعليمات التعديل أولاً.');
                return;
            }
            const bundle = currentBundleFromEditors();
            if (!bundle.bundle_html.trim()) {
                showError('لا يوجد HTML في المحرر — أضف محتوى أو ولّد محاكاة أولاً.');
                return;
            }
            setLoading(btnRefine, true);

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
                    dispatchGenerated(res.data.data);
                })
                .catch(function (e) {
                    showError(e.message || 'حدث خطأ أثناء التعديل.');
                })
                .finally(function () {
                    setLoading(btnRefine, false);
                });
        });
    }
})();
</script>
