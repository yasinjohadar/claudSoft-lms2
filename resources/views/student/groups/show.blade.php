@extends('student.layouts.master')

@section('page-title')
    تفاصيل المجموعة - {{ $group->name }}
@stop

@section('content')
<div class="main-content app-content student-group-show-page">
    <div class="container-fluid pb-3">

        @include('student.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4">
            <div class="min-w-0">
                <h4 class="student-my-courses-welcome__title mb-1">تفاصيل المجموعة</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item">الانضمامات</li>
                        <li class="breadcrumb-item"><a href="{{ route('student.groups.index') }}">المجموعات</a></li>
                        <li class="breadcrumb-item active">{{ $group->name }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
                <a href="{{ route('student.groups.my-requests') }}" class="btn btn-outline-secondary rounded-pill">
                    <i class="fe fe-list me-1"></i>طلباتي
                </a>
                <a href="{{ route('student.groups.index') }}" class="btn btn-outline-primary rounded-pill">
                    <i class="fe fe-arrow-right me-1"></i>المجموعات المتاحة
                </a>
            </div>
        </div>

        @include('student.groups.partials.show-stats', ['group' => $group])

        <div class="row align-items-start g-4 student-group-show-content-row">
            <div class="col-lg-8">
                @include('student.groups.partials.show-details', ['group' => $group])
            </div>
            <div class="col-lg-4">
                @include('student.groups.partials.show-membership', [
                    'group' => $group,
                    'canRequest' => $canRequest,
                    'hasPendingRequest' => $hasPendingRequest,
                ])
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
