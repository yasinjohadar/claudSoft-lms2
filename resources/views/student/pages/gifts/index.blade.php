@extends('student.layouts.master')

@section('page-title')
    هدايا الأكاديمية
@stop

@section('content')
<div class="main-content app-content student-gifts-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4">
            <div>
                <h4 class="student-my-courses-welcome__title mb-1">هدايا الأكاديمية</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">هدايا الأكاديمية</li>
                    </ol>
                </nav>
                <p class="text-muted fs-13 mb-0 mt-2">الهدايا والموارد التي تمنحها لك الأكاديمية</p>
            </div>
            <div class="mt-3 mt-md-0">
                <span class="badge bg-primary-transparent fs-12 px-3 py-2">
                    {{ $recipients->total() }} هدية
                </span>
            </div>
        </div>

        @include('student.pages.gifts.partials.gifts-stats', ['stats' => $stats])

        <div class="card custom-card student-quizzes-panel">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="avatar avatar-sm bg-warning-transparent">
                        <i class="ri ri-gift-line text-warning"></i>
                    </span>
                    <div>
                        <h6 class="card-title mb-0">هداياي من الأكاديمية</h6>
                        <p class="text-muted fs-12 mb-0">معاينة أو تحميل الهدايا الممنوحة لك</p>
                    </div>
                </div>

                @include('student.pages.gifts.partials.grid', ['recipients' => $recipients])

                @if($recipients->hasPages())
                    <div class="mt-4">{{ $recipients->links() }}</div>
                @endif
            </div>
        </div>

    </div>
</div>
@stop

@push('scripts')
<script>
(function () {
    function animateCount(el, target) {
        if (!el) return;
        const duration = 600;
        const start = performance.now();
        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = new Intl.NumberFormat('ar-EG').format(Math.round(target * eased));
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    document.querySelectorAll('.student-gifts-page [data-countup]').forEach(function (el) {
        animateCount(el, parseFloat(el.dataset.countup || '0'));
    });
})();
</script>
@endpush
