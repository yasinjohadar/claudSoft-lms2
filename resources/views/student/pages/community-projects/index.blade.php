@extends('student.layouts.master')

@section('page-title')
    مجتمع المشاريع
@stop

@section('content')
<div class="main-content app-content student-community-projects-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="my-4 page-header-breadcrumb admin-dashboard-welcome dashboard-fade-in">
            <nav class="mb-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item">الواجبات والتحديات</li>
                    <li class="breadcrumb-item active">مجتمع المشاريع</li>
                </ol>
            </nav>
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <h4 class="mb-1 admin-dashboard-welcome__title">معرض المشاريع</h4>
                    <p class="mb-0 text-muted admin-dashboard-welcome__subtitle">
                        استكشف مشاريع الطلاب المنشورة وتعلّم من أعمال زملائك.
                    </p>
                </div>
                <a href="{{ route('student.project-challenges.index') }}" class="btn btn-outline-primary rounded-pill">
                    <i class="fe fe-layers me-1"></i>تحديات المشاريع
                </a>
            </div>
        </div>

        @include('student.pages.community-projects.partials.index-stats', ['stats' => $stats ?? []])

        <div class="card custom-card group-show-members-card dashboard-fade-in mb-4">
            <div class="card-header border-0 pb-0">
                <h4 class="card-title mb-1">تصفية المشاريع</h4>
                <p class="fs-12 text-muted mb-0">ابحث بالعنوان أو فلتر حسب التحدي.</p>
            </div>
            <div class="card-body pt-3">
                @include('student.pages.community-projects.partials.index-filters', ['challenges' => $challenges ?? collect()])
            </div>
        </div>

        <div class="card custom-card group-show-members-card dashboard-fade-in">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                <h6 class="group-show-members-card__title mb-0">
                    المشاريع المنشورة
                    <span class="group-show-members-card__count">{{ $showcases->total() }}</span>
                </h6>
            </div>
            <div class="card-body pt-3">
                <div class="row g-4">
                    @forelse($showcases as $index => $showcase)
                        @include('student.pages.community-projects.partials.showcase-card', [
                            'showcase' => $showcase,
                            'index' => $index,
                        ])
                    @empty
                        <div class="col-12">
                            <div class="group-show-empty py-5">
                                <i class="fe fe-globe group-show-empty__icon"></i>
                                <h5 class="group-show-empty__title">لا توجد مشاريع منشورة بعد</h5>
                                <p class="group-show-empty__desc mb-3">
                                    @if(request()->hasAny(['q', 'challenge_id']))
                                        جرّب تعديل البحث أو الفلاتر.
                                    @else
                                        عندما ينشر الطلاب مشاريعهم ستظهر هنا.
                                    @endif
                                </p>
                                @if(request()->hasAny(['q', 'challenge_id']))
                                    <a href="{{ route('student.community-projects.index') }}" class="btn btn-primary btn-sm rounded-pill">
                                        <i class="fe fe-rotate-cw me-1"></i>إعادة تعيين
                                    </a>
                                @else
                                    <a href="{{ route('student.project-challenges.index') }}" class="btn btn-primary btn-sm rounded-pill">
                                        <i class="fe fe-layers me-1"></i>تصفّح التحديات
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforelse
                </div>

                @if($showcases->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $showcases->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    function formatNumber(value) {
        return new Intl.NumberFormat('ar-EG').format(Math.round(value));
    }

    document.querySelectorAll('[data-countup]').forEach(function (el) {
        const target = parseFloat(el.dataset.countup || '0');
        const duration = 900;
        const start = performance.now();

        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = formatNumber(target * eased);
            if (progress < 1) requestAnimationFrame(step);
        }

        requestAnimationFrame(step);
    });
})();
</script>
@endpush
