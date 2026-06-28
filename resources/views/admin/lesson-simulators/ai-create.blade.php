@extends('admin.layouts.master')

@section('page-title')
    توليد محاكاة بالذكاء الاصطناعي
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h5 class="page-title fs-21 mb-0">توليد محاكاة HTML/CSS/JS بالذكاء الاصطناعي</h5>
                <p class="text-muted small mb-0">ولّد المحاكاة، راجعها، ثم احفظها — أو استخدم Queue للمواضيع الطويلة.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.lesson-simulators.create') }}" class="btn btn-outline-secondary btn-sm">إنشاء يدوي</a>
                <a href="{{ route('admin.lesson-simulators.index') }}" class="btn btn-secondary btn-sm">رجوع</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @include('admin.lesson-simulators.partials.ai-generate-panel', array_merge(
            \App\Support\SimulatorAiWizard::viewData(),
            [
                'panelId' => 'sim-ai-create',
                'asyncUrl' => route('admin.lesson-simulators.ai.store'),
                'showAsync' => true,
            ]
        ))

        <div id="ai-result-section" class="d-none">
            <div class="alert alert-success">
                <i class="fe fe-check-circle me-1"></i>
                تم التوليد — راجع الكود أدناه ثم احفظ.
            </div>
            @include('admin.lesson-simulators.partials.bundle-form', [
                'action' => route('admin.lesson-simulators.store'),
                'method' => 'POST',
                'simulator' => null,
                'bundle' => $bundle,
                'courses' => $courses,
                'statuses' => $statuses,
                'categoryOptions' => $categoryOptions,
            ])
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('simulator-ai-generated', function (e) {
    const data = e.detail || {};
    const section = document.getElementById('ai-result-section');
    if (section) section.classList.remove('d-none');

    const titleEl = document.querySelector('[name="title"]');
    const descEl = document.querySelector('[name="description"]');
    const htmlEl = document.getElementById('bundle_html');
    const cssEl = document.getElementById('bundle_css');
    const jsEl = document.getElementById('bundle_js');
    const customPanel = document.getElementById('custom-assets-panel');

    if (titleEl && data.title) titleEl.value = data.title;
    if (descEl && data.description) descEl.value = data.description;
    if (htmlEl) htmlEl.value = data.html || '';
    if (cssEl) cssEl.value = data.css || '';
    if (jsEl) jsEl.value = data.js || '';

    if (customPanel && ((data.css && data.css.trim()) || (data.js && data.js.trim()))) {
        customPanel.classList.add('show');
    }

    section?.scrollIntoView({ behavior: 'smooth', block: 'start' });

    if (typeof window.simulatorRefreshPreview === 'function') {
        window.simulatorRefreshPreview();
    }
});
</script>
@endpush
