@extends('admin.layouts.master')

@section('page-title')
    مراجعة توليد المحاكاة
@stop

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
            $status = $generationStatus ?? ($generationMeta['status'] ?? 'unknown');
        @endphp

        @if(in_array($status, ['pending', 'processing'], true))
            <div class="card shadow-sm border-0 mb-4" id="ai-polling-card">
                <div class="card-body text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <h5>جاري التوليد في الخلفية...</h5>
                    <p class="text-muted mb-0" id="ai-polling-message">قد يستغرق 2–5 دقائق. لا تغلق الصفحة.</p>
                </div>
            </div>
        @elseif($status === 'failed')
            <div class="alert alert-danger">
                <strong>فشل التوليد:</strong>
                {{ $generationMeta['error'] ?? 'خطأ غير معروف' }}
            </div>
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
            <div class="alert alert-warning">حالة التوليد غير معروفة ({{ $status }}).</div>
        @endif
    </div>
</div>
@endsection

@if(in_array($status ?? '', ['pending', 'processing'], true))
@push('scripts')
<script>
(function () {
    const statusUrl = @json(route('admin.lesson-simulators.ai.status', $simulator));
    const pollMs = 4000;

    function poll() {
        fetch(statusUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) return;
                if (data.status === 'completed' || data.status === 'failed') {
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
