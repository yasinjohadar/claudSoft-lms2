@extends('student.layouts.master')

@section('page-title')
    الاختبارات المتاحة
@stop

@section('content')
    <div class="main-content app-content student-quizzes-page">
        <div class="container-fluid">

            @include('student.components.alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between my-4">
                <div>
                    <h4 class="student-quizzes-welcome__title mb-1">الاختبارات المتاحة</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">الاختبارات المتاحة</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('student.quizzes.review.index') }}" class="btn btn-outline-primary rounded-pill">
                        <i class="fe fe-clock me-1"></i>محاولاتي السابقة
                    </a>
                </div>
            </div>

            @include('student.pages.quizzes.partials.available-stats', ['stats' => $stats ?? []])

            @include('student.pages.quizzes.partials.available-filters', ['courses' => $courses ?? collect()])

            <div class="card custom-card student-quizzes-panel">
                <div class="card-header border-0 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm bg-primary-transparent">
                            <i class="fe fe-clipboard text-primary"></i>
                        </span>
                        <h6 class="card-title mb-0">قائمة الاختبارات</h6>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <div class="row g-4">
                        @forelse($quizzes as $index => $quiz)
                            @include('student.pages.quizzes.partials.available-quiz-card', [
                                'quiz' => $quiz,
                                'index' => $index,
                            ])
                        @empty
                            @include('student.pages.quizzes.partials.available-empty')
                        @endforelse
                    </div>

                    @if($quizzes->hasPages())
                        <div class="d-flex justify-content-center mt-4 pt-2 border-top">
                            {{ $quizzes->appends(request()->query())->links() }}
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
            var suffix = el.dataset.countupSuffix || '';
            var duration = 800;
            var start = performance.now();

            function step(now) {
                var progress = Math.min((now - start) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = formatNumber(target * eased) + suffix;
                if (progress < 1) requestAnimationFrame(step);
            }

            requestAnimationFrame(step);
        });
    })();
</script>
@stop
