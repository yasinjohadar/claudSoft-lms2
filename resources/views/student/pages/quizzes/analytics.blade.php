@extends('student.layouts.master')

@section('page-title')
    تحليلات الأداء
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div>
                    <h4 class="mb-0">تحليلات الأداء</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('student.quizzes.review.index') }}">مراجعة الاختبارات</a></li>
                            <li class="breadcrumb-item active">التحليلات</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('student.quizzes.review.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i>العودة
                </a>
            </div>

            <!-- المقاييس العامة -->
            @if(isset($overallMetrics))
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <div class="text-primary mb-2"><i class="fas fa-percentage fa-2x"></i></div>
                                <h4 class="fw-bold mb-1 text-primary">{{ number_format($overallMetrics['average_score'] ?? 0, 1) }}%</h4>
                                <p class="text-muted mb-0">متوسط النتيجة</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <div class="text-success mb-2"><i class="fas fa-check-circle fa-2x"></i></div>
                                <h4 class="fw-bold mb-1 text-success">{{ number_format($overallMetrics['pass_rate'] ?? 0, 1) }}%</h4>
                                <p class="text-muted mb-0">معدل النجاح</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <div class="text-info mb-2"><i class="fas fa-clock fa-2x"></i></div>
                                <h4 class="fw-bold mb-1 text-info">{{ number_format($overallMetrics['average_time'] ?? 0, 0) }} دقيقة</h4>
                                <p class="text-muted mb-0">متوسط الوقت</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card text-center border-0 shadow-sm">
                            <div class="card-body">
                                <div class="text-warning mb-2"><i class="fas fa-list fa-2x"></i></div>
                                <h4 class="fw-bold mb-1 text-warning">{{ number_format($overallMetrics['total_attempts'] ?? 0) }}</h4>
                                <p class="text-muted mb-0">إجمالي المحاولات</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <!-- نقاط القوة -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-thumbs-up me-2 text-success"></i>نقاط القوة</h5>
                        </div>
                        <div class="card-body">
                            @if(isset($topStrengths) && count($topStrengths) > 0)
                                @foreach($topStrengths as $strength)
                                    <div class="d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded">
                                        <div>
                                            <strong>{{ $strength['name'] ?? 'غير محدد' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $strength['category'] ?? '' }}</small>
                                        </div>
                                        <span class="badge bg-success">{{ number_format($strength['accuracy'] ?? 0, 1) }}%</span>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted text-center py-3">لا توجد بيانات كافية</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- نقاط الضعف -->
                <div class="col-lg-6 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>نقاط الضعف</h5>
                        </div>
                        <div class="card-body">
                            @if(isset($topWeaknesses) && count($topWeaknesses) > 0)
                                @foreach($topWeaknesses as $weakness)
                                    <div class="d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded">
                                        <div>
                                            <strong>{{ $weakness['name'] ?? 'غير محدد' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $weakness['category'] ?? '' }}</small>
                                        </div>
                                        <span class="badge bg-danger">{{ number_format($weakness['accuracy'] ?? 0, 1) }}%</span>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-muted text-center py-3">لا توجد بيانات كافية</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- الأداء حسب الكورس -->
            @if(isset($performanceByCourse) && count($performanceByCourse) > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-book me-2 text-info"></i>الأداء حسب الكورس</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>الكورس</th>
                                        <th>عدد المحاولات</th>
                                        <th>متوسط النتيجة</th>
                                        <th>معدل النجاح</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($performanceByCourse as $course)
                                        <tr>
                                            <td><strong>{{ $course['name'] ?? 'غير محدد' }}</strong></td>
                                            <td>{{ $course['attempts'] ?? 0 }}</td>
                                            <td>
                                                <span class="fw-bold">{{ number_format($course['average_score'] ?? 0, 1) }}%</span>
                                            </td>
                                            <td>
                                                <span class="badge {{ ($course['pass_rate'] ?? 0) >= 70 ? 'bg-success' : 'bg-warning' }}">
                                                    {{ number_format($course['pass_rate'] ?? 0, 1) }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- التقدم مع الوقت -->
            @if(isset($progressOverTime) && count($progressOverTime) > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>التقدم مع الوقت</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="progressChart" height="100"></canvas>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    @if(isset($progressOverTime) && count($progressOverTime) > 0)
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('progressChart');
        const progressData = @json($progressOverTime);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: progressData.map(item => item.date),
                datasets: [{
                    label: 'متوسط النتيجة',
                    data: progressData.map(item => parseFloat(item.avg_score)),
                    borderColor: '#0555a2',
                    backgroundColor: 'rgba(5, 85, 162, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    </script>
    @endif
    @endpush
@stop



