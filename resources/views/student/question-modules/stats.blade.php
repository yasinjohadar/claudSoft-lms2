@extends('student.layouts.master')

@section('page-title', 'إحصائيات الاختبارات')

@section('content')
<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h4 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    إحصائيات الاختبارات
                </h4>
                <p class="mb-0 text-muted">عرض شامل لأدائك في جميع الاختبارات</p>
            </div>
            <div class="ms-auto">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print me-2"></i>طباعة التقرير
                </button>
            </div>
        </div>
        <!-- End Page Header -->
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white text-center py-4">
                    <i class="fas fa-clipboard-list mb-3" style="font-size: 3rem; opacity: 0.9;"></i>
                    <h3 class="mb-1 fw-bold">{{ $totalAttempts }}</h3>
                    <p class="mb-0 fs-6">إجمالي المحاولات</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white text-center py-4">
                    <i class="fas fa-chart-line mb-3" style="font-size: 3rem; opacity: 0.9;"></i>
                    <h3 class="mb-1 fw-bold">{{ number_format($averageScore, 1) }}%</h3>
                    <p class="mb-0 fs-6">متوسط الدرجات</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white text-center py-4">
                    <i class="fas fa-check-circle mb-3" style="font-size: 3rem; opacity: 0.9;"></i>
                    <h3 class="mb-1 fw-bold">{{ $passedAttempts }}</h3>
                    <p class="mb-0 fs-6">محاولات ناجحة</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="card-body text-white text-center py-4">
                    <i class="fas fa-clock mb-3" style="font-size: 3rem; opacity: 0.9;"></i>
                    <h3 class="mb-1 fw-bold">{{ $totalHours }}</h3>
                    <p class="mb-0 fs-6">ساعات التدريب</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Performance Chart -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-area me-2"></i>
                        الأداء خلال آخر 30 يوم
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="100"></canvas>
                </div>
            </div>

            <!-- Grade Distribution -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>
                        توزيع الدرجات
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        @foreach(['A' => ['label' => 'ممتاز (90-100%)', 'color' => 'success'],
                                  'B' => ['label' => 'جيد جداً (80-89%)', 'color' => 'info'],
                                  'C' => ['label' => 'جيد (70-79%)', 'color' => 'primary'],
                                  'D' => ['label' => 'مقبول (60-69%)', 'color' => 'warning'],
                                  'F' => ['label' => 'راسب (<60%)', 'color' => 'danger']] as $grade => $info)
                            <div class="col-md-2 mb-3">
                                <div class="p-3 rounded bg-{{ $info['color'] }} bg-opacity-10 border border-{{ $info['color'] }}">
                                    <div class="display-6 fw-bold text-{{ $info['color'] }}">{{ $gradeDistribution[$grade] }}</div>
                                    <div class="badge bg-{{ $info['color'] }} mt-2">{{ $grade }}</div>
                                    <small class="d-block text-muted mt-1">{{ $info['label'] }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <canvas id="gradeChart" height="80" class="mt-3"></canvas>
                </div>
            </div>

            <!-- Question Types Performance -->
            @if($questionTypeStats->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-layer-group me-2"></i>
                        الأداء حسب نوع السؤال
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($questionTypeStats as $stat)
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">{{ $stat->display_name }}</span>
                            <span class="badge bg-{{ $stat->percentage >= 70 ? 'success' : ($stat->percentage >= 50 ? 'warning' : 'danger') }}">
                                {{ $stat->correct }} / {{ $stat->total }} ({{ number_format($stat->percentage, 1) }}%)
                            </span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-{{ $stat->percentage >= 70 ? 'success' : ($stat->percentage >= 50 ? 'warning' : 'danger') }}"
                                 role="progressbar"
                                 style="width: {{ $stat->percentage }}%">
                                {{ number_format($stat->percentage, 1) }}%
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Recent Attempts -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        آخر المحاولات
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>الاختبار</th>
                                    <th>المحاولة</th>
                                    <th>الدرجة</th>
                                    <th>النسبة</th>
                                    <th>النتيجة</th>
                                    <th>التاريخ</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAttempts as $attempt)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ Str::limit($attempt->questionModule->title, 40) }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">المحاولة #{{ $attempt->attempt_number }}</span>
                                    </td>
                                    <td>{{ number_format($attempt->total_score, 2) }}</td>
                                    <td>
                                        <div class="progress" style="width: 80px; height: 20px;">
                                            <div class="progress-bar bg-{{ $attempt->is_passed ? 'success' : 'danger' }}"
                                                 style="width: {{ $attempt->percentage }}%">
                                                {{ number_format($attempt->percentage, 0) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($attempt->is_passed)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>ناجح
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i>راسب
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $attempt->completed_at->format('Y-m-d H:i') }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('student.question-module.result', $attempt->id) }}"
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox text-muted fs-1"></i>
                                        <p class="text-muted mt-2">لا توجد محاولات بعد</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Best Performance -->
            @if($bestAttempt)
            <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="fas fa-trophy me-2"></i>أفضل أداء
                        </h5>
                        <i class="fas fa-star fs-3"></i>
                    </div>
                    <h3 class="mb-2">{{ number_format($bestAttempt->percentage, 1) }}%</h3>
                    <p class="mb-2 fs-6">{{ $bestAttempt->questionModule->title }}</p>
                    <small class="opacity-75">
                        <i class="fas fa-calendar me-1"></i>
                        {{ $bestAttempt->completed_at->format('Y-m-d') }}
                    </small>
                </div>
            </div>
            @endif

            <!-- Quick Stats -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        معلومات سريعة
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between py-3 border-bottom">
                        <span class="text-muted">
                            <i class="fas fa-book me-2"></i>اختبارات مختلفة
                        </span>
                        <span class="fw-bold">{{ $uniqueModules }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-3 border-bottom">
                        <span class="text-muted">
                            <i class="fas fa-times-circle me-2"></i>محاولات راسبة
                        </span>
                        <span class="fw-bold text-danger">{{ $failedAttempts }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-3 border-bottom">
                        <span class="text-muted">
                            <i class="fas fa-percentage me-2"></i>معدل النجاح
                        </span>
                        <span class="fw-bold text-success">
                            {{ $totalAttempts > 0 ? number_format(($passedAttempts / $totalAttempts) * 100, 1) : 0 }}%
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-3">
                        <span class="text-muted">
                            <i class="fas fa-hourglass-half me-2"></i>متوسط الوقت
                        </span>
                        <span class="fw-bold">
                            {{ $totalAttempts > 0 ? round(($totalTimeSpent / $totalAttempts) / 60) : 0 }} دقيقة
                        </span>
                    </div>
                </div>
            </div>

            <!-- Available Tests -->
            @if($availableModules->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-check me-2"></i>
                        اختبارات متاحة
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($availableModules->take(5) as $module)
                        <a href="{{ route('student.question-module.start', $module->id) }}"
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">{{ Str::limit($module->title, 30) }}</div>
                                    <small class="text-muted">
                                        <i class="fas fa-question-circle me-1"></i>
                                        {{ $module->questions->count() }} سؤال
                                    </small>
                                </div>
                                <i class="fas fa-arrow-left text-success"></i>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Motivational Card -->
            <div class="card border-0" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white text-center py-4">
                    <i class="fas fa-rocket fs-1 mb-3"></i>
                    <h5 class="mb-2">استمر في التفوق!</h5>
                    <p class="mb-0 fs-6">أنت تقوم بعمل رائع. استمر في التدريب والممارسة</p>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
<!-- End::app-content -->
@endsection

@push('styles')
<style>
    @media print {
        .page-header,
        .btn,
        .sidebar,
        .navbar {
            display: none !important;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Performance Chart
    const performanceData = @json($performanceData);
    const performanceCtx = document.getElementById('performanceChart').getContext('2d');
    new Chart(performanceCtx, {
        type: 'line',
        data: {
            labels: performanceData.map(d => d.date),
            datasets: [{
                label: 'النسبة المئوية',
                data: performanceData.map(d => d.average),
                borderColor: 'rgb(99, 102, 241)',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
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

    // Grade Distribution Chart
    const gradeData = @json($gradeDistribution);
    const gradeCtx = document.getElementById('gradeChart').getContext('2d');
    new Chart(gradeCtx, {
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
                    'rgb(239, 68, 68)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush
