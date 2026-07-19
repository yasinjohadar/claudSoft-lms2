@extends('student.layouts.master')

@section('page-title')
    تسجيلاتي في المعسكرات
@stop

@section('content')
<div class="main-content app-content student-camp-enrollments-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4">
            <div>
                <h4 class="student-my-courses-welcome__title mb-1">تسجيلاتي في المعسكرات</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.training-camps.index') }}">التسجيل على المعسكرات</a></li>
                        <li class="breadcrumb-item active">تسجيلاتي</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('student.training-camps.index') }}" class="btn btn-sm btn-primary-light">
                    <i class="fe fe-flag me-1"></i>التسجيل على المعسكرات
                </a>
            </div>
        </div>

        @include('student.pages.training-camps.partials.enrollments-stats', ['stats' => $stats])

        <div class="card custom-card student-quizzes-panel">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="avatar avatar-sm bg-primary-transparent">
                        <i class="fe fe-list text-primary"></i>
                    </span>
                    <h6 class="card-title mb-0">تسجيلاتي</h6>
                </div>

                @include('student.pages.training-camps.partials.enrollments-filters')

                @if($enrollments->count() > 0)
                    <div class="row g-4">
                        @foreach($enrollments as $index => $enrollment)
                            @include('student.pages.training-camps.partials.enrollment-card', [
                                'enrollment' => $enrollment,
                                'index' => $index,
                            ])
                        @endforeach
                    </div>

                    @if($enrollments->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $enrollments->appends(request()->query())->links() }}
                        </div>
                    @endif
                @else
                    @include('student.pages.training-camps.partials.enrollments-empty')
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
