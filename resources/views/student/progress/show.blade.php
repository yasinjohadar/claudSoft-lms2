@extends('student.layouts.master')

@section('page-title')
    تقدمي - {{ $course->title }}
@stop

@section('content')
<div class="main-content app-content student-progress-show-page">
    <div class="container-fluid">

        @include('student.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4">
            <div class="min-w-0">
                <h4 class="student-my-courses-welcome__title mb-1 text-truncate">
                    تقرير التقدم — {{ $course->title }}
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.progress.overview') }}">تقدمي في الكورسات</a></li>
                        <li class="breadcrumb-item active text-truncate">{{ $course->title }}</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
                <a href="{{ route('student.progress.ai-reports.index', $course) }}"
                   class="btn btn-outline-primary rounded-pill">
                    <i class="fe fe-cpu me-1"></i>تقارير الدراسة (AI)
                </a>
                @if($stats['can_get_certificate'])
                    <a href="{{ route('student.progress.certificate', $course->id) }}"
                       class="btn btn-success rounded-pill">
                        <i class="fe fe-award me-1"></i>تحميل الشهادة
                    </a>
                @endif
            </div>
        </div>

        @include('student.progress.partials.show-stats', ['stats' => $stats])

        <div class="card custom-card student-my-courses-panel mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="avatar avatar-sm bg-primary-transparent">
                        <i class="fe fe-trending-up text-primary"></i>
                    </span>
                    <h6 class="card-title mb-0">التقدم الإجمالي</h6>
                </div>

                @php $overallPct = (float) ($stats['completion_percentage'] ?? 0); @endphp

                <div class="student-progress-overall">
                    <div class="d-flex justify-content-between align-items-center student-progress-overall__header">
                        <span class="text-muted">نسبة الإكمال</span>
                        <span class="student-progress-overall__value">{{ number_format($overallPct, 1) }}%</span>
                    </div>
                    <div class="student-progress-overall__track">
                        <div class="student-progress-overall__bar"
                             style="width: {{ max(0, min(100, $overallPct)) }}%"
                             role="progressbar"
                             aria-valuenow="{{ $overallPct }}"
                             aria-valuemin="0"
                             aria-valuemax="100"></div>
                    </div>
                </div>

                @if($stats['can_get_certificate'])
                    <div class="student-progress-certificate-callout mt-4">
                        <i class="fe fe-award"></i>
                        <div>
                            <strong>مبروك!</strong>
                            <span class="text-muted">أنت مؤهل للحصول على شهادة إتمام هذا الكورس.</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card custom-card student-my-courses-panel mb-4">
            <div class="card-body pb-lg-0">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <span class="avatar avatar-sm bg-primary-transparent">
                        <i class="fe fe-list text-primary"></i>
                    </span>
                    <h6 class="card-title mb-0">التقدم حسب الأقسام</h6>
                </div>

                @include('student.progress.partials.show-sections-table', ['sectionsProgress' => $sectionsProgress])

                <div class="row g-3 d-lg-none pb-3">
                    @foreach($sectionsProgress as $index => $sectionData)
                        @include('student.progress.partials.show-section-card', [
                            'sectionData' => $sectionData,
                            'index' => $index,
                        ])
                    @endforeach
                </div>
            </div>
        </div>

        @include('student.progress.partials.show-recent-completions', ['recentCompletions' => $recentCompletions])

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
