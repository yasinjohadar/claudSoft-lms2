@extends('admin.layouts.master')

@section('page-title')
    تحليلات الاختبار: {{ $quiz->title }}
@stop

@section('css')
<style>
    .stat-card {
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
</style>
@stop

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">

        <!-- Alerts -->
        @include('admin.components.alerts')

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div class="my-auto">
                <h5 class="page-title fs-21 mb-1">تحليلات الاختبار: {{ $quiz->title }}</h5>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('quiz-analytics.index') }}">تحليلات الاختبارات</a></li>
                        <li class="breadcrumb-item active">{{ $quiz->title }}</li>
                    </nav>
                </div>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('quizzes.show', $quiz->id) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>العودة للاختبار
                </a>
            </div>
        </div>

        <!-- Quiz Info -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4 class="mb-3">{{ $quiz->title }}</h4>
                        @if($quiz->description)
                            <p class="text-muted mb-3">{{ $quiz->description }}</p>
                        @endif
                        <div class="d-flex flex-wrap gap-3">
                            @if($quiz->course)
                                <span class="badge bg-info">
                                    <i class="fas fa-book me-1"></i>{{ $quiz->course->title }}
                                </span>
                            @endif
                            <span class="badge bg-primary">
                                <i class="fas fa-question-circle me-1"></i>{{ $quiz->quizQuestions->count() }} أسئلة
                            </span>
                            @if($quiz->time_limit)
                                <span class="badge bg-warning">
                                    <i class="fas fa-clock me-1"></i>{{ $quiz->time_limit }} دقيقة
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-clipboard-list fa-2x text-primary"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">إجمالي المحاولات</h6>
                                <h3 class="mb-0">{{ $stats['total_attempts'] }}</h3>
                                <small class="text-muted">{{ $stats['completed_attempts'] }} مكتملة</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-success bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-chart-line fa-2x text-success"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">متوسط الدرجات</h6>
                                <h3 class="mb-0">{{ number_format($stats['average_score'] ?? 0, 1) }}%</h3>
                                <small class="text-muted">أعلى: {{ number_format($stats['highest_score'] ?? 0, 1) }}%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-info bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-percentage fa-2x text-info"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">معدل النجاح</h6>
                                <h3 class="mb-0">{{ number_format($stats['pass_rate'] ?? 0, 1) }}%</h3>
                                <small class="text-muted">أقل: {{ number_format($stats['lowest_score'] ?? 0, 1) }}%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card stat-card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-warning bg-opacity-10 p-3 rounded">
                                    <i class="fas fa-clock fa-2x text-warning"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">متوسط الوقت</h6>
                                <h3 class="mb-0">{{ $stats['average_time'] ? number_format($stats['average_time'] / 60, 1) : '0' }} د</h3>
                                <small class="text-muted">{{ $stats['in_progress'] }} قيد التنفيذ</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Score Distribution -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar text-primary me-2"></i>
                            توزيع الدرجات
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($scoreDistribution && count($scoreDistribution) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>المدى</th>
                                            <th class="text-center">عدد الطلاب</th>
                                            <th class="text-center">النسبة</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $total = array_sum($scoreDistribution);
                                        @endphp
                                        @foreach($scoreDistribution as $range => $count)
                                            <tr>
                                                <td><strong>{{ $range }}%</strong></td>
                                                <td class="text-center">{{ $count }}</td>
                                                <td class="text-center">
                                                    @if($total > 0)
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar 
                                                                @if($range == '0-20') bg-danger
                                                                @elseif($range == '21-40') bg-warning
                                                                @elseif($range == '41-60') bg-info
                                                                @elseif($range == '61-80') bg-primary
                                                                @else bg-success
                                                                @endif" 
                                                                role="progressbar" 
                                                                style="width: {{ ($count / $total) * 100 }}%">
                                                                {{ number_format(($count / $total) * 100, 1) }}%
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">0%</span>
                                                    @endif
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

            <!-- Question Analysis -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-question-circle text-info me-2"></i>
                            تحليل الأسئلة
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($questionAnalysis && $questionAnalysis->count() > 0)
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm table-hover">
                                    <thead class="sticky-top bg-white">
                                        <tr>
                                            <th>السؤال</th>
                                            <th class="text-center">معدل النجاح</th>
                                            <th class="text-center">الإجابات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($questionAnalysis as $index => $analysis)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-start">
                                                        <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                                        <div>
                                                            {!! Str::limit(strip_tags($analysis['question']->question_text ?? ''), 50) !!}
                                                            <br>
                                                            <small class="text-muted">
                                                                {{ $analysis['question']->questionType->display_name ?? 'غير معروف' }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $successRate = $analysis['success_rate'] ?? 0;
                                                        $badgeClass = $successRate >= 70 ? 'bg-success' : ($successRate >= 50 ? 'bg-warning' : 'bg-danger');
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">
                                                        {{ number_format($successRate, 1) }}%
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <small class="text-muted">
                                                        {{ $analysis['correct_responses'] ?? 0 }} / {{ $analysis['total_responses'] ?? 0 }}
                                                    </small>
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

        <!-- Student Performance -->
        <div class="row g-4 mt-2">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user-graduate text-success me-2"></i>
                            أداء الطلاب
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($studentPerformance && $studentPerformance->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>الطالب</th>
                                            <th class="text-center">عدد المحاولات</th>
                                            <th class="text-center">أفضل درجة</th>
                                            <th class="text-center">متوسط الدرجة</th>
                                            <th class="text-center">التحسن</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($studentPerformance as $performance)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-primary me-2">{{ $loop->iteration }}</span>
                                                        {{ $performance['student']->name ?? 'غير معروف' }}
                                                    </div>
                                                </td>
                                                <td class="text-center">{{ $performance['attempts_count'] ?? 0 }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-success">
                                                        {{ number_format($performance['best_score'] ?? 0, 1) }}%
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info">
                                                        {{ number_format($performance['average_score'] ?? 0, 1) }}%
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $improvement = $performance['improvement'] ?? 0;
                                                        $improvementClass = $improvement > 0 ? 'text-success' : ($improvement < 0 ? 'text-danger' : 'text-muted');
                                                    @endphp
                                                    <span class="{{ $improvementClass }}">
                                                        @if($improvement > 0)
                                                            <i class="fas fa-arrow-up me-1"></i>
                                                        @elseif($improvement < 0)
                                                            <i class="fas fa-arrow-down me-1"></i>
                                                        @endif
                                                        {{ number_format($improvement, 1) }}%
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

        <!-- Attempt Trends -->
        @if($attemptTrends && $attemptTrends->count() > 0)
        <div class="row g-4 mt-2">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line text-warning me-2"></i>
                            اتجاهات المحاولات
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>التاريخ</th>
                                        <th class="text-center">عدد المحاولات</th>
                                        <th class="text-center">متوسط الدرجة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($attemptTrends as $trend)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($trend->date)->format('Y-m-d') }}</td>
                                            <td class="text-center">{{ $trend->count }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-info">
                                                    {{ number_format($trend->avg_score ?? 0, 1) }}%
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection

@section('js')
@endsection

