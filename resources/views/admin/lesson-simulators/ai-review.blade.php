@extends('admin.layouts.master')

@section('page-title')
    مراجعة توليد المحاكاة
@stop

@section('styles')
@include('admin.docs.pages.partials.ai-page-styles')
@endsection

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h5 class="page-title fs-21 mb-0">مراجعة توليد المحاكاة</h5>
                <p class="text-muted small mb-0">{{ $simulator->title }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.lesson-simulators.preview', $simulator) }}" class="btn btn-outline-info btn-sm" target="_blank">معاينة</a>
                <a href="{{ route('admin.lesson-simulators.index') }}" class="btn btn-secondary btn-sm">رجوع</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @php
            $status = $generationStatus ?? 'unknown';
            $gp = $generationPayload ?? [];
            $resumable = (bool) ($gp['resumable'] ?? false);
        @endphp

        @if(in_array($status, ['queued', 'running'], true))
            <div class="card custom-card doc-ai-panel doc-ai-animate mb-4" id="ai-polling-card">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="spinner-border text-primary" role="status"></div>
                        <div>
                            <h6 class="mb-0" id="ai-polling-stage">{{ $gp['stage_label'] ?? 'جاري بدء التوليد…' }}</h6>
                            <p class="doc-ai-hint mb-0">لا تغلق الصفحة — التوليد يخطط ثم يكتب HTML وCSS وJS تباعاً.</p>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 8px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="ai-polling-bar" role="progressbar" style="width: {{ (int) ($gp['progress'] ?? 0) }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <small class="text-muted" id="ai-polling-phases">
                            @if(!empty($gp['phases']))
                                {{ $gp['phases']['done'] }} من {{ $gp['phases']['planned'] }} ملفات
                            @endif
                        </small>
                        <small class="fw-semibold" id="ai-polling-pct">{{ (int) ($gp['progress'] ?? 0) }}%</small>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-outline-danger btn-sm" id="ai-cancel-btn">
                            <span class="loading-spinner spinner-border spinner-border-sm" role="status"></span>
                            <i class="fe fe-square me-1"></i>
                            <span class="btn-text">إيقاف التوليد</span>
                        </button>
                        <p class="doc-ai-hint mb-0 mt-1">قد يستغرق الإيقاف حتى تنتهي المحاولة الحالية (لا يمكن مقاطعة طلب جارٍ فعلياً للموديل).</p>
                    </div>
                </div>
            </div>
        @elseif(in_array($status, ['failed', 'paused', 'cancelled'], true))
            @php
                $bannerClass = match ($status) { 'failed' => 'danger', 'cancelled' => 'secondary', default => 'warning' };
                $bannerLabel = match ($status) { 'failed' => 'فشل التوليد:', 'cancelled' => 'أُوقِف التوليد:', default => 'توقّف التوليد مؤقتاً:' };
            @endphp
            <div class="alert alert-{{ $bannerClass }}">
                <strong>{{ $bannerLabel }}</strong>
                {{ $gp['error_message'] ?? 'خطأ غير معروف' }}
            </div>
            @if($resumable)
                <div class="d-flex mb-3">
                    <button type="button" class="doc-ai-generate-btn" id="ai-resume-btn">
                        <span class="loading-spinner spinner-border spinner-border-sm" role="status"></span>
                        <i class="fe fe-play"></i>
                        <span class="btn-text">متابعة التوليد</span>
                    </button>
                </div>
            @endif
            @include('admin.lesson-simulators.partials.ai-generate-panel', array_merge(
                \App\Support\SimulatorAiWizard::viewData(),
                [
                    'panelId' => 'sim-ai-review',
                    'defaultTopic' => $simulator->description ?? $simulator->title,
                    'regenerateUrl' => route('admin.lesson-simulators.ai.regenerate', $simulator),
                    'showRegenerateAsync' => true,
                    'collapsed' => $resumable,
                ]
            ))
        @elseif($status === 'completed')
            <div class="alert alert-success mb-3">
                <i class="fe fe-check me-1"></i> اكتمل التوليد — راجع المحتوى واحفظ التعديلات.
            </div>
            @include('admin.lesson-simulators.partials.ai-generate-panel', array_merge(
                \App\Support\SimulatorAiWizard::viewData(),
                [
                    'panelId' => 'sim-ai-review',
                    'defaultTopic' => $simulator->description ?? $simulator->title,
                    'regenerateUrl' => route('admin.lesson-simulators.ai.regenerate', $simulator),
                    'showRegenerateAsync' => true,
                    'collapsed' => true,
                ]
            ))
            @include('admin.lesson-simulators.partials.ai-refine-panel', array_merge(
                \App\Support\SimulatorAiWizard::viewData(),
                ['panelId' => 'sim-ai-refine']
            ))
            @include('admin.lesson-simulators.partials.bundle-form', [
                'action' => route('admin.lesson-simulators.update', $simulator),
                'method' => 'PUT',
                'simulator' => $simulator,
                'bundle' => $bundle,
                'courses' => $courses,
                'statuses' => $statuses,
                'categoryOptions' => $categoryOptions,
            ])
        @else
            <div class="alert alert-light border">لم يبدأ توليد بعد لهذه المحاكاة — استخدم النموذج أدناه.</div>
            @include('admin.lesson-simulators.partials.ai-generate-panel', array_merge(
                \App\Support\SimulatorAiWizard::viewData(),
                [
                    'panelId' => 'sim-ai-review',
                    'defaultTopic' => $simulator->description ?? $simulator->title,
                    'regenerateUrl' => route('admin.lesson-simulators.ai.regenerate', $simulator),
                    'showRegenerateAsync' => true,
                    'collapsed' => false,
                ]
            ))
        @endif
    </div>
</div>
@endsection

@if(in_array($status ?? '', ['queued', 'running'], true))
@push('scripts')
<script>
(function () {
    const statusUrl = @json(route('admin.lesson-simulators.ai.status', $simulator));
    const pollMs = 3000;

    function applyProgress(data) {
        const bar = document.getElementById('ai-polling-bar');
        const pct = document.getElementById('ai-polling-pct');
        const stage = document.getElementById('ai-polling-stage');
        const phases = document.getElementById('ai-polling-phases');
        const p = Math.max(0, Math.min(100, parseInt(data.progress || 0, 10)));
        if (bar) bar.style.width = p + '%';
        if (pct) pct.textContent = p + '%';
        if (stage && data.stage_label) stage.textContent = data.stage_label;
        if (phases && data.phases) phases.textContent = data.phases.done + ' من ' + data.phases.planned + ' ملفات';
    }

    function poll() {
        fetch(statusUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) return;
                applyProgress(data);
                if (['completed', 'failed', 'paused', 'cancelled'].includes(data.status)) {
                    window.location.reload();
                    return;
                }
                setTimeout(poll, pollMs);
            })
            .catch(function () {
                setTimeout(poll, pollMs * 2);
            });
    }

    setTimeout(poll, pollMs);
})();
</script>
@endpush
@endif

@if(in_array($status ?? '', ['queued', 'running'], true))
@push('scripts')
<script>
(function () {
    const btn = document.getElementById('ai-cancel-btn');
    if (!btn) return;
    const cancelUrl = @json(route('admin.lesson-simulators.ai.cancel', $simulator));
    const csrf = @json(csrf_token());

    btn.addEventListener('click', function () {
        if (!confirm('إيقاف التوليد؟ ستبقى الملفات المكتملة محفوظة ويمكن المتابعة لاحقاً.')) return;

        btn.disabled = true;
        const spinner = btn.querySelector('.loading-spinner');
        const textEl = btn.querySelector('.btn-text');
        if (spinner) spinner.classList.add('active');
        if (textEl) textEl.textContent = 'جاري الإيقاف...';

        fetch(cancelUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    alert(data.message || 'تعذّر الإيقاف.');
                    btn.disabled = false;
                    if (spinner) spinner.classList.remove('active');
                    if (textEl) textEl.textContent = 'إيقاف التوليد';
                    return;
                }
                window.location.reload();
            })
            .catch(function () {
                btn.disabled = false;
                if (spinner) spinner.classList.remove('active');
                if (textEl) textEl.textContent = 'إيقاف التوليد';
            });
    });
})();
</script>
@endpush
@endif

@if($resumable ?? false)
@push('scripts')
<script>
(function () {
    const btn = document.getElementById('ai-resume-btn');
    if (!btn) return;
    const resumeUrl = @json(route('admin.lesson-simulators.ai.resume', $simulator));
    const csrf = @json(csrf_token());

    btn.addEventListener('click', function () {
        btn.disabled = true;
        const spinner = btn.querySelector('.loading-spinner');
        const textEl = btn.querySelector('.btn-text');
        if (spinner) spinner.classList.add('active');
        if (textEl) textEl.textContent = 'جاري الاستئناف...';

        fetch(resumeUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    alert(data.message || 'تعذّرت المتابعة.');
                    btn.disabled = false;
                    if (spinner) spinner.classList.remove('active');
                    if (textEl) textEl.textContent = 'متابعة التوليد';
                    return;
                }
                window.location.reload();
            })
            .catch(function () {
                btn.disabled = false;
                if (spinner) spinner.classList.remove('active');
                if (textEl) textEl.textContent = 'متابعة التوليد';
            });
    });
})();
</script>
@endpush
@endif

@if(($status ?? '') === 'completed')
@push('scripts')
<script>
document.addEventListener('simulator-ai-generated', function (e) {
    const data = e.detail || {};
    const htmlEl = document.getElementById('bundle_html');
    const cssEl = document.getElementById('bundle_css');
    const jsEl = document.getElementById('bundle_js');
    const titleEl = document.querySelector('[name="title"]');
    const customPanel = document.getElementById('custom-assets-panel');

    if (titleEl && data.title) titleEl.value = data.title;
    if (htmlEl) htmlEl.value = data.html || '';
    if (cssEl) cssEl.value = data.css || '';
    if (jsEl) jsEl.value = data.js || '';
    if (customPanel && ((data.css && data.css.trim()) || (data.js && data.js.trim()))) {
        customPanel.classList.add('show');
    }
    if (typeof window.simulatorRefreshPreview === 'function') {
        window.simulatorRefreshPreview();
    }
});
</script>
@endpush
@endif
