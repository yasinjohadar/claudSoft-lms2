@extends('student.layouts.master')

@section('page-title')
    واجباتي
@stop

@section('content')
<div class="main-content app-content student-assignments-page">
    <div class="container-fluid pb-3">

        @include('student.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4 dashboard-fade-in">
            <div class="min-w-0">
                <h4 class="student-my-courses-welcome__title mb-1">واجباتي</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">واجباتي</li>
                    </ol>
                </nav>
                <p class="text-muted fs-13 mb-0 mt-2">تابع واجباتك، سلّمها في الموعد، واطّلع على تقييم المدرّس.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
                <span class="badge bg-primary-transparent fs-12 px-3 py-2 align-self-center">
                    {{ $stats['total'] }} واجب
                </span>
            </div>
        </div>

        @include('student.assignments.partials.stats', ['stats' => $stats])

        @if($stats['pending'] > 0 || $stats['overdue'] > 0)
            <div class="row g-3 mb-4">
                @if($stats['pending'] > 0)
                    <div class="col-md-6">
                        <div class="alert alert-primary border-0 d-flex align-items-center gap-2 mb-0 dashboard-fade-in">
                            <i class="fe fe-info fs-18"></i>
                            <span>لديك <strong class="mx-1">{{ $stats['pending'] }}</strong> واجب بانتظار التسليم</span>
                        </div>
                    </div>
                @endif
                @if($stats['overdue'] > 0)
                    <div class="col-md-6">
                        <div class="alert alert-danger border-0 d-flex align-items-center gap-2 mb-0 dashboard-fade-in">
                            <i class="fe fe-alert-triangle fs-18"></i>
                            <span>لديك <strong class="mx-1">{{ $stats['overdue'] }}</strong> واجب متأخر</span>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        @if(count($courseStats) > 0)
            <div class="card custom-card student-quizzes-panel dashboard-fade-in mb-4">
                <div class="card-header border-0 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm bg-cyan-transparent">
                            <i class="fe fe-bar-chart-2 text-info"></i>
                        </span>
                        <div>
                            <h6 class="card-title mb-1">إحصائيات الواجبات حسب الكورس</h6>
                            <p class="fs-12 text-muted mb-0">ملخص أدائك في كل كورس</p>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-3">
                    @include('student.assignments.partials.course-stats-table', compact('courseStats', 'stats'))
                </div>
            </div>
        @endif

        <div class="card custom-card student-quizzes-panel dashboard-fade-in">
            <div class="card-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar avatar-sm bg-warning-transparent">
                        <i class="fe fe-clipboard text-warning"></i>
                    </span>
                    <div>
                        <h6 class="card-title mb-1">قائمة الواجبات</h6>
                        <p class="fs-12 text-muted mb-0">اضغط «تسليم» أو «عرض» لفتح تفاصيل الواجب</p>
                    </div>
                </div>
            </div>
            <div class="card-body pt-3">
                @if(count($assignmentsData) > 0)
                    @include('student.assignments.partials.assignments-table', compact('assignmentsData'))
                @else
                    @include('student.assignments.partials.empty-state')
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@push('scripts')
<script>
(function () {
    function formatNumber(value, suffix) {
        return new Intl.NumberFormat('ar-EG').format(Math.round(value)) + (suffix || '');
    }

    document.querySelectorAll('[data-countup]').forEach(function (el) {
        var target = parseFloat(el.dataset.countup || '0');
        var suffix = el.dataset.countupSuffix || '';
        var duration = 700;
        var start = performance.now();

        function step(now) {
            var progress = Math.min((now - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = formatNumber(target * eased, suffix);
            if (progress < 1) {
                requestAnimationFrame(step);
            }
        }

        requestAnimationFrame(step);
    });
})();
</script>
@endpush
