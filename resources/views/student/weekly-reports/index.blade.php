@extends('student.layouts.master')

@section('page-title')
التقارير الأسبوعية
@stop

@section('content')
<div class="main-content app-content student-weekly-reports-page">
    <div class="container-fluid pb-3">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        <div class="d-md-flex d-block align-items-center justify-content-between my-4">
            <div class="min-w-0">
                <h4 class="student-my-courses-welcome__title mb-1">التقارير الأسبوعية</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">التقارير الأسبوعية</li>
                    </ol>
                </nav>
                <p class="text-muted fs-13 mb-0 mt-2">تابع تقاريرك الأسبوعية، سلّمها في الموعد، واطّلع على رد الإدارة.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
                <span class="badge bg-primary-transparent fs-12 px-3 py-2">
                    {{ $stats['total'] ?? 0 }} تقرير
                </span>
            </div>
        </div>

        @include('student.weekly-reports.partials.stats', ['stats' => $stats])

        <div class="card custom-card student-quizzes-panel">
            <div class="card-header border-0 pb-0">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm bg-primary-transparent">
                            <i class="fe fe-clipboard text-primary"></i>
                        </span>
                        <div>
                            <h6 class="card-title mb-1">تقاريري الأسبوعية</h6>
                            <p class="fs-12 text-muted mb-0">اضغط «فتح التقرير» لكتابة التفاصيل واختيار الدروس والإرسال.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body pt-3">
                @if($reports->isNotEmpty())
                    @include('student.weekly-reports.partials.reports-table', ['reports' => $reports])

                    @if($reports->hasPages())
                        <div class="d-flex justify-content-center mt-4 pt-2 border-top">
                            {{ $reports->links() }}
                        </div>
                    @endif
                @else
                    @include('student.weekly-reports.partials.empty-state')
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
            var target = parseFloat(el.dataset.countup || '0');
            var duration = 700;
            var start = performance.now();

            function step(now) {
                var progress = Math.min((now - start) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = formatNumber(target * eased);
                if (progress < 1) {
                    requestAnimationFrame(step);
                }
            }

            requestAnimationFrame(step);
        });
    })();
</script>
@endpush
