@extends('admin.layouts.master')

@section('page-title')
    توليد محاكاة بالذكاء الاصطناعي
@stop

@section('styles')
@include('admin.docs.pages.partials.ai-page-styles')
@endsection

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="group-show-hero dashboard-fade-in doc-ai-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-zap me-1"></i>
                        توليد بالذكاء الاصطناعي
                    </span>
                    <h2 class="group-show-hero__title mb-2">توليد محاكاة HTML/CSS/JS بالذكاء الاصطناعي</h2>
                    <p class="group-show-hero__desc mb-2">
                        يخطط أولاً ثم يولّد <strong>HTML وCSS وJS</strong> على مراحل متتابعة — راجع النتيجة ثم احفظها، أو استخدم Queue للمواضيع الطويلة.
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('admin.lesson-simulators.create') }}" class="btn btn-outline-primary">
                            <i class="fe fe-edit-3 me-1"></i>إنشاء يدوي
                        </a>
                        <a href="{{ route('admin.lesson-simulators.index') }}" class="btn btn-light border">
                            <i class="fe fe-list me-1"></i>رجوع
                        </a>
                    </div>
                </div>
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
