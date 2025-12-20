@extends('student.layouts.master')

@section('page-title', 'إحصائيات ' . $questionModule->title)

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>
                    إحصائيات: {{ $questionModule->title }}
                </h4>
            </div>
            <div class="ms-auto">
                <a href="{{ route('student.question-module.stats.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-right me-2"></i>العودة للإحصائيات
                </a>
            </div>
        </div>
        <!-- End Page Header -->
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white text-center py-4">
                    <i class="fas fa-clipboard-list mb-2" style="font-size: 2.5rem;"></i>
                    <h4 class="mb-1 fw-bold">{{ $attempts->count() }}</h4>
                    <p class="mb-0">إجمالي المحاولات</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white text-center py-4">
                    <i class="fas fa-chart-line mb-2" style="font-size: 2.5rem;"></i>
                    <h4 class="mb-1 fw-bold">{{ number_format($averageScore, 1) }}%</h4>
                    <p class="mb-0">متوسط الدرجات</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white text-center py-4">
                    <i class="fas fa-trophy mb-2" style="font-size: 2.5rem;"></i>
                    <h4 class="mb-1 fw-bold">{{ number_format($bestScore, 1) }}%</h4>
                    <p class="mb-0">أفضل درجة</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="card-body text-white text-center py-4">
                    <i class="fas fa-clock mb-2" style="font-size: 2.5rem;"></i>
                    <h4 class="mb-1 fw-bold">{{ round($totalTimeSpent / 60) }}</h4>
                    <p class="mb-0">إجمالي الدقائق</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Progress Chart -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-area me-2"></i>
                        تطور الأداء عبر المحاولات
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="progressChart" height="100"></canvas>
                </div>
            </div>

            <!-- Question-by-Question Performance -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-list-check me-2"></i>
                        الأداء لكل سؤال
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($questionStats as $index => $stat)
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <div class="flex-grow-1">
                                <span class="badge bg-secondary me-2">{{ $index + 1 }}</span>
                                <span class="fw-bold">
                                    {!! Str::limit(strip_tags($stat['question']->question_text), 80) !!}
                                </span>
                            </div>
                            <span class="badge bg-{{ $stat['accuracy'] >= 70 ? 'success' : ($stat['accuracy'] >= 50 ? 'warning' : 'danger') }} ms-2">
                                {{ $stat['correct_count'] }} / {{ $stat['total_attempts'] }} ({{ $stat['accuracy'] }}%)
                            </span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-{{ $stat['accuracy'] >= 70 ? 'success' : ($stat['accuracy'] >= 50 ? 'warning' : 'danger') }}"
                                 style="width: {{ $stat['accuracy'] }}%">
                                {{ $stat['accuracy'] }}%
                            </div>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-tag me-1"></i>{{ $stat['question']->questionType->display_name }}
                        </small>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-4">
            <!-- Module Info -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        معلومات الاختبار
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">عدد الأسئلة</small>
                        <strong>{{ $questionModule->questions->count() }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">إجمالي الدرجات</small>
                        <strong>{{ $questionModule->getTotalGrade() }}</strong>
                    </div>
                    @if($questionModule->time_limit)
                    <div class="mb-3">
                        <small class="text-muted d-block">الوقت المحدد</small>
                        <strong>{{ $questionModule->time_limit }} دقيقة</strong>
                    </div>
                    @endif
                    <div class="mb-3">
                        <small class="text-muted d-block">نسبة النجاح</small>
                        <strong>{{ $questionModule->pass_percentage }}%</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">المحاولات المسموحة</small>
                        <strong>{{ $questionModule->attempts_allowed }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">المحاولات المتبقية</small>
                        <strong>{{ $questionModule->attempts_allowed - $attempts->count() }}</strong>
                    </div>
                </div>
            </div>

            <!-- Attempts List -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        قائمة المحاولات
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($attempts as $attempt)
                        <a href="{{ route('student.question-module.result', $attempt->id) }}"
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-secondary">المحاولة #{{ $attempt->attempt_number }}</span>
                                    <div class="mt-1">
                                        <small class="text-muted">
                                            {{ $attempt->completed_at->format('Y-m-d H:i') }}
                                        </small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold {{ $attempt->is_passed ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($attempt->percentage, 1) }}%
                                    </div>
                                    @if($attempt->is_passed)
                                        <small class="text-success">ناجح</small>
                                    @else
                                        <small class="text-danger">راسب</small>
                                    @endif
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Best vs Worst -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-balance-scale me-2"></i>
                        أفضل وأسوأ أداء
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 p-3 rounded bg-success bg-opacity-10 border border-success">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-arrow-up text-success me-2"></i>
                                <strong>أفضل درجة</strong>
                            </div>
                            <span class="h4 mb-0 text-success">{{ number_format($bestScore, 1) }}%</span>
                        </div>
                    </div>
                    <div class="p-3 rounded bg-danger bg-opacity-10 border border-danger">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-arrow-down text-danger me-2"></i>
                                <strong>أقل درجة</strong>
                            </div>
                            <span class="h4 mb-0 text-danger">{{ number_format($worstScore, 1) }}%</span>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            تحسن بمقدار: <strong class="text-primary">{{ number_format($bestScore - $worstScore, 1) }}%</strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Button -->
    @if($questionModule->canStudentAttempt(auth()->id()))
    <div class="row">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body text-center py-4">
                    <i class="fas fa-rocket fs-1 mb-3"></i>
                    <h4 class="mb-3">جاهز لمحاولة جديدة؟</h4>
                    <p class="mb-3">لديك {{ $questionModule->attempts_allowed - $attempts->count() }} محاولة متبقية</p>
                    <a href="{{ route('student.question-module.start', $questionModule->id) }}"
                       class="btn btn-light btn-lg">
                        <i class="fas fa-play me-2"></i>بدء محاولة جديدة
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
    </div>
</div>
<!-- End::app-content -->
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Progress Chart
    const progressData = @json($progressData);
    const ctx = document.getElementById('progressChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: progressData.map(d => 'المحاولة ' + d.attempt_number),
            datasets: [{
                label: 'النسبة المئوية',
                data: progressData.map(d => d.percentage),
                borderColor: 'rgb(99, 102, 241)',
                backgroundColor: progressData.map(d => d.is_passed ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'),
                tension: 0.4,
                fill: true,
                pointBackgroundColor: progressData.map(d => d.is_passed ? 'rgb(16, 185, 129)' : 'rgb(239, 68, 68)'),
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'الدرجة: ' + context.parsed.y.toFixed(1) + '%';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
