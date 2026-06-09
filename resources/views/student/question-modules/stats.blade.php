@extends('student.layouts.master')

@section('page-title')
    إحصائيات الاختبارات
@stop

@section('styles')
<style>
    @media print {
        .no-print,
        .app-header,
        .app-sidebar,
        .page-header-breadcrumb,
        .student-qm-stats-hero {
            display: none !important;
        }

        .student-qm-stats-page .group-show-members-card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
    }
</style>
@stop

@section('content')
<div class="main-content app-content student-qm-stats-page">
    <div class="container-fluid pb-3">

        @include('student.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4 no-print student-qm-stats-hero">
            <div class="min-w-0">
                <h4 class="student-my-courses-welcome__title mb-1">إحصائيات الاختبارات</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">إحصائيات الاختبارات</li>
                    </ol>
                </nav>
                <p class="text-muted fs-13 mb-0 mt-2">عرض شامل لأدائك في جميع الاختبارات</p>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
                <button type="button" onclick="window.print()" class="btn btn-primary rounded-pill">
                    <i class="fe fe-printer me-1"></i>طباعة التقرير
                </button>
            </div>
        </div>

        @include('student.question-modules.partials.stats-kpi', compact(
            'totalAttempts', 'passedAttempts', 'averageScore', 'totalHours'
        ))

        <div class="row align-items-start g-3 student-qm-stats-content-row">
            <div class="col-xl-8 col-lg-7 d-flex flex-column gap-3">
                <div class="card custom-card group-show-members-card dashboard-fade-in">
                    <div class="card-header border-0 pb-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-primary-transparent">
                                <i class="fe fe-trending-up text-primary"></i>
                            </span>
                            <div>
                                <h4 class="card-title mb-1">الأداء خلال آخر 30 يوم</h4>
                                <p class="fs-12 text-muted mb-0">متوسط النسبة المئوية لمحاولاتك اليومية.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <div class="student-quiz-analytics-chart-wrap">
                            <canvas id="performanceChart" height="100"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card custom-card group-show-members-card dashboard-fade-in">
                    <div class="card-header border-0 pb-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm bg-info-transparent">
                                <i class="fe fe-pie-chart text-info"></i>
                            </span>
                            <div>
                                <h4 class="card-title mb-1">توزيع الدرجات</h4>
                                <p class="fs-12 text-muted mb-0">توزيع محاولاتك حسب فئات التقدير.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <div class="row g-2 student-qm-stats-grade-grid mb-3">
                            @foreach([
                                'A' => ['label' => 'ممتاز', 'range' => '90-100%', 'color' => 'success'],
                                'B' => ['label' => 'جيد جداً', 'range' => '80-89%', 'color' => 'info'],
                                'C' => ['label' => 'جيد', 'range' => '70-79%', 'color' => 'primary'],
                                'D' => ['label' => 'مقبول', 'range' => '60-69%', 'color' => 'warning'],
                                'F' => ['label' => 'راسب', 'range' => '<60%', 'color' => 'danger'],
                            ] as $grade => $info)
                                <div class="col">
                                    <div class="student-qm-stats-grade-item student-qm-stats-grade-item--{{ $info['color'] }}">
                                        <span class="student-qm-stats-grade-item__letter">{{ $grade }}</span>
                                        <strong class="student-qm-stats-grade-item__value"
                                                data-countup="{{ $gradeDistribution[$grade] }}">0</strong>
                                        <small class="student-qm-stats-grade-item__label">{{ $info['label'] }}</small>
                                        <small class="student-qm-stats-grade-item__range">{{ $info['range'] }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="student-quiz-analytics-chart-wrap student-qm-stats-grade-chart">
                            <canvas id="gradeChart" height="80"></canvas>
                        </div>
                    </div>
                </div>

                @if($questionTypeStats->count() > 0)
                    <div class="card custom-card group-show-members-card dashboard-fade-in">
                        <div class="card-header border-0 pb-0">
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar avatar-sm bg-warning-transparent">
                                    <i class="fe fe-layers text-warning"></i>
                                </span>
                                <div>
                                    <h4 class="card-title mb-1">الأداء حسب نوع السؤال</h4>
                                    <p class="fs-12 text-muted mb-0">دقة إجاباتك لكل نوع من الأسئلة.</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-3">
                            @foreach($questionTypeStats as $index => $stat)
                                @php
                                    $barColor = $stat->percentage >= 70 ? 'success' : ($stat->percentage >= 50 ? 'warning' : 'danger');
                                @endphp
                                <div class="dashboard-stat-row dashboard-stagger-item mb-3" style="--stagger-delay: {{ $index * 40 }}ms">
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                                        <span class="fw-semibold fs-13">{{ $stat->display_name }}</span>
                                        <span class="badge bg-{{ $barColor }}-transparent text-{{ $barColor }}">
                                            {{ $stat->correct }} / {{ $stat->total }} ({{ number_format($stat->percentage, 1) }}%)
                                        </span>
                                    </div>
                                    <div class="progress progress-xs">
                                        <div class="progress-bar bg-{{ $barColor }}" style="width: {{ min(100, $stat->percentage) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @include('student.question-modules.partials.stats-recent-attempts', ['recentAttempts' => $recentAttempts])
            </div>

            <div class="col-xl-4 col-lg-5 d-flex flex-column gap-3">
                @include('student.question-modules.partials.stats-sidebar', compact(
                    'bestAttempt',
                    'uniqueModules',
                    'failedAttempts',
                    'passedAttempts',
                    'totalAttempts',
                    'totalTimeSpent',
                    'availableModules'
                ))
            </div>
        </div>

    </div>
</div>
@stop

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    'use strict';

    function formatNumber(value, decimals) {
        if (decimals) {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 1,
                maximumFractionDigits: 1,
            }).format(value);
        }
        return new Intl.NumberFormat('ar-EG').format(Math.round(value));
    }

    document.querySelectorAll('[data-countup]').forEach(function (el) {
        var target = parseFloat(el.dataset.countup || '0');
        var prefix = el.dataset.countupPrefix || '';
        var suffix = el.dataset.countupSuffix || '';
        var decimals = el.dataset.countupDecimals === '1' || el.dataset.countupDecimals === '2';
        var duration = 800;
        var start = performance.now();

        function step(now) {
            var progress = Math.min((now - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            var value = formatNumber(target * eased, decimals);
            el.textContent = prefix + value + suffix;
            if (progress < 1) requestAnimationFrame(step);
        }

        requestAnimationFrame(step);
    });

    var primary = getComputedStyle(document.documentElement).getPropertyValue('--primary-rgb').trim() || '5, 85, 162';

    var performanceData = @json($performanceData);
    var performanceCtx = document.getElementById('performanceChart');
    if (performanceCtx) {
        new Chart(performanceCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: performanceData.map(function (d) { return d.date; }),
                datasets: [{
                    label: 'النسبة المئوية',
                    data: performanceData.map(function (d) { return d.average; }),
                    borderColor: 'rgb(' + primary + ')',
                    backgroundColor: 'rgba(' + primary + ', 0.12)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function (value) { return value + '%'; }
                        }
                    }
                }
            }
        });
    }

    var gradeData = @json($gradeDistribution);
    var gradeCtx = document.getElementById('gradeChart');
    if (gradeCtx) {
        new Chart(gradeCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['ممتاز (A)', 'جيد جداً (B)', 'جيد (C)', 'مقبول (D)', 'راسب (F)'],
                datasets: [{
                    data: [gradeData.A, gradeData.B, gradeData.C, gradeData.D, gradeData.F],
                    backgroundColor: [
                        'rgb(16, 185, 129)',
                        'rgb(59, 130, 246)',
                        'rgb(99, 102, 241)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                    ],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
})();
</script>
@stop
