@extends('admin.layouts.master')

@section('page-title')
    تحليلات الاختبارات
@stop

@section('css')
@stop

@section('content')

<!-- Start::app-content -->
<div class="main-content app-content">
    <div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">تحليلات الاختبارات</h2>
            <p class="text-muted mb-0">عرض شامل لأداء الاختبارات والطلاب</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="fas fa-clipboard-list fa-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">إجمالي الاختبارات</h6>
                            <h3 class="mb-0">{{ $stats['total_quizzes'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">المحاولات المكتملة</h6>
                            <h3 class="mb-0">{{ $stats['completed_attempts'] }}</h3>
                            <small class="text-muted">من أصل {{ $stats['total_attempts'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="fas fa-chart-line fa-2x text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">متوسط الدرجات</h6>
                            <h3 class="mb-0">{{ number_format($stats['average_score'] ?? 0, 1) }}%</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="fas fa-user-graduate fa-2x text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">عدد الطلاب</h6>
                            <h3 class="mb-0">{{ $stats['total_students'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Top Students -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy text-warning me-2"></i>
                        أفضل الطلاب
                    </h5>
                </div>
                <div class="card-body">
                    @if($topStudents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>الطالب</th>
                                        <th class="text-center">المحاولات</th>
                                        <th class="text-center">متوسط الدرجة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topStudents as $index => $studentData)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge bg-primary me-2">{{ $loop->iteration }}</span>
                                                    {{ $studentData->name }}
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $studentData->attempts_count }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-success">
                                                    {{ number_format($studentData->average_score, 1) }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">لا توجد بيانات متاحة</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Difficult Quizzes -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        الاختبارات الأكثر صعوبة
                    </h5>
                </div>
                <div class="card-body">
                    @if($difficultQuizzes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>الاختبار</th>
                                        <th class="text-center">المحاولات</th>
                                        <th class="text-center">متوسط الدرجة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($difficultQuizzes as $quizData)
                                        <tr>
                                            <td>
                                                {{ $quizData->title }}
                                            </td>
                                            <td class="text-center">{{ $quizData->attempts_count }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-danger">
                                                    {{ number_format($quizData->average_score, 1) }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">لا توجد بيانات متاحة</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Attempts -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clock text-info me-2"></i>
                        آخر المحاولات
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentAttempts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>الطالب</th>
                                        <th>الاختبار</th>
                                        <th class="text-center">الدرجة</th>
                                        <th class="text-center">الحالة</th>
                                        <th class="text-center">التاريخ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentAttempts as $attempt)
                                        <tr>
                                            <td>{{ $attempt['student']->name ?? 'N/A' }}</td>
                                            <td>
                                                {{ $attempt['title'] }}
                                                @if($attempt['type'] === 'module')
                                                    <span class="badge bg-info badge-sm ms-1">وحدة</span>
                                                @else
                                                    <span class="badge bg-secondary badge-sm ms-1">اختبار</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($attempt['is_completed'] && $attempt['score'] !== null)
                                                    <span class="badge {{ $attempt['score'] >= 60 ? 'bg-success' : 'bg-danger' }}">
                                                        {{ number_format($attempt['score'], 1) }}%
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($attempt['is_completed'])
                                                    <span class="badge bg-success">مكتمل</span>
                                                @else
                                                    <span class="badge bg-warning">جاري</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                {{ $attempt['started_at']->format('Y-m-d H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">لا توجد محاولات حديثة</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    </div>
</div>
<!-- End::app-content -->

@endsection

@section('js')
@endsection
