@extends('student.layouts.master')

@section('page-title')
    المجموعات المتاحة
@stop

@section('content')
<div class="main-content app-content student-groups-page">
    <div class="container-fluid pb-3">

        @include('student.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4">
            <div class="min-w-0">
                <h4 class="student-my-courses-welcome__title mb-1">المجموعات المتاحة</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item">الانضمامات</li>
                        <li class="breadcrumb-item active">المجموعات المتاحة</li>
                    </ol>
                </nav>
                <p class="text-muted fs-13 mb-0 mt-2">تصفّح المجموعات المفتوحة وقدّم طلب انضمام للإدارة</p>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
                <span class="badge bg-primary-transparent fs-12 px-3 py-2">
                    {{ $groups->total() }} مجموعة
                </span>
                <a href="{{ route('student.groups.my-requests') }}" class="btn btn-outline-primary rounded-pill">
                    <i class="fe fe-list me-1"></i>طلباتي
                </a>
            </div>
        </div>

        @include('student.groups.partials.groups-stats', ['stats' => $stats])

        @include('student.groups.partials.groups-filters', ['courses' => $courses])

        <div class="card custom-card student-quizzes-panel">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="avatar avatar-sm bg-primary-transparent">
                        <i class="fe fe-users text-primary"></i>
                    </span>
                    <h6 class="card-title mb-0">المجموعات المفتوحة للانضمام</h6>
                </div>

                @include('student.groups.partials.groups-grid', ['groups' => $groups])
            </div>
        </div>

    </div>
</div>
@stop

@push('scripts')
<script>
(function () {
    function formatNumber(value) {
        return new Intl.NumberFormat('ar-EG').format(Math.round(value));
    }

    function animateCount(el, target) {
        if (!el) return;
        var duration = 600;
        var start = performance.now();
        function step(now) {
            var progress = Math.min((now - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = formatNumber(target * eased);
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    document.querySelectorAll('[data-countup]').forEach(function (el) {
        animateCount(el, parseFloat(el.dataset.countup || '0'));
    });
})();
</script>
@endpush
