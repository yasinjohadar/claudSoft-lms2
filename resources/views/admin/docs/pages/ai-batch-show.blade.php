@extends('admin.layouts.master')

@section('page-title', 'تفاصيل دفعة توليد التوثيق')

@section('styles')
@include('admin.docs.categories.partials.styles')
@include('admin.docs.pages.partials.ai-page-styles')
@include('admin.docs.pages.partials.ai-batch-styles')
<style>
    /* "Incomplete only" is a view filter over the same live table. */
    body.doc-ai-only-incomplete #batchItemsBody tr:not([data-incomplete="1"]) { display: none; }
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
                    <li class="breadcrumb-item"><a href="{{ route('admin.docs.ai-pages.batch.index') }}">سجل الدفعات</a></li>
                    <li class="breadcrumb-item active">تفاصيل الدفعة</li>
                </ol>
            </nav>
        </div>

        <div class="group-show-hero dashboard-fade-in doc-ai-animate mb-4">
            <div class="row align-items-start g-3">
                <div class="col-lg-8">
                    <span class="group-show-hero__eyebrow">
                        <i class="fe fe-activity me-1"></i>
                        دفعة توليد
                    </span>
                    <h2 class="group-show-hero__title mb-2">
                        {{ $batch->total }} موضوعاً — {{ $batch->created_at?->format('Y-m-d H:i') }}
                    </h2>
                    <p class="group-show-hero__desc mb-0">
                        القسم: <strong>{{ $category->name ?? '—' }}</strong>
                        · الطول: <strong>{{ ['short' => 'قصير', 'medium' => 'متوسط', 'long' => 'طويل'][$batch->settings['content_length'] ?? 'medium'] ?? '—' }}</strong>
                        · المحرك: <strong>{{ ($batch->settings['docs_engine'] ?? 'legacy') === 'laravel_ai' ? 'Laravel AI SDK' : 'موديلات قديمة' }}</strong>
                    </p>
                </div>
                <div class="col-lg-4">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="{{ route('admin.docs.ai-pages.batch.index') }}" class="btn btn-light border">
                            <i class="fe fe-arrow-right me-1"></i>سجل الدفعات
                        </a>
                        <a href="{{ route('admin.docs.ai-pages.batch.create') }}" class="btn btn-outline-secondary">
                            <i class="fe fe-plus-circle me-1"></i>دفعة جديدة
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card doc-ai-panel doc-cat-table-card doc-ai-animate">
            <div class="card-header doc-ai-panel__header border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="doc-ai-panel__title mb-0">
                    <span class="doc-ai-panel__title-icon doc-ai-panel__title-icon--content"><i class="fe fe-list"></i></span>
                    مواضيع الدفعة
                </h6>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="onlyIncompleteToggle">
                    <label class="form-check-label" for="onlyIncompleteToggle">أظهر غير المكتملة فقط</label>
                </div>
            </div>
            <div class="card-body pt-2">
                @include('admin.docs.pages.partials.ai-batch-progress')
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
@include('admin.docs.pages.partials.ai-batch-scripts')
<script>
(function () {
    const initialBatch = @json($initialBatch);

    document.addEventListener('DOMContentLoaded', function () {
        // This page is already addressed by uuid, so no ?batch= mirroring.
        window.DocAiBatch.attach({ initialBatch: initialBatch, mirrorUrl: false });

        const toggle = document.getElementById('onlyIncompleteToggle');
        const KEY = 'docAiBatchOnlyIncomplete';

        function apply(on) {
            document.body.classList.toggle('doc-ai-only-incomplete', on);
        }

        let saved = false;
        try { saved = sessionStorage.getItem(KEY) === '1'; } catch (e) {}
        toggle.checked = saved;
        apply(saved);

        toggle.addEventListener('change', function () {
            apply(toggle.checked);
            try { sessionStorage.setItem(KEY, toggle.checked ? '1' : '0'); } catch (e) {}
        });
    });
})();
</script>
@endsection
