@extends('student.layouts.master')

@section('page-title')
    اختباراتي
@stop

@section('content')
    <div class="main-content app-content student-quizzes-page">
        <div class="container-fluid">

            @include('student.components.alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between my-4">
                <div>
                    <h4 class="student-quizzes-welcome__title mb-1">اختباراتي</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">اختباراتي</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0 d-flex flex-wrap gap-2">
                    <a href="{{ route('student.quizzes.index') }}" class="btn btn-primary rounded-pill">
                        <i class="fe fe-plus me-1"></i>الاختبارات المتاحة
                    </a>
                    <a href="{{ route('student.quizzes.review.analytics') }}" class="btn btn-outline-primary rounded-pill">
                        <i class="fe fe-bar-chart-2 me-1"></i>التحليلات
                    </a>
                </div>
            </div>

            @include('student.pages.quizzes.partials.review-stats', ['stats' => $stats])

            @include('student.pages.quizzes.partials.review-filters', ['quizzes' => $quizzes ?? collect()])

            <div class="card custom-card student-quizzes-panel">
                <div class="card-header border-0 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <span class="avatar avatar-sm bg-primary-transparent">
                            <i class="fe fe-clipboard text-primary"></i>
                        </span>
                        <h6 class="card-title mb-0">جميع المحاولات</h6>
                    </div>
                </div>
                <div class="card-body pt-3">
                    @include('student.pages.quizzes.partials.review-attempts-table', ['attempts' => $attempts ?? collect()])

                    <div class="row g-3 d-lg-none">
                        @forelse($attempts ?? [] as $index => $attempt)
                            @include('student.pages.quizzes.partials.review-attempt-card', [
                                'attempt' => $attempt,
                                'index' => $index,
                            ])
                        @empty
                            <div class="col-12">
                                @include('student.pages.quizzes.partials.review-attempts-empty')
                            </div>
                        @endforelse
                    </div>

                    @if(isset($attempts) && $attempts->hasPages())
                        <div class="d-flex justify-content-center mt-4 pt-2 border-top">
                            {{ $attempts->appends(request()->query())->links() }}
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
        function formatNumber(value, decimals) {
            if (decimals) {
                return new Intl.NumberFormat('ar-EG', {
                    minimumFractionDigits: 1,
                    maximumFractionDigits: 1,
                }).format(value);
            }
            return new Intl.NumberFormat('ar-EG').format(Math.round(value));
        }

        document.querySelectorAll('[data-countup]').forEach(function (el) {
            var target = parseFloat(el.dataset.countup || '0');
            var isPercent = el.dataset.countupSuffix === '%';
            var decimals = el.dataset.countupDecimals === '1';
            var duration = 800;
            var start = performance.now();

            function step(now) {
                var progress = Math.min((now - start) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                var value = formatNumber(target * eased, decimals);
                el.textContent = isPercent ? value + '%' : value;
                if (progress < 1) requestAnimationFrame(step);
            }

            requestAnimationFrame(step);
        });
    })();
</script>
@stop
