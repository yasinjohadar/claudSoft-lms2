@extends('student.layouts.master')

@section('page-title')
    تحليلات الأداء
@stop

@section('content')
    <div class="main-content app-content student-quizzes-page">
        <div class="container-fluid">

            <div class="d-md-flex d-block align-items-center justify-content-between my-4">
                <div>
                    <h4 class="student-quizzes-welcome__title mb-1">تحليلات الأداء</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.quizzes.review.index') }}">مراجعة الاختبارات</a></li>
                            <li class="breadcrumb-item active">التحليلات</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('student.quizzes.review.index') }}" class="btn btn-outline-primary rounded-pill btn-sm">
                        <i class="fe fe-arrow-right me-1"></i>العودة
                    </a>
                </div>
            </div>

            @if(isset($overallMetrics))
                @include('student.pages.quizzes.partials.analytics-stats', ['overallMetrics' => $overallMetrics])
            @endif

            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    @include('student.pages.quizzes.partials.analytics-insights', [
                        'title' => 'نقاط القوة',
                        'variant' => 'success',
                        'items' => $topStrengths ?? collect(),
                    ])
                </div>
                <div class="col-lg-6">
                    @include('student.pages.quizzes.partials.analytics-insights', [
                        'title' => 'نقاط الضعف',
                        'variant' => 'danger',
                        'items' => $topWeaknesses ?? collect(),
                    ])
                </div>
            </div>

            @include('student.pages.quizzes.partials.analytics-by-course', ['performanceByCourse' => $performanceByCourse ?? collect()])

            @if(isset($progressOverTime) && count($progressOverTime) > 0)
                <div class="card custom-card student-quizzes-panel">
                    <div class="card-header border-0 pb-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-primary-transparent">
                                <i class="fe fe-trending-up text-primary"></i>
                            </span>
                            <h6 class="card-title mb-0">التقدم مع الوقت</h6>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <div class="student-quiz-analytics-chart-wrap">
                            <canvas id="progressChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
@stop

@section('scripts')
@if(isset($progressOverTime) && count($progressOverTime) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function () {
        function formatNumber(value, decimals) {
            if (decimals) {
                return new Intl.NumberFormat('ar-EG', { minimumFractionDigits: 1, maximumFractionDigits: 1 }).format(value);
            }
            return new Intl.NumberFormat('ar-EG').format(Math.round(value));
        }

        document.querySelectorAll('[data-countup]').forEach(function (el) {
            var target = parseFloat(el.dataset.countup || '0');
            var suffix = el.dataset.countupSuffix || '';
            var decimals = el.dataset.countupDecimals === '1';
            var start = performance.now();

            function step(now) {
                var progress = Math.min((now - start) / 800, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = formatNumber(target * eased, decimals) + suffix;
                if (progress < 1) requestAnimationFrame(step);
            }

            requestAnimationFrame(step);
        });

        var ctx = document.getElementById('progressChart');
        if (!ctx) return;

        var progressData = @json($progressOverTime);
        var primary = getComputedStyle(document.documentElement).getPropertyValue('--primary-rgb').trim() || '5, 85, 162';

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: progressData.map(function (item) { return item.date; }),
                datasets: [{
                    label: 'متوسط النتيجة',
                    data: progressData.map(function (item) { return parseFloat(item.avg_score); }),
                    borderColor: 'rgb(' + primary + ')',
                    backgroundColor: 'rgba(' + primary + ', 0.12)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true } },
                scales: {
                    y: { beginAtZero: true, max: 100 }
                }
            }
        });
    })();
</script>
@else
<script>
    (function () {
        function formatNumber(value, decimals) {
            if (decimals) {
                return new Intl.NumberFormat('ar-EG', { minimumFractionDigits: 1, maximumFractionDigits: 1 }).format(value);
            }
            return new Intl.NumberFormat('ar-EG').format(Math.round(value));
        }

        document.querySelectorAll('[data-countup]').forEach(function (el) {
            var target = parseFloat(el.dataset.countup || '0');
            var suffix = el.dataset.countupSuffix || '';
            var decimals = el.dataset.countupDecimals === '1';
            var start = performance.now();

            function step(now) {
                var progress = Math.min((now - start) / 800, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = formatNumber(target * eased, decimals) + suffix;
                if (progress < 1) requestAnimationFrame(step);
            }

            requestAnimationFrame(step);
        });
    })();
</script>
@endif
@stop
