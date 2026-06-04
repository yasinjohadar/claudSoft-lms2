@extends('student.layouts.master')

@section('page-title')
    تقارير الدراسة
@stop

@section('content')
<div class="main-content app-content student-study-reports-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4">
            <div>
                <h4 class="student-my-courses-welcome__title mb-1">تقارير الدراسة (AI)</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">تقارير الدراسة</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('student.progress.overview') }}" class="btn btn-outline-primary rounded-pill">
                    <i class="fe fe-trending-up me-1"></i>تقدمي في الكورسات
                </a>
            </div>
        </div>

        @include('student.study-reports.partials.study-reports-stats', ['stats' => $stats])

        <div class="student-study-reports-info mb-4">
            <i class="fe fe-info"></i>
            <p class="mb-0">هنا تجد كل التقارير الصادرة لك. يمكنك أيضاً فتح صفحة التقارير لكل كورس من القائمة أدناه.</p>
        </div>

        @include('student.study-reports.partials.study-reports-quick-links', ['enrolledCourses' => $enrolledCourses])

        <div class="card custom-card student-quizzes-panel">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm bg-primary-transparent">
                            <i class="fe fe-list text-primary"></i>
                        </span>
                        <h6 class="card-title mb-0">كل التقارير</h6>
                    </div>
                </div>

                @include('student.study-reports.partials.study-reports-table', ['reports' => $reports])

                <div class="row g-3 d-lg-none">
                    @forelse($reports as $index => $report)
                        @include('student.study-reports.partials.study-reports-card', [
                            'report' => $report,
                            'index' => $index,
                        ])
                    @empty
                        <div class="col-12">
                            @include('student.study-reports.partials.study-reports-empty')
                        </div>
                    @endforelse
                </div>

                @if($reports->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $reports->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@stop

@section('scripts')
<script>
    (function () {
        function formatNumber(value) {
            return new Intl.NumberFormat('ar-EG').format(Math.round(value));
        }

        document.querySelectorAll('[data-countup]').forEach(function (el) {
            var target = parseFloat(el.dataset.countup || '0');
            var duration = 800;
            var start = performance.now();

            function step(now) {
                var progress = Math.min((now - start) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = formatNumber(target * eased);
                if (progress < 1) requestAnimationFrame(step);
            }

            requestAnimationFrame(step);
        });
    })();
</script>
@stop
